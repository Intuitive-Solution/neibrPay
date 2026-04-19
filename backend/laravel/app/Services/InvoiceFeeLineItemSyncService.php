<?php

namespace App\Services;

use App\Models\InvoiceUnit;
use App\Models\Tenant;
use App\Support\TenantTimezone;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceFeeLineItemSyncService
{
    /** @var list<string> */
    private const STATUSES_FOR_QUERY = ['sent', 'partial', 'overdue', 'payment_rejected'];

    /** @var list<string> */
    private const LATE_FEE_STATUSES = ['sent', 'overdue', 'payment_rejected'];

    /** @var list<string> */
    private const EARLY_DISCOUNT_STATUSES = ['sent', 'partial', 'overdue', 'payment_rejected'];

    public function __construct(
        private readonly InvoiceN8nInvoiceEmailService $invoiceEmailService
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
        $hadLateFeeBefore = $this->indexOfLineKind($originalItems, InvoiceUnit::INVOICE_LINE_KIND_LATE_FEE) !== null;

        $items = $originalItems;
        if (!$invoice->late_fee_enabled) {
            $items = $this->stripLinesByKind($items, InvoiceUnit::INVOICE_LINE_KIND_LATE_FEE);
        }

        $items = $this->applyEarlyDiscountRemovals($invoice, $items, $todayYmd, $balance);

        $allowLateFee = in_array($status, self::LATE_FEE_STATUSES, true);
        $allowEarlyDiscount = in_array($status, self::EARLY_DISCOUNT_STATUSES, true);

        $firstLateFeeInsert = false;

        if ($balance > 0) {
            if ($allowEarlyDiscount) {
                $items = $this->syncEarlyPaymentDiscountLine($invoice, $items, $todayYmd);
            } else {
                $items = $this->stripLinesByKind($items, InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT);
            }

            if ($allowLateFee && $invoice->late_fee_enabled && $invoice->late_fee_applies_on_date) {
                $appliesYmd = $invoice->late_fee_applies_on_date->format('Y-m-d');
                if ($todayYmd >= $appliesYmd) {
                    $items = $this->upsertLateFeeLine($invoice, $items);
                }
            }
        }

        $hadLateFeeAfter = $this->indexOfLineKind($items, InvoiceUnit::INVOICE_LINE_KIND_LATE_FEE) !== null;
        $firstLateFeeInsert = !$hadLateFeeBefore && $hadLateFeeAfter;

        if (json_encode($items) === $originalJson) {
            return;
        }

        DB::transaction(function () use ($invoice, $items, &$summary, $firstLateFeeInsert) {
            $invoice->items = $items;
            $invoice->calculateTotals();
            $invoice->save();
            $invoice->refresh();
            $invoice->load(['tenant', 'unit.owners', 'payments']);
            $summary['invoices_updated']++;

            if ($firstLateFeeInsert) {
                $this->maybeEmailLateFeeNotice($invoice, $summary);
            }
        });
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function applyEarlyDiscountRemovals(InvoiceUnit $invoice, array $items, string $todayYmd, float $balance): array
    {
        $shouldRemove = !$invoice->early_payment_discount_enabled
            || !$invoice->early_payment_discount_by_date
            || $todayYmd > $invoice->early_payment_discount_by_date->format('Y-m-d')
            || $balance <= 0;

        if ($shouldRemove) {
            return $this->stripLinesByKind($items, InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT);
        }

        return $items;
    }

    /**
     * @param  list<array<string, mixed>>  $items
     * @return list<array<string, mixed>>
     */
    private function syncEarlyPaymentDiscountLine(InvoiceUnit $invoice, array $items, string $todayYmd): array
    {
        if (!$invoice->early_payment_discount_enabled
            || !$invoice->early_payment_discount_by_date
            || !$invoice->early_payment_discount_type
            || $invoice->early_payment_discount_amount === null) {
            return $this->stripLinesByKind($items, InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT);
        }

        $byYmd = $invoice->early_payment_discount_by_date->format('Y-m-d');
        if ($todayYmd > $byYmd) {
            return $this->stripLinesByKind($items, InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT);
        }

        $base = InvoiceUnit::baseSubtotalForAdjustments($items);
        $amount = $this->computeEarlyDiscountAmount($invoice, $base);
        $desc = $this->buildEarlyDiscountDescription($invoice);

        $line = $this->buildAdjustmentLine(
            InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT,
            'Early Payment Discount',
            $desc,
            $amount,
            $items
        );

        return $this->upsertLineByKind($items, InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT, $line);
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

        $base = InvoiceUnit::baseSubtotalForAdjustments($items);
        $amount = $this->computeLateFeeAmount($invoice, $base);
        $desc = $this->buildLateFeeDescription($invoice);

        $line = $this->buildAdjustmentLine(
            InvoiceUnit::INVOICE_LINE_KIND_LATE_FEE,
            'Late Fee',
            $desc,
            $amount,
            $items
        );

        return $this->upsertLineByKind($items, InvoiceUnit::INVOICE_LINE_KIND_LATE_FEE, $line);
    }

    private function computeLateFeeAmount(InvoiceUnit $invoice, float $base): float
    {
        if ($invoice->late_fee_type === 'percentage') {
            return round($base * ((float) $invoice->late_fee_amount) / 100, 2);
        }

        return round((float) $invoice->late_fee_amount, 2);
    }

    private function computeEarlyDiscountAmount(InvoiceUnit $invoice, float $base): float
    {
        if ($invoice->early_payment_discount_type === 'percentage') {
            return round($base * ((float) $invoice->early_payment_discount_amount) / 100, 2);
        }

        return round(min((float) $invoice->early_payment_discount_amount, $base), 2);
    }

    private function buildLateFeeDescription(InvoiceUnit $invoice): string
    {
        $applies = $invoice->late_fee_applies_on_date?->format('Y-m-d') ?? '';
        if ($invoice->late_fee_type === 'percentage') {
            $pct = number_format((float) $invoice->late_fee_amount, 2, '.', '');

            return "Amount or percentage and applies date — Percentage: {$pct}%. Applies on {$applies}.";
        }
        $amt = number_format((float) $invoice->late_fee_amount, 2, '.', '');

        return "Amount or percentage and applies date — Fixed amount: \${$amt}. Applies on {$applies}.";
    }

    private function buildEarlyDiscountDescription(InvoiceUnit $invoice): string
    {
        $by = $invoice->early_payment_discount_by_date?->format('Y-m-d') ?? '';
        if ($invoice->early_payment_discount_type === 'percentage') {
            $pct = number_format((float) $invoice->early_payment_discount_amount, 2, '.', '');

            return "Amount or percentage and pay-by date — Percentage: {$pct}%. Pay by {$by}.";
        }
        $amt = number_format((float) $invoice->early_payment_discount_amount, 2, '.', '');

        return "Amount or percentage and pay-by date — Fixed amount: \${$amt}. Pay by {$by}.";
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
     * @param  array<string, int>  $summary
     */
    private function maybeEmailLateFeeNotice(InvoiceUnit $invoice, array &$summary): void
    {
        $invoice->loadMissing(['unit.owners', 'tenant']);
        if (!$invoice->unit) {
            Log::warning('Late fee email skipped: no unit', ['invoice_id' => $invoice->id]);

            return;
        }
        if (!$invoice->unit->relationLoaded('owners')) {
            $invoice->unit->load('owners');
        }
        $owner = $invoice->unit->owners->first();
        if (!$owner || !is_string($owner->email) || trim($owner->email) === '') {
            Log::warning('Late fee email skipped: no owner email', ['invoice_id' => $invoice->id]);

            return;
        }

        $email = trim($owner->email);
        $result = $this->invoiceEmailService->postInvoiceWebhook($invoice, $owner, $email);
        if ($result['attempted']) {
            $summary['late_fee_first_insert_email_attempted']++;
        }
        if (!empty($result['http_success'])) {
            $summary['late_fee_first_insert_email_http_success']++;
        }
    }
}
