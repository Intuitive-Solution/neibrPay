<?php

namespace Tests\Unit;

use App\Models\InvoiceUnit;
use Tests\TestCase;

class InvoiceUnitCalculateTotalsTest extends TestCase
{
    public function test_calculate_totals_subtracts_early_discount_and_adds_late_fee(): void
    {
        $invoice = new InvoiceUnit([
            'tax_rate' => 10,
            'items' => [
                [
                    'name' => 'Dues',
                    'unit_cost' => 100,
                    'quantity' => 1,
                    'line_total' => 100,
                ],
                [
                    'name' => 'Early Payment Discount',
                    'unit_cost' => 10,
                    'quantity' => 1,
                    'line_total' => 10,
                    'invoice_line_kind' => InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT,
                ],
                [
                    'name' => 'Late Fee',
                    'unit_cost' => 5,
                    'quantity' => 1,
                    'line_total' => 5,
                    'invoice_line_kind' => InvoiceUnit::INVOICE_LINE_KIND_LATE_FEE,
                ],
            ],
        ]);

        $invoice->calculateTotals();

        $this->assertSame(95.0, (float) $invoice->subtotal);
        $this->assertSame(9.5, (float) $invoice->tax_amount);
        $this->assertSame(104.5, (float) $invoice->total);
    }

    public function test_base_subtotal_for_adjustments_excludes_system_lines(): void
    {
        $items = [
            ['line_total' => 200, 'name' => 'A'],
            [
                'line_total' => 50,
                'name' => 'Late Fee',
                'invoice_line_kind' => InvoiceUnit::INVOICE_LINE_KIND_LATE_FEE,
            ],
            [
                'line_total' => 20,
                'name' => 'Discount',
                'invoice_line_kind' => InvoiceUnit::INVOICE_LINE_KIND_EARLY_PAYMENT_DISCOUNT,
            ],
        ];

        $this->assertSame(200.0, InvoiceUnit::baseSubtotalForAdjustments($items));
    }
}
