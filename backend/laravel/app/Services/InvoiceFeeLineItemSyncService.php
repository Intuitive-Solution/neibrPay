<?php

namespace App\Services;

use App\Models\InvoiceUnit;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantTimezone;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceFeeLineItemSyncService
{
    private const LINE_KIND_LATE_FEE = 'late_fee';
    private const LINE_KIND_EARLY_DISCOUNT = 'early_payment_discount';

    /** @var list<string> */
    private const STATUSES_FOR_QUERY = ['sent', 'partial', 'overdue', 'payment_rejected'];

    /** @var list<string> */
    private const LATE_FEE_STATUSES = ['sent', 'overdue', 'payment_rejected'];

    public function __construct(
        private readonly InvoicePdfService $pdfService,
        private readonly InvoiceN8nNotificationService $n8nPayloadService
    ) {
    }

    /**
     * @return array<string, int>
     */
    public function run(): array
    {
        $summary = [
            'invoices_seen' => 0,
            'invoices_updated' => 0,
            'late_fee_first_insert_email_attempted' => 0,
            'late_fee_first_insert_email_http_success' => 0,
            'pdfs_regenerated' => 0,
        ];

        Tenant::query()->chunkById(100, function ($tenants) use (&$summary) {
            foreach ($tenants as $tenant) {
                $this->processTenant($tenant, $summary);
            }
        });

        return $summary;
    }

    /**
     * @param  array<string, int>  $summary
     */
    private function processTenant(Tenant $tenant, array &$summary): void
    {
        $timezone = TenantTimezone::normalize(data_get($tenant->settings, 'timezone'));
        $todayYmd = now()->timezone($timezone)->format('Y-m-d');

        InvoiceUnit::query()
            ->forTenant($tenant->id)
            ->whereIn('status', self::STATUSES_FOR_QUERY)
            ->whereNull('deleted_at')
            ->with(['payments', 'tenant', 'unit.owners'])
            ->chunkById(75, function ($invoices) use (&$summary, $todayYmd) {
                foreach ($invoices as $invoice) {
                    $summary['invoices_seen']++;
                    $this->processInvoice($invoice, $todayYmd, $summary);
                }
            });
    }

    /**
     * @param  array<string, int>  $summary
     */
    private function processInvoice(InvoiceUnit $invoice, string $todayYmd, array &$summary): void
    {
        $invoice->loadMissing(['payments', 'tenant', 'unit.owners']);

        $balance = (float) $invoice->balance_due;
        $status = (string) $invoice->status;

        $originalItems = is_array($invoice->items) ? $invoice->items : [];
        $originalJson = json_encode($originalItems);
        $hadLateFeeBefore = $this->indexOfLineKind($originalItems, self::LINE_KIND_LATE_FEE) !== null;

        $items = $originalItems;
        $items = $this->stripLinesByKind($items, self::LINE_KIND_EARLY_DISCOUNT);

        if (!$invoice->late_fee_enabled) {
            $items = $this->stripLinesByKind($items, self::LINE_KIND_LATE_FEE);
        }

        $allowLateFee = in_array($status, self::LATE_FEE_STATUSES, true);

        if ($balance > 0) {
            if ($allowLateFee && $invoice->late_fee_enabled && $invoice->late_fee_applies_on_date) {
                $appliesYmd = $this->formatDateYmd($invoice->late_fee_applies_on_date);
                if ($appliesYmd !== null && $todayYmd >= $appliesYmd) {
                    $items = $this->upsertLateFeeLine($invoice, $items);
                } else {
                    $items = $this->stripLinesByKind($items, self::LINE_KIND_LATE_FEE);
                }
            } else {
                $items = $this->stripLinesByKind($items, self::LINE_KIND_LATE_FEE);
            }
        } else {
            $items = $this->stripLinesByKind($items, self::LINE_KIND_LATE_FEE);
        }

        if (json_encode($items) === $originalJson) {
            return;
        }

        $hasLateFeeAfter = $this->indexOfLineKind($items, self::LINE_KIND_LATE_FEE) !== null;
        $firstLateFeeInsert = !$hadLateFeeBefore && $hasLateFeeAfter;
        $feeAdjustmentLinesChanged = $this->adjustmentLinesPresenceChanged($originalItems, $items);

        DB::transaction(function () use ($invoice, $items, &$summary, $firstLateFeeInsert) {
            $invoice->items = $items;
            $invoice->calculateTotals();
            $invoice->save();
            $invoice->refresh();
            $invoice->load(['tenant', 'unit.owners', 'payments']);
            $summary['invoices_updated']++;

            if ($firstLateFeeInsert) {
                $this->notifyLateFeeApplied($invoice, $summary);
            }
        });

        if ($feeAdjustmentLinesChanged) {
            $this->regenerateInvoicePdfSafely($invoice, $summary);
        }
    }

    /**
     * True when a late_fee line was added or removed (not only updated in place).
     *
     * @param  list<array<string, mixed>>  $beforeItems
     * @param  list<array<string, mixed>>  $afterItems
     */
    private function adjustmentLinesPresenceChanged(array $beforeItems, array $afterItems): bool
    {
        $before = $this->adjustmentLineKindsPresent($beforeItems);
        $after = $this->adjustmentLineKindsPresent($afterItems);

        return $before !== $after;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array{late_fee: bool, early_payment_discount: bool}
     */
    private function adjustmentLineKindsPresent(array $items): array
    {
        $late = false;
        foreach ($items as $row) {
            $kind = $row['invoice_line_kind'] ?? null;
            if ($kind === self::LINE_KIND_LATE_FEE) {
                $late = true;
            }
        }

        return [
            'late_fee' => $late,
            'early_payment_discount' => false,
        ];
    }

    /**
     * @param  array<string, int>  $summary
     */
    private function regenerateInvoicePdfSafely(InvoiceUnit $invoice, array &$summary): void
    {
        try {
            $invoice->refresh();
            $invoice->loadMissing(['unit', 'notes', 'tenant', 'payments']);

            $payment = null;
            if ($invoice->status === 'paid') {
                $payment = $invoice->payments()->approved()->latest()->first();
            }

            $html = $this->pdfService->generateInvoiceHtml($invoice, $payment);
            $this->pdfService->generatePdf($invoice, $html, (int) $invoice->created_by);
            $summary['pdfs_regenerated']++;
        } catch (\Throwable $e) {
            Log::error('Invoice fee line sync: PDF regeneration failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function upsertLateFeeLine(InvoiceUnit $invoice, array $items): array
    {
        if (!$invoice->late_fee_type || $invoice->late_fee_amount === null) {
            return $items;
        }

        $base = $this->baseSubtotalForLateFee($items);
        $amount = $this->computeLateFeeAmount($invoice, $base);
        $desc = $this->buildLateFeeDescription($invoice);

        $line = $this->buildAdjustmentLine(
            self::LINE_KIND_LATE_FEE,
            'Late Fee',
            $desc,
            $amount,
            $items
        );

        return $this->upsertLineByKind($items, self::LINE_KIND_LATE_FEE, $line);
    }

    private function computeLateFeeAmount(InvoiceUnit $invoice, float $base): float
    {
        if ($invoice->late_fee_type === 'percentage') {
            return round($base * ((float) $invoice->late_fee_amount) / 100, 2);
        }

        return round((float) $invoice->late_fee_amount, 2);
    }

    private function buildLateFeeDescription(InvoiceUnit $invoice): string
    {
        $applies = $this->formatDateYmd($invoice->late_fee_applies_on_date) ?? '';
        if ($invoice->late_fee_type === 'percentage') {
            $pct = number_format((float) $invoice->late_fee_amount, 2, '.', '');

            return "Amount or percentage and applies date — Percentage: {$pct}%. Applies on {$applies}.";
        }
        $amt = number_format((float) $invoice->late_fee_amount, 2, '.', '');

        return "Amount or percentage and applies date — Fixed amount: \${$amt}. Applies on {$applies}.";
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function buildAdjustmentLine(string $kind, string $name, string $description, float $amount, array $items): array
    {
        $sort = $this->nextSortOrder($items);

        return [
            'name' => $name,
            'description' => $description,
            'unit_cost' => $amount,
            'quantity' => 1,
            'line_total' => $amount,
            'sort_order' => $sort,
            'taxable' => true,
            'charge_id' => null,
            'invoice_line_kind' => $kind,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function upsertLineByKind(array $items, string $kind, array $line): array
    {
        $idx = $this->indexOfLineKind($items, $kind);
        if ($idx !== null) {
            $existing = $items[$idx];
            if (isset($existing['sort_order'])) {
                $line['sort_order'] = $existing['sort_order'];
            }
            $items[$idx] = array_merge($existing, $line);

            return array_values($items);
        }

        $items[] = $line;

        return array_values($items);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function stripLinesByKind(array $items, string $kind): array
    {
        $out = [];
        foreach ($items as $row) {
            if (($row['invoice_line_kind'] ?? null) === $kind) {
                continue;
            }
            $out[] = $row;
        }

        return $out;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function indexOfLineKind(array $items, string $kind): ?int
    {
        foreach ($items as $i => $row) {
            if (($row['invoice_line_kind'] ?? null) === $kind) {
                return (int) $i;
            }
        }

        return null;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function nextSortOrder(array $items): int
    {
        $max = 0;
        foreach ($items as $row) {
            $max = max($max, (int) ($row['sort_order'] ?? 0));
        }

        return $max + 1;
    }

    /**
     * @param list<array<string, mixed>> $items
     */
    private function baseSubtotalForLateFee(array $items): float
    {
        $subtotal = 0.0;

        foreach ($items as $row) {
            if (($row['invoice_line_kind'] ?? null) === self::LINE_KIND_LATE_FEE) {
                continue;
            }
            $subtotal += (float) ($row['line_total'] ?? 0);
        }

        return max(0.0, round($subtotal, 2));
    }

    private function formatDateYmd(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->format('Y-m-d');
        }

        if (is_string($value) && trim($value) !== '') {
            try {
                return (new \DateTimeImmutable($value))->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    /**
     * @param array<string, int> $summary
     */
    private function notifyLateFeeApplied(InvoiceUnit $invoice, array &$summary): void
    {
        $invoice->loadMissing(['tenant', 'unit.owners']);

        $owner = $invoice->unit?->owners?->first();
        if (!$owner instanceof User) {
            Log::warning('Late fee notification skipped: owner not found', [
                'invoice_id' => $invoice->id,
                'unit_id' => $invoice->unit_id,
            ]);
            return;
        }

        $email = trim((string) ($owner->email ?? ''));
        if ($email === '') {
            Log::warning('Late fee notification skipped: owner email missing', [
                'invoice_id' => $invoice->id,
                'owner_id' => $owner->id,
            ]);
            return;
        }

        $payload = $this->n8nPayloadService->buildInvoicePayload($invoice, $owner, $email);
        $payload['type'] = 'invoice_late_fee_applied';
        $payload['late_fee_notice'] = [
            'applied' => true,
            'applied_amount' => number_format($this->currentLateFeeLineAmount($invoice), 2, '.', ''),
            'late_fee_type' => $invoice->late_fee_type,
            'late_fee_amount_configured' => number_format((float) ($invoice->late_fee_amount ?? 0), 2, '.', ''),
            'late_fee_applies_on_date' => $this->formatDateYmd($invoice->late_fee_applies_on_date),
        ];

        $summary['late_fee_first_insert_email_attempted']++;
        if ($this->sendToN8n($payload)) {
            $summary['late_fee_first_insert_email_http_success']++;
            return;
        }

        Log::warning('Late fee notification webhook returned non-success', [
            'invoice_id' => $invoice->id,
        ]);
    }

    private function currentLateFeeLineAmount(InvoiceUnit $invoice): float
    {
        $items = is_array($invoice->items) ? $invoice->items : [];
        foreach ($items as $row) {
            if (($row['invoice_line_kind'] ?? null) === self::LINE_KIND_LATE_FEE) {
                return round((float) ($row['line_total'] ?? 0), 2);
            }
        }

        return 0.0;
    }

    private function sendToN8n(array $payload): bool
    {
        $url = config('n8n.webhook_url') ?: config('services.n8n.webhook_url');
        if (!$url) {
            Log::warning('Late fee notification skipped: N8N webhook URL missing');
            return false;
        }

        $headers = ['Content-Type' => 'application/json'];
        $secret = config('n8n.webhook_secret');
        if (!empty($secret)) {
            $headers['X-Webhook-Token'] = $secret;
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->withHeaders($headers)
                ->post($url, $payload);

            return $response->successful();
        } catch (\Throwable $e) {
            Log::error('Late fee notification send failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
