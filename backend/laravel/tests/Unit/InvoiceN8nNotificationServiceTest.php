<?php

namespace Tests\Unit;

use App\Models\InvoiceUnit;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\InvoiceN8nNotificationService;
use Tests\TestCase;

class InvoiceN8nNotificationServiceTest extends TestCase
{
    public function test_reminder_payload_adds_cc_and_dedupes_same_email(): void
    {
        $service = new InvoiceN8nNotificationService();

        $tenant = new Tenant([
            'name' => 'Demo HOA',
            'email' => 'hoa@example.com',
        ]);
        $unit = new Unit([
            'title' => 'A-101',
            'address' => '123 Main',
            'city' => 'Austin',
            'state' => 'TX',
            'zip_code' => '78701',
        ]);
        $owner = new User([
            'id' => 100,
            'name' => 'Resident One',
            'email' => 'resident@example.com',
        ]);

        $invoice = new InvoiceUnit([
            'id' => 200,
            'tenant_id' => 1,
            'unit_id' => 10,
            'invoice_number' => 'INV-001',
            'start_date' => '2026-03-01',
            'due_date' => 'net_15',
            'total' => 100.00,
            'late_fee_enabled' => true,
            'late_fee_amount' => 5,
            'late_fee_type' => 'percentage',
            'late_fee_applies_on_date' => '2026-03-20',
            'status' => 'sent',
            'items' => [],
        ]);
        $invoice->setRelation('tenant', $tenant);
        $invoice->setRelation('unit', $unit);
        $invoice->setRelation('payments', collect());

        $payload = $service->buildReminderPayload($invoice, $owner, 'pre_due', 7);

        $this->assertSame('invoice_due_reminder', $payload['type']);
        $this->assertSame(['hoa@example.com'], $payload['cc']);
        $this->assertSame(7, $payload['days_until_due']);
        $this->assertArrayHasKey('late_fee_notice', $payload);

        $tenantSameAsRecipient = new Tenant([
            'name' => 'Demo HOA',
            'email' => 'resident@example.com',
        ]);
        $invoice->setRelation('tenant', $tenantSameAsRecipient);
        $payloadNoDup = $service->buildReminderPayload($invoice, $owner, 'post_due', 3);
        $this->assertSame([], $payloadNoDup['cc']);
        $this->assertSame(3, $payloadNoDup['days_overdue']);
    }
}

