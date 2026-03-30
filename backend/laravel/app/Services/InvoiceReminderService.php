<?php

namespace App\Services;

use App\Models\InvoiceReminderLog;
use App\Models\InvoiceUnit;
use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class InvoiceReminderService
{
    public function __construct(
        private readonly InvoiceN8nNotificationService $n8nPayloadService
    ) {
    }

    public function run(): array
    {
        Log::info('Running invoice reminders');
        $summary = [
            'sent_count' => 0,
            'skipped_duplicate_count' => 0,
            'skipped_ineligible_count' => 0,
            'failed_count' => 0,
        ];

        Tenant::query()->chunkById(100, function ($tenants) use (&$summary) {
            foreach ($tenants as $tenant) {
                $summary = $this->processTenant($tenant, $summary);
            }
        });

        return $summary;
    }

    private function processTenant(Tenant $tenant, array $summary): array
    {
        Log::info('Processing tenant', ['tenant_id' => $tenant->id]);
        $config = $this->normalizeReminderConfig((array) ($tenant->settings ?? []));
        if (!$config['enabled']) {
            Log::info('Tenant is not enabled', ['tenant_id' => $tenant->id]);
            return $summary;
        }

        Log::info('Tenant is enabled', ['tenant_id' => $tenant->id]);
        $timezone = $config['timezone'];
        $today = now()->setTimezone($timezone)->startOfDay();

        $invoices = InvoiceUnit::query()
            ->forTenant($tenant->id)
            ->whereIn('status', ['sent', 'partial'])
            ->whereNull('deleted_at')
            ->with(['tenant', 'unit.owners', 'payments'])
            ->get();

        foreach ($invoices as $invoice) {
            Log::info('Processing invoice', ['invoice_id' => $invoice->id]);
            if ((float) $invoice->balance_due <= 0) {
                $summary['skipped_ineligible_count']++;
                continue;
            }

            $owner = $invoice->unit?->owners?->first();
            if (!$owner || empty($owner->email)) {
                $summary['skipped_ineligible_count']++;
                continue;
            }

            $dueDate = $invoice->getActualDueDate()->setTimezone($timezone)->startOfDay();
            Log::info('Due date', ['due_date' => $dueDate]);
            Log::info('Invoice Number', ['invoice_number' => $invoice->invoice_number]);
            Log::info('Invoice owner', ['owner' => $owner->email]);
            $daysUntilDue = $today->diffInDays($dueDate, false);
            Log::info('Days until due', ['days_until_due' => $daysUntilDue]);

            if ($daysUntilDue > 0 && in_array($daysUntilDue, $config['pre_due_offsets_days'], true)) {
                $summary = $this->attemptReminder(
                    $summary,
                    $invoice,
                    'pre_due',
                    "pre:{$daysUntilDue}",
                    $daysUntilDue,
                    fn () => $this->n8nPayloadService->buildReminderPayload($invoice, $owner, 'pre_due', $daysUntilDue)
                );
            }

            if ($daysUntilDue < 0) {
                $daysOverdue = abs($daysUntilDue);
                if ($daysOverdue % $config['post_due_interval_days'] !== 0) {
                    $summary['skipped_ineligible_count']++;
                    continue;
                }

                if ($config['post_due_stop_after_days'] !== null && $daysOverdue > $config['post_due_stop_after_days']) {
                    $summary['skipped_ineligible_count']++;
                    continue;
                }

                if ($config['post_due_max_reminders'] !== null) {
                    $postCount = InvoiceReminderLog::query()
                        ->where('invoice_unit_id', $invoice->id)
                        ->where('reminder_kind', 'post_due')
                        ->where('status', 'sent')
                        ->count();
                    if ($postCount >= $config['post_due_max_reminders']) {
                        $summary['skipped_ineligible_count']++;
                        continue;
                    }
                }

                // Idempotency key must be stable for this overdue "slot" (multiples of interval), not
                // calendar date — otherwise a second run the same day (n8n + manual test, retries,
                // duplicate schedules) hits the unique index and looks like a false "every N days" bug.
                $summary = $this->attemptReminder(
                    $summary,
                    $invoice,
                    'post_due',
                    'post:' . $daysOverdue,
                    $daysOverdue,
                    fn () => $this->n8nPayloadService->buildReminderPayload($invoice, $owner, 'post_due', $daysOverdue)
                );
            }
        }

        return $summary;
    }

    private function attemptReminder(
        array $summary,
        InvoiceUnit $invoice,
        string $kind,
        string $reminderKey,
        int $phaseDayValue,
        callable $payloadBuilder
    ): array {
        $logRow = null;

        try {
            DB::transaction(function () use (
                $invoice,
                $kind,
                $reminderKey,
                $phaseDayValue,
                &$logRow
            ) {
                $logRow = InvoiceReminderLog::create([
                    'tenant_id' => $invoice->tenant_id,
                    'invoice_unit_id' => $invoice->id,
                    'reminder_kind' => $kind,
                    'reminder_key' => $reminderKey,
                    'status' => 'sent',
                    'phase_day_value' => $phaseDayValue,
                ]);
            });
        } catch (QueryException $e) {
            if ($this->isUniqueConstraintViolation($e)) {
                $existing = InvoiceReminderLog::query()
                    ->where('invoice_unit_id', $invoice->id)
                    ->where('reminder_kind', $kind)
                    ->where('reminder_key', $reminderKey)
                    ->first();

                if ($existing && $existing->status === 'failed') {
                    $logRow = $existing;
                } else {
                    $summary['skipped_duplicate_count']++;
                    return $summary;
                }
            } else {
                throw $e;
            }
        }

        if ($logRow === null) {
            return $summary;
        }

        try {
            $payload = $payloadBuilder();
            $response = $this->sendToN8n($payload);

            if (!$response->successful()) {
                throw new \RuntimeException(
                    'n8n returned status ' . $response->status() . ': ' . substr($response->body(), 0, 300)
                );
            }

            $logRow->update([
                'status' => 'sent',
                'recipient_email' => data_get($payload, 'recipient.email'),
                'cc_emails' => data_get($payload, 'cc', []),
                'payload_hash' => hash('sha256', json_encode($payload)),
                'sent_at' => now(),
                'error_message' => null,
            ]);
            $summary['sent_count']++;
        } catch (\Throwable $e) {
            $logRow->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            Log::error('Invoice reminder send failed', [
                'invoice_id' => $invoice->id,
                'reminder_kind' => $kind,
                'reminder_key' => $reminderKey,
                'error' => $e->getMessage(),
            ]);
            $summary['failed_count']++;
        }

        return $summary;
    }

    private function sendToN8n(array $payload)
    {
        $url = config('n8n.webhook_url') ?: config('services.n8n.webhook_url');
        Log::info('Sending to N8N', ['url' => $url]);
        if (!$url) {
            throw new \RuntimeException('N8N webhook URL is not configured');
        }

        $headers = ['Content-Type' => 'application/json'];
        $secret = config('n8n.webhook_secret');
        if (!empty($secret)) {
            $headers['X-Webhook-Token'] = $secret;
        }
        Log::info('N8N headers', ['headers' => $headers]);
        Log::info('N8N payload', ['payload' => $payload]);
        return Http::timeout(10)
            ->retry(2, 100)
            ->withHeaders($headers)
            ->post($url, $payload);
    }

    private function normalizeReminderConfig(array $tenantSettings): array
    {
        $reminders = (array) data_get($tenantSettings, 'reminders.invoice_due', []);
        $offsets = array_values(array_unique(array_map('intval', (array) ($reminders['pre_due_offsets_days'] ?? [30, 15, 7, 3, 2, 1]))));
        $offsets = array_values(array_filter($offsets, fn ($d) => $d > 0));

        return [
            'enabled' => (bool) ($reminders['enabled'] ?? true),
            'pre_due_offsets_days' => $offsets,
            'post_due_interval_days' => max(1, (int) ($reminders['post_due_interval_days'] ?? 3)),
            'post_due_max_reminders' => isset($reminders['post_due_max_reminders']) ? (int) $reminders['post_due_max_reminders'] : null,
            'post_due_stop_after_days' => isset($reminders['post_due_stop_after_days']) ? (int) $reminders['post_due_stop_after_days'] : null,
            'timezone' => (string) ($tenantSettings['timezone'] ?? 'UTC'),
        ];
    }

    private function isUniqueConstraintViolation(QueryException $e): bool
    {
        $message = $e->getMessage();
        return str_contains($message, 'invoice_reminder_unique') ||
            str_contains($message, 'UNIQUE constraint failed') ||
            str_contains($message, 'Duplicate entry');
    }
}

