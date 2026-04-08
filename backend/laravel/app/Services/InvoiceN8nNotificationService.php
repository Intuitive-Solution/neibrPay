<?php

namespace App\Services;

use App\Models\InvoiceUnit;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class InvoiceN8nNotificationService
{
    public function buildInvoicePayload(InvoiceUnit $invoice, User $owner, ?string $email = null): array
    {
        $recipientEmail = $email ?: $owner->email;
        $magicLink = $this->createMagicLink($invoice, $owner, $recipientEmail);

        return [
            'type' => 'invoice',
            'recipient' => [
                'email' => $recipientEmail,
                'name' => $owner->name ?? $owner->email,
            ],
            'cc' => $this->buildCcEmails($invoice, $recipientEmail),
            'invoice_summary' => $this->invoiceSummary($invoice),
            'magic_link' => $magicLink,
            'tenant_name' => $invoice->tenant->name ?? 'HOA',
        ];
    }

    public function buildReminderPayload(
        InvoiceUnit $invoice,
        User $owner,
        string $phase,
        int $phaseDayValue
    ): array {
        $recipientEmail = $owner->email;
        $magicLink = $this->createMagicLink($invoice, $owner, $recipientEmail);
        $payload = [
            'type' => 'invoice_due_reminder',
            'reminder_phase' => $phase,
            'recipient' => [
                'email' => $recipientEmail,
                'name' => $owner->name ?? $owner->email,
            ],
            'cc' => $this->buildCcEmails($invoice, $recipientEmail),
            'invoice_summary' => $this->invoiceSummary($invoice),
            'magic_link' => $magicLink,
            'tenant_name' => $invoice->tenant->name ?? 'HOA',
        ];

        if ($phase === 'pre_due') {
            $payload['days_until_due'] = $phaseDayValue;
        } else {
            $payload['days_overdue'] = $phaseDayValue;
        }

        $lateFeeNotice = $this->buildLateFeeNotice($invoice);
        if ($lateFeeNotice !== null) {
            $payload['late_fee_notice'] = $lateFeeNotice;
        }

        return $payload;
    }

    private function invoiceSummary(InvoiceUnit $invoice): array
    {
        $dueDate = $invoice->getActualDueDate();
        $unit = $invoice->unit;
        $unitAddress = trim(
            ($unit->address ?? '') . ', ' .
            ($unit->city ?? '') . ', ' .
            ($unit->state ?? '') . ' ' .
            ($unit->zip_code ?? '')
        );

        return [
            'invoice_number' => $invoice->invoice_number,
            'total' => number_format((float) $invoice->total, 2, '.', ''),
            'balance_due' => number_format((float) $invoice->balance_due, 2, '.', ''),
            'due_date' => $dueDate->format('Y-m-d'),
            'unit_title' => $unit->title ?? '',
            'unit_address' => trim($unitAddress, ', '),
        ];
    }

    private function buildCcEmails(InvoiceUnit $invoice, string $recipientEmail): array
    {
        $tenantEmail = trim((string) ($invoice->tenant->email ?? ''));
        if ($tenantEmail === '' || strcasecmp($tenantEmail, $recipientEmail) === 0) {
            return [];
        }

        return [$tenantEmail];
    }

    private function buildLateFeeNotice(InvoiceUnit $invoice): ?array
    {
        if (!$invoice->late_fee_enabled || !$invoice->late_fee_amount || !$invoice->late_fee_type) {
            return null;
        }

        $appliesOn = $invoice->late_fee_applies_on_date?->format('Y-m-d');
        if ($invoice->late_fee_type === 'amount') {
            $feeText = '$' . number_format((float) $invoice->late_fee_amount, 2, '.', '');
            $description = "A late fee of {$feeText} may apply";
        } else {
            $feeText = number_format((float) $invoice->late_fee_amount, 2, '.', '') . '%';
            $description = "A late fee of {$feeText} of the balance due may apply";
        }

        if ($appliesOn) {
            $description .= " on or after {$appliesOn} if the balance remains unpaid.";
        } else {
            $description .= ' if the balance remains unpaid.';
        }

        return [
            'applies' => true,
            'applies_on_date' => $appliesOn,
            'description' => $description,
        ];
    }

    private function createMagicLink(InvoiceUnit $invoice, User $owner, string $email): string
    {
        $magicLinkToken = Str::random(64);
        Cache::put("magic_link:{$magicLinkToken}", [
            'email' => $email,
            'owner_id' => $owner->id,
            'invoice_id' => $invoice->id,
            'unit_id' => $invoice->unit_id,
            'created_at' => now(),
        ], now()->addDays(30));

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        return rtrim($frontendUrl, '/') .
            '/magic-link?token=' . $magicLinkToken .
            '&email=' . urlencode($email) .
            '&redirect=' . urlencode('/invoices/' . $invoice->id);
    }
}

