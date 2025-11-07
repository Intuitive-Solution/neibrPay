<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceUnit;
use App\Models\Unit;
use App\Services\InvoicePdfService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    protected $pdfService;

    public function __construct(InvoicePdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $includeDeleted = $request->boolean('include_deleted', false);
        $unitId = $request->get('unit_id');
        $status = $request->get('status');
        
        $query = InvoiceUnit::forTenant($user->tenant_id)
            ->with(['unit', 'creator', 'notes', 'payments', 'schedule']);
            
        // If user is a resident, filter invoices to only show those for user's owned units
        if ($user->isResident()) {
            $ownedUnitIds = $user->ownedUnits()->get()->pluck('id')->toArray();
            if (empty($ownedUnitIds)) {
                // Resident has no owned units, return empty result
                return response()->json([
                    'data' => [],
                    'meta' => [
                        'total' => 0,
                        'include_deleted' => $includeDeleted,
                        'filters' => [
                            'unit_id' => $unitId,
                            'status' => $status,
                        ],
                    ],
                ]);
            }
            $query->whereIn('unit_id', $ownedUnitIds);
        }
            
        if ($includeDeleted) {
            $query->withTrashed();
        }
        
        if ($unitId) {
            $query->where('unit_id', $unitId);
        }
        
        if ($status) {
            $query->where('status', $status);
        }
        
        $invoices = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'data' => $invoices,
            'meta' => [
                'total' => $invoices->count(),
                'include_deleted' => $includeDeleted,
                'filters' => [
                    'unit_id' => $unitId,
                    'status' => $status,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'integer|exists:units,id',
            'frequency' => 'required|in:one-time,monthly,weekly,quarterly,yearly',
            'start_date' => 'required|date',
            'remaining_cycles' => 'nullable|string',
            'due_date' => 'required|in:use_payment_terms,net_15,net_30,net_45,net_60,due_on_receipt',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'required|in:amount,percentage',
            'auto_bill' => 'required|in:disabled,enabled,on_due_date,on_send',
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.line_total' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|array',
            'notes.public_notes' => 'nullable|string',
            'notes.private_notes' => 'nullable|string',
            'notes.terms' => 'nullable|string',
            'notes.footer' => 'nullable|string',
            'paid_to_date' => 'nullable|numeric|min:0',
        ]);

        // Ensure all units belong to the same tenant
        $unitIds = $validated['unit_ids'];
        $validUnits = Unit::whereIn('id', $unitIds)
            ->where('tenant_id', $user->tenant_id)
            ->pluck('id')
            ->toArray();

        if (count($validUnits) !== count($unitIds)) {
            return response()->json(['message' => 'Some units do not belong to your tenant'], 400);
        }

        DB::beginTransaction();
        
        try {
            $createdInvoices = [];
            $parentInvoice = null;
            
            // If multiple units, create a parent invoice for the first unit
            if (count($unitIds) > 1) {
                $parentInvoice = $this->createInvoiceUnit($validated, $unitIds[0], $user, null);
                $createdInvoices[] = $parentInvoice;
                
                // Create child invoices for remaining units
                for ($i = 1; $i < count($unitIds); $i++) {
                    $childInvoice = $this->createInvoiceUnit($validated, $unitIds[$i], $user, $parentInvoice->id);
                    $createdInvoices[] = $childInvoice;
                }
            } else {
                // Single unit invoice
                $invoice = $this->createInvoiceUnit($validated, $unitIds[0], $user, null);
                $createdInvoices[] = $invoice;
            }
            
            DB::commit();
            
            // Generate PDF for each created invoice
            foreach ($createdInvoices as $invoice) {
                try {
                    // Load the invoice with necessary relationships for PDF generation
                    $invoice->load(['unit', 'notes']);
                    
                    // Generate HTML for the invoice
                    $html = $this->generateInvoiceHtml($invoice);
                    
                    // Generate and store PDF
                    $this->pdfService->generatePdf($invoice, $html, $user->id);
                    
                    \Log::info("PDF generated successfully for new invoice {$invoice->id}");
                } catch (\Exception $e) {
                    // Log error but don't fail the invoice creation
                    \Log::error("Failed to generate PDF for new invoice {$invoice->id}: " . $e->getMessage());
                }
            }
            
            return response()->json([
                'data' => $createdInvoices,
                'message' => 'Invoice(s) created successfully',
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        // Fetch invoice with trashed (deleted) invoices included
        $invoiceUnit = InvoiceUnit::withTrashed()
            ->where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoiceUnit) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // If user is a resident, verify invoice belongs to user's owned units
        if ($user->isResident()) {
            $ownedUnitIds = $user->ownedUnits()->pluck('id')->toArray();
            if (!in_array($invoiceUnit->unit_id, $ownedUnitIds)) {
                return response()->json(['message' => 'Invoice not found'], 403);
            }
        }

        $invoiceUnit->load([
            'unit.owners',
            'creator',
            'notes',
            'payments.recorder',
            'schedule',
            'attachments.uploader',
            'parentInvoice',
            'childInvoices.unit'
        ]);

        return response()->json([
            'data' => $invoiceUnit,
        ]);
    }

    /**
     * Update the specified invoice in storage.
     */
    public function update(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Prevent updating sent/paid invoices
        if (in_array($invoiceUnit->status, ['sent', 'paid'])) {
            return response()->json(['message' => 'Cannot update sent or paid invoices'], 400);
        }

        $validated = $request->validate([
            'unit_id' => 'required|integer|exists:units,id',
            'frequency' => 'required|in:one-time,monthly,weekly,quarterly,yearly',
            'start_date' => 'required|date',
            'remaining_cycles' => 'nullable|string',
            'due_date' => 'required|in:use_payment_terms,net_15,net_30,net_45,net_60,due_on_receipt',
            'po_number' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'discount_type' => 'sometimes|in:amount,percentage',
            'auto_bill' => 'sometimes|in:disabled,enabled,on_due_date,on_send',
            'items' => 'sometimes|array|min:1',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit_cost' => 'required_with:items|numeric|min:0',
            'items.*.quantity' => 'required_with:items|numeric|min:1',
            'items.*.line_total' => 'required_with:items|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'paid_to_date' => 'nullable|numeric|min:0',
            'notes' => 'nullable|array',
            'notes.public_notes' => 'nullable|string',
            'notes.private_notes' => 'nullable|string',
            'notes.terms' => 'nullable|string',
            'notes.footer' => 'nullable|string',
        ]);

        // Ensure the new unit belongs to the user's tenant
        if (isset($validated['unit_id'])) {
            $newUnit = Unit::where('id', $validated['unit_id'])
                ->where('tenant_id', $user->tenant_id)
                ->first();
            
            if (!$newUnit) {
                return response()->json(['message' => 'Unit does not belong to your tenant'], 400);
            }
        }

        DB::beginTransaction();
        
        try {
            // Update invoice fields (exclude items and notes which are handled separately)
            $updateFields = array_filter($validated, function($key) {
                return !in_array($key, ['items', 'notes']);
            }, ARRAY_FILTER_USE_KEY);
            
            $invoiceUnit->update($updateFields);

            // Update items if provided
            if (isset($validated['items'])) {
                $invoiceUnit->items = $validated['items'];
            }

            // Recalculate totals
            $invoiceUnit->calculateTotals();
            $invoiceUnit->save();

            // Update notes if provided
            if (isset($validated['notes'])) {
                $this->updateInvoiceNotes($invoiceUnit, $validated['notes']);
            }
            
            // Generate new PDF with is_latest = true (mark previous PDFs as is_latest = false)
            try {
                // Mark all existing PDFs as not latest
                $invoiceUnit->pdfs()->update(['is_latest' => false]);
                
                // Load the invoice with necessary relationships for PDF generation
                $invoiceUnit->load(['unit', 'notes']);
                
                // Generate HTML for the invoice
                $html = $this->generateInvoiceHtml($invoiceUnit);
                
                // Generate and store new PDF with is_latest = true
                $this->pdfService->generatePdf($invoiceUnit, $html, $user->id);
                
                \Log::info("PDF regenerated successfully for updated invoice {$invoiceUnit->id}");
            } catch (\Exception $e) {
                // Log error but don't fail the invoice update
                \Log::error("Failed to regenerate PDF for updated invoice {$invoiceUnit->id}: " . $e->getMessage());
            }
            
            DB::commit();
            
            $invoiceUnit->load(['unit', 'creator', 'notes', 'payments', 'schedule']);
            
            return response()->json([
                'data' => $invoiceUnit,
                'message' => 'Invoice updated successfully',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice from storage (soft delete).
     */
    public function destroy(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Prevent deleting sent/paid invoices
        if (in_array($invoiceUnit->status, ['sent', 'paid'])) {
            return response()->json(['message' => 'Cannot delete sent or paid invoices'], 400);
        }

        $invoiceUnit->delete();

        return response()->json([
            'message' => 'Invoice deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted invoice.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $invoiceUnit = InvoiceUnit::withTrashed()
            ->where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoiceUnit) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if (!$invoiceUnit->trashed()) {
            return response()->json(['message' => 'Invoice is not deleted'], 400);
        }

        $invoiceUnit->restore();
        $invoiceUnit->load(['unit', 'creator', 'notes', 'payments', 'schedule']);

        return response()->json([
            'data' => $invoiceUnit,
            'message' => 'Invoice restored successfully',
        ]);
    }

    /**
     * Permanently delete an invoice (admin only).
     */
    public function forceDelete(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $invoiceUnit = InvoiceUnit::withTrashed()
            ->where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$invoiceUnit) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $invoiceUnit->forceDelete();

        return response()->json([
            'message' => 'Invoice permanently deleted',
        ]);
    }

    /**
     * Mark an invoice as sent.
     */
    public function markAsSent(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if ($invoiceUnit->status !== 'draft') {
            return response()->json(['message' => 'Only draft invoices can be marked as sent'], 400);
        }

        $invoiceUnit->update(['status' => 'sent']);
        $invoiceUnit->load(['unit', 'creator', 'notes', 'payments', 'schedule']);

        return response()->json([
            'data' => $invoiceUnit,
            'message' => 'Invoice marked as sent',
        ]);
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if ($invoiceUnit->status === 'paid') {
            return response()->json(['message' => 'Invoice is already marked as paid'], 400);
        }

        // Update invoice status and paid amount
        $invoiceUnit->update([
            'status' => 'paid',
            'paid_to_date' => $invoiceUnit->total,
            'balance_due' => 0, // Set balance_due to 0 when fully paid
        ]);

        // Create a payment record
        $payment = $invoiceUnit->payments()->create([
            'amount' => $invoiceUnit->total,
            'payment_method' => 'other',
            'payment_reference' => 'Marked as paid',
            'notes' => 'Invoice marked as paid by admin',
            'payment_date' => now()->format('Y-m-d H:i:s'),
            'recorded_by' => $user->id,
        ]);

        // Regenerate PDF with payment details
        try {
            // Refresh the payment object to ensure we have the latest data
            $payment->refresh();
            
            // Debug: Log the payment data before PDF generation
            \Log::info('Payment data before PDF generation:', [
                'payment_id' => $payment->id,
                'payment_date' => $payment->payment_date,
                'payment_method' => $payment->payment_method,
                'payment_reference' => $payment->payment_reference,
            ]);
            
            // Check if payment date is invalid and fix it
            if (!$payment->payment_date || $payment->payment_date === 'use_payment_terms' || strpos($payment->payment_date, 'use_') === 0) {
                \Log::warning('Invalid payment date detected, fixing it', [
                    'payment_id' => $payment->id,
                    'invalid_date' => $payment->payment_date,
                ]);
                
                $payment->update(['payment_date' => now()->format('Y-m-d H:i:s')]);
                $payment->refresh();
            }
            
            $html = $this->generateInvoiceHtml($invoiceUnit, $payment);
            $this->pdfService->generatePdf($invoiceUnit, $html, $user->id);
        } catch (\Exception $e) {
            // Log error but don't fail the payment marking
            \Log::error('Failed to regenerate PDF after marking as paid: ' . $e->getMessage());
        }

        $invoiceUnit->load(['unit', 'creator', 'notes', 'payments', 'schedule']);

        return response()->json([
            'data' => $invoiceUnit,
            'message' => 'Invoice marked as paid',
        ]);
    }

    /**
     * Generate HTML for invoice with optional payment details.
     */
    private function generateInvoiceHtml(InvoiceUnit $invoiceUnit, $payment = null): string
    {
        $unit = $invoiceUnit->unit;
        $unitTitle = $unit ? $unit->title : "Unit {$invoiceUnit->unit_id}";
        $unitAddress = $unit ? "{$unit->address}, {$unit->city}" : '';
        $unitResident = $unit ? $unit->resident_name : '';

        $formatDate = function ($dateString) {
            if (!$dateString || $dateString === 'use_payment_terms' || strpos($dateString, 'use_') === 0) {
                return '';
            }
            
            try {
                // Handle different date formats
                if (is_object($dateString) && method_exists($dateString, 'format')) {
                    return $dateString->format('F j, Y');
                }
                
                return \Carbon\Carbon::parse($dateString)->format('F j, Y');
            } catch (\Exception $e) {
                // Log the error for debugging
                \Log::warning('Failed to parse date in PDF generation', [
                    'date_string' => $dateString,
                    'date_type' => gettype($dateString),
                    'error' => $e->getMessage(),
                ]);
                return '';
            }
        };

        // Only include payment method formatter if payment is provided
        $formatPaymentMethod = null;
        if ($payment) {
            $formatPaymentMethod = function ($method) {
                $methodMap = [
                    'cash' => 'Cash',
                    'check' => 'Check',
                    'credit_card' => 'Credit Card',
                    'bank_transfer' => 'Bank Transfer',
                    'other' => 'Other'
                ];
                return $methodMap[$method] ?? $method;
            };
        }

        $publicNotes = $invoiceUnit->notes->where('type', 'public_notes')->first()?->content ?? '';
        $terms = $invoiceUnit->notes->where('type', 'terms')->first()?->content ?? '';
        $footer = $invoiceUnit->notes->where('type', 'footer')->first()?->content ?? '';

        $itemsHtml = '';
        foreach ($invoiceUnit->items as $item) {
            $itemsHtml .= "
            <tr>
                <td class=\"item-name\">{$item['name']}</td>
                <td class=\"item-description\">" . ($item['description'] ?? '') . "</td>
                <td class=\"item-cost\">$" . number_format((float)$item['unit_cost'], 2) . "</td>
                <td class=\"item-quantity\">{$item['quantity']}</td>
                <td class=\"item-total\">$" . number_format((float)$item['line_total'], 2) . "</td>
            </tr>";
        }

        $discountHtml = '';
        if ($invoiceUnit->discount_amount && $invoiceUnit->discount_amount > 0) {
            $discountValue = $invoiceUnit->discount_type === 'percentage' 
                ? ($invoiceUnit->subtotal * $invoiceUnit->discount_amount) / 100 
                : $invoiceUnit->discount_amount;
            $discountHtml = "
            <div class=\"total-row clearfix\">
                <span class=\"total-label\">Discount (" . ($invoiceUnit->discount_type === 'percentage' ? $invoiceUnit->discount_amount . '%' : 'Amount') . "):</span>
                <span class=\"total-value\">-$" . number_format((float)$discountValue, 2) . "</span>
            </div>";
        }

        $taxHtml = '';
        if ($invoiceUnit->tax_rate > 0) {
            $taxHtml = "
            <div class=\"total-row clearfix\">
                <span class=\"total-label\">Tax ({$invoiceUnit->tax_rate}%):</span>
                <span class=\"total-value\">$" . number_format((float)$invoiceUnit->tax_amount, 2) . "</span>
            </div>";
        }

        $paidToDateHtml = '';
        if ($invoiceUnit->paid_to_date > 0) {
            $paidToDateHtml = "
            <div class=\"total-row clearfix\">
                <span class=\"total-label\">Paid to Date:</span>
                <span class=\"total-value\">$" . number_format((float)$invoiceUnit->paid_to_date, 2) . "</span>
            </div>";
        }

        $balanceDueHtml = '';
        if ($invoiceUnit->balance_due > 0) {
            $balanceDueHtml = "
            <div class=\"total-row balance-due clearfix\">
                <span class=\"total-label\">Balance Due:</span>
                <span class=\"total-value\">$" . number_format((float)$invoiceUnit->balance_due, 2) . "</span>
            </div>";
        }

        // Conditional payment details HTML
        $paymentDetailsHtml = '';
        if ($payment) {
            // Debug: Log the payment data to see what we're getting
            \Log::info('Payment data for PDF generation:', [
                'payment_id' => $payment->id,
                'payment_date' => $payment->payment_date,
                'payment_date_type' => gettype($payment->payment_date),
                'payment_method' => $payment->payment_method,
                'payment_reference' => $payment->payment_reference,
            ]);

            // Get payment date with fallback - handle any invalid date values
            $paymentDate = $payment->payment_date;
            
            // Handle different date formats and invalid values
            if (!$paymentDate || $paymentDate === 'use_payment_terms' || strpos($paymentDate, 'use_') === 0) {
                $paymentDate = now()->format('Y-m-d H:i:s');
            } elseif (is_object($paymentDate) && method_exists($paymentDate, 'format')) {
                // If it's a Carbon instance, format it
                $paymentDate = $paymentDate->format('Y-m-d H:i:s');
            } elseif (!is_string($paymentDate)) {
                // If it's not a string, convert it
                $paymentDate = now()->format('Y-m-d H:i:s');
            }

            $paymentDetailsHtml = "
            <div class=\"payment-details-section\">
                <h3 class=\"section-title\">Payment Details:</h3>
                <div class=\"payment-details-content\">
                    <p><strong>Payment Date:</strong> {$formatDate($paymentDate)}</p>
                    <p><strong>Payment Method:</strong> {$formatPaymentMethod($payment->payment_method)}</p>
                    <p><strong>Reference:</strong> {$payment->payment_reference}</p>
                </div>
            </div>";
        }

        $notesHtml = $publicNotes ? "
        <div class=\"notes-section\">
            <h3 class=\"section-title\">Notes:</h3>
            <div class=\"notes-content\">{$publicNotes}</div>
        </div>" : '';

        $termsHtml = $terms ? "
        <div class=\"terms-section\">
            <h3 class=\"section-title\">Terms & Conditions:</h3>
            <div class=\"terms-content\">{$terms}</div>
        </div>" : '';

        $footerHtml = $footer ? "
        <div class=\"footer-section\">
            <div class=\"footer-content\">{$footer}</div>
        </div>" : '';

        // Conditional PAID stamp
        $paidStampHtml = $payment ? '
                    <div class="paid-stamp">
                        <div class="paid-stamp-content">PAID</div>
                    </div>' : '';

        // Conditional CSS for payment details
        $paymentDetailsCss = $payment ? '
                .payment-details-section { margin-bottom: 20px; padding: 15px 15px 15px 15px; margin-right: 5mm; background-color: #f0fdf4; border: 2px solid #10b981; border-radius: 6px; }
                .payment-details-content p { margin: 4px 0; font-size: 10px; color: #1f2937; }
                .paid-stamp { position: absolute; top: 10px; right: 15mm; z-index: 10; }
                .paid-stamp-content { background: #10b981; color: white; padding: 4px 8px; border-radius: 3px; font-weight: bold; font-size: 12px; text-align: center; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); transform: rotate(-15deg); }' : '';

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset=\"utf-8\">
            <title>Invoice {$invoiceUnit->invoice_number}</title>
            <style>
                * { box-sizing: border-box; }
                body { font-family: Arial, sans-serif; margin: 0; padding: 0; background: #f5f5f5; }
                .invoice-template { width: 180mm; max-width: 180mm; min-height: 297mm; padding: 10mm 8mm 10mm 10mm; font-family: Arial, sans-serif; background: white; color: #333; line-height: 1.3; overflow: hidden; }
                .invoice-header { width: 100%; margin-bottom: 25px; border-bottom: 3px solid #2563eb; padding-bottom: 15px; overflow: hidden; position: relative; }
                .company-info { float: left; width: 55%; }
                .company-name { font-size: 18px; font-weight: bold; color: #2563eb; margin: 0 0 4px 0; }
                .company-details p { margin: 1px 0; font-size: 9px; color: #666; }
                .invoice-meta { float: right; width: 40%; text-align: right; padding-right: 5mm; }
                .invoice-title { font-size: 18px; font-weight: bold; color: #1f2937; margin: 0 0 6px 0; }
                .invoice-details p { margin: 1px 0; font-size: 9px; }
                .bill-to-section { margin-bottom: 25px; }
                .section-title { font-size: 12px; font-weight: bold; color: #1f2937; margin: 0 0 6px 0; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; }
                .unit-header h4 { font-size: 12px; font-weight: bold; margin: 0 0 3px 0; color: #1f2937; }
                .unit-details p { margin: 1px 0; font-size: 10px; color: #666; }
                .items-section { margin-bottom: 25px; }
                .items-table { width: 100%; border-collapse: collapse; margin: 0; table-layout: fixed; }
                .items-table th { background-color: #f8fafc; color: #1f2937; font-weight: bold; padding: 6px 4px; text-align: left; border: 1px solid #e5e7eb; font-size: 10px; }
                .items-table td { padding: 6px 4px; border: 1px solid #e5e7eb; font-size: 10px; vertical-align: top; word-wrap: break-word; }
                .item-name { width: 18%; font-weight: 500; }
                .item-description { width: 32%; }
                .item-cost { width: 15%; text-align: right; }
                .item-quantity { width: 10%; text-align: right; }
                .item-total { width: 15%; text-align: right; font-weight: 500; }
                .totals-section { margin-bottom: 25px; }
                .totals-container { max-width: 220px; margin-left: auto; margin-right: 5mm; }
                .total-row { display: flex; justify-content: space-between; align-items: center; padding: 6px 0; border-bottom: 1px solid #f3f4f6; }
                .total-label { font-size: 10px; color: #6b7280; }
                .total-value { font-size: 10px; font-weight: 500; color: #1f2937; }
                .final-total { border-top: 2px solid #1f2937; border-bottom: 2px solid #1f2937; font-weight: bold; font-size: 12px; margin-top: 6px; padding: 8px 0; }
                .final-total .total-label, .final-total .total-value { font-size: 12px; font-weight: bold; }
                .balance-due { background-color: #fef2f2; border: 1px solid #fecaca; border-radius: 3px; padding: 8px 12px; margin-top: 8px; }
                .balance-due .total-label, .balance-due .total-value { color: #dc2626; font-weight: bold; }
                .payment-section { margin-top: 20px; padding-top: 12px; border-top: 1px solid #e5e7eb; }
                .payment-details p { margin: 3px 0; font-size: 9px; color: #4b5563; }
                .notes-section, .terms-section { margin-bottom: 15px; }
                .notes-content, .terms-content { font-size: 10px; line-height: 1.4; color: #4b5563; margin-top: 6px; }
                .footer-section { margin-bottom: 15px; padding-top: 12px; border-top: 1px solid #e5e7eb; }
                .footer-content { font-size: 9px; color: #6b7280; text-align: center; }
                .clearfix::after { content: \"\"; display: table; clear: both; }
                {$paymentDetailsCss}
            </style>
        </head>
        <body>
            <div class=\"invoice-template\">
                <div class=\"invoice-header clearfix\">
                    {$paidStampHtml}
                    <div class=\"company-info\">
                        <h1 class=\"company-name\">NeibrPay HOA</h1>
                        <div class=\"company-details\">
                            <p>123 HOA Management Street</p>
                            <p>Property City, PC 12345</p>
                            <p>Phone: (555) 123-4567</p>
                            <p>Email: info@neibrpay.com</p>
                        </div>
                    </div>
                    <div class=\"invoice-meta\">
                        <h2 class=\"invoice-title\">INVOICE</h2>
                        <div class=\"invoice-details\">
                            <p><strong>Invoice #:</strong> {$invoiceUnit->invoice_number}</p>
                            <p><strong>Date:</strong> {$formatDate($invoiceUnit->start_date)}</p>
                            <p><strong>Due Date:</strong> {$formatDate($invoiceUnit->getActualDueDate())}</p>
                        </div>
                    </div>
                </div>

                <div class=\"bill-to-section\">
                    <h3 class=\"section-title\">Bill To:</h3>
                    <div class=\"unit-header\">
                        <h4>{$unitTitle}</h4>
                    </div>
                    <div class=\"unit-details\">
                        <p>{$unitAddress}</p>
                        <p>{$unitResident}</p>
                    </div>
                </div>

                <div class=\"items-section\">
                    <table class=\"items-table\">
                        <thead>
                            <tr>
                                <th class=\"item-name\">Item</th>
                                <th class=\"item-description\">Description</th>
                                <th class=\"item-cost\">Unit Cost</th>
                                <th class=\"item-quantity\">Qty</th>
                                <th class=\"item-total\">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            {$itemsHtml}
                        </tbody>
                    </table>
                </div>

                <div class=\"totals-section\">
                    <div class=\"totals-container\">
                        <div class=\"total-row\">
                            <span class=\"total-label\">Subtotal:</span>
                            <span class=\"total-value\">$" . number_format((float)$invoiceUnit->subtotal, 2) . "</span>
                        </div>
                        {$discountHtml}
                        {$taxHtml}
                        <div class=\"total-row final-total\">
                            <span class=\"total-label\">Total:</span>
                            <span class=\"total-value\">$" . number_format((float)$invoiceUnit->total, 2) . "</span>
                        </div>
                        {$paidToDateHtml}
                        {$balanceDueHtml}
                    </div>
                </div>

                {$paymentDetailsHtml}

                {$notesHtml}
                {$termsHtml}
                {$footerHtml}

                <div class=\"payment-section\">
                    <h3 class=\"section-title\">Payment Information</h3>
                    <div class=\"payment-details\">
                        <p><strong>Payment Methods:</strong> Check, Bank Transfer, Online Payment</p>
                        <p><strong>Make checks payable to:</strong> NeibrPay HOA</p>
                        <p><strong>For questions about this invoice, contact:</strong> (555) 123-4567</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }


    /**
     * Clone an existing invoice.
     */
    public function clone(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        DB::beginTransaction();
        
        try {
            // Create a new invoice with the same data
            $clonedInvoice = new InvoiceUnit([
                'tenant_id' => $user->tenant_id,
                'unit_id' => $invoiceUnit->unit_id,
                'frequency' => $invoiceUnit->frequency,
                'start_date' => now()->format('Y-m-d'), // Set start date to today
                'remaining_cycles' => $invoiceUnit->remaining_cycles,
                'due_date' => $invoiceUnit->due_date,
                'discount_amount' => $invoiceUnit->discount_amount,
                'discount_type' => $invoiceUnit->discount_type,
                'auto_bill' => $invoiceUnit->auto_bill,
                'items' => $invoiceUnit->items,
                'tax_rate' => $invoiceUnit->tax_rate,
                'status' => 'draft',
                'created_by' => $user->id,
            ]);

            // Generate new invoice number
            $clonedInvoice->generateInvoiceNumber();
            
            // Calculate totals
            $clonedInvoice->calculateTotals();
            
            $clonedInvoice->save();

            // Clone notes if they exist
            if ($invoiceUnit->notes) {
                foreach ($invoiceUnit->notes as $note) {
                    $clonedInvoice->notes()->create([
                        'type' => $note->type,
                        'content' => $note->content,
                    ]);
                }
            }

            // Create schedule for recurring invoices
            if ($invoiceUnit->frequency !== 'one-time') {
                $this->createInvoiceSchedule($clonedInvoice);
            }

            DB::commit();

            $clonedInvoice->load(['unit', 'creator', 'notes', 'payments', 'schedule']);

            return response()->json([
                'data' => [$clonedInvoice],
                'message' => 'Invoice cloned successfully',
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to clone invoice: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Email an invoice.
     */
    public function email(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        $validated = $request->validate([
            'email' => 'nullable|email',
        ]);

        // Load invoice with relationships
        // First ensure unit is loaded, then load owners
        if (!$invoiceUnit->relationLoaded('unit')) {
            $invoiceUnit->load('unit');
        }
        
        // Check if unit exists
        if (!$invoiceUnit->unit) {
            Log::error('Invoice unit not found for invoice', [
                'invoice_id' => $invoiceUnit->id,
                'unit_id' => $invoiceUnit->unit_id,
            ]);
            return response()->json(['message' => 'Unit not found for this invoice'], 400);
        }
        
        // Load owners relationship if not already loaded
        if (!$invoiceUnit->unit->relationLoaded('owners')) {
            $invoiceUnit->unit->load('owners');
        }
        
        // Load tenant if not already loaded
        if (!$invoiceUnit->relationLoaded('tenant')) {
            $invoiceUnit->load('tenant');
        }

        // Get the owner - use provided email or first unit owner
        $owner = null;
        $email = $validated['email'] ?? null;
        
        if ($email) {
            // Find owner by email
            $owner = $invoiceUnit->unit->owners->firstWhere('email', $email);
            if (!$owner) {
                Log::warning('Owner not found with provided email', [
                    'invoice_id' => $invoiceUnit->id,
                    'unit_id' => $invoiceUnit->unit_id,
                    'provided_email' => $email,
                ]);
            }
        } else {
            // Use first owner from unit
            $owner = $invoiceUnit->unit->owners->first();
            if ($owner) {
                $email = $owner->email;
            }
        }
        
        // If no owner found, check if unit has any owners
        if (!$owner) {
            $ownersCount = $invoiceUnit->unit->owners->count();
            Log::warning('No owner found for invoice email', [
                'invoice_id' => $invoiceUnit->id,
                'unit_id' => $invoiceUnit->unit_id,
                'unit_title' => $invoiceUnit->unit->title,
                'owners_count' => $ownersCount,
            ]);
            
            if ($ownersCount === 0) {
                return response()->json([
                    'message' => 'No owners assigned to this unit. Please assign an owner with an email address before sending the invoice.',
                ], 400);
            } else {
                return response()->json([
                    'message' => 'No owner with a valid email address found for this unit. Please ensure at least one owner has an email address.',
                ], 400);
            }
        }
        
        // Check if owner has an email
        if (!$email || empty(trim($email))) {
            Log::warning('Owner found but no email address', [
                'invoice_id' => $invoiceUnit->id,
                'unit_id' => $invoiceUnit->unit_id,
                'owner_id' => $owner->id,
                'owner_name' => $owner->name ?? 'N/A',
            ]);
            return response()->json([
                'message' => 'The owner assigned to this unit does not have an email address. Please update the owner\'s email address.',
            ], 400);
        }

        // Check if n8n webhook URL is configured
        $n8nWebhookUrl = config('n8n.webhook_url');
        if (!$n8nWebhookUrl) {
            Log::warning('N8N_WEBHOOK_URL not configured. Email invoice webhook not sent.');
            return response()->json([
                'message' => 'Email service not configured. Invoice email not sent.',
            ], 500);
        }

        // Get webhook secret token for authentication
        $webhookSecret = config('n8n.webhook_secret');
        if (!$webhookSecret) {
            Log::warning('N8N_WEBHOOK_SECRET not configured. Webhook may be unsecured.');
        }

        try {
            // Build invoice view link (using signed URL for security)
            $appUrl = config('app.url');
            $frontendUrl = config('app.frontend_url', $appUrl);
            
            // Create a signed URL that expires in 30 days for invoice viewing
            // For now, use a simple link - can be enhanced with signed URLs later
            $magicLink = rtrim($frontendUrl, '/') . '/invoices/' . $invoiceUnit->id;

            // Calculate due date
            $dueDate = $invoiceUnit->getActualDueDate();
            $dueDateFormatted = $dueDate->format('Y-m-d');

            // Build unit address string
            $unit = $invoiceUnit->unit;
            $unitAddress = trim(
                ($unit->address ?? '') . ', ' . 
                ($unit->city ?? '') . ', ' . 
                ($unit->state ?? '') . ' ' . 
                ($unit->zip_code ?? '')
            );
            $unitAddress = trim($unitAddress, ', ');

            // Prepare n8n webhook payload
            $payload = [
                'type' => 'invoice',
                'recipient' => [
                    'email' => $email,
                    'name' => $owner->name ?? $owner->email,
                ],
                'invoice_summary' => [
                    'invoice_number' => $invoiceUnit->invoice_number,
                    'total' => number_format((float) $invoiceUnit->total, 2, '.', ''),
                    'balance_due' => number_format((float) $invoiceUnit->balance_due, 2, '.', ''),
                    'due_date' => $dueDateFormatted,
                    'unit_title' => $unit->title ?? '',
                    'unit_address' => $unitAddress,
                ],
                'magic_link' => $magicLink,
                'tenant_name' => $invoiceUnit->tenant->name ?? 'HOA',
            ];

            Log::info('Sending n8n webhook request', [
                'url' => $n8nWebhookUrl,
                'has_secret' => !empty($webhookSecret),
                'payload_keys' => array_keys($payload),
            ]);
            // Prepare headers for webhook request
            $headers = [
                'Content-Type' => 'application/json',
            ];

            // Add authentication token if configured
            if ($webhookSecret) {
                $headers['X-Webhook-Token'] = $webhookSecret;
            }

            // Send HTTP POST request to n8n webhook
            // Note: Using synchronous call to catch errors, but it's fast enough
            try {
                $response = Http::timeout(10)
                    ->retry(2, 100) // Retry twice with 100ms delay
                    ->withHeaders($headers)
                    ->post($n8nWebhookUrl, $payload);
                
                if ($response->successful()) {
                    Log::info('n8n webhook sent successfully', [
                        'status' => $response->status(),
                        'response_body' => substr($response->body(), 0, 200), // First 200 chars
                    ]);
                    
                    // Update invoice status to 'sent' when email is successfully sent
                    if ($invoiceUnit->status === 'draft') {
                        $invoiceUnit->update(['status' => 'sent']);
                        Log::info('Invoice status updated to sent', [
                            'invoice_id' => $invoiceUnit->id,
                            'invoice_number' => $invoiceUnit->invoice_number,
                        ]);
                    }
                } else {
                    $errorMessage = 'n8n webhook returned non-success status';
                    $responseBody = $response->body();
                    
                    // Provide helpful error messages for common issues
                    if ($response->status() === 404) {
                        $errorMessage = 'n8n webhook not found - workflow may not be activated';
                        if (strpos($responseBody, 'not registered') !== false) {
                            Log::warning($errorMessage . '. Make sure the n8n workflow is ACTIVE (not in test mode).', [
                                'status' => $response->status(),
                                'url' => $n8nWebhookUrl,
                                'hint' => 'Activate the workflow in n8n and use production webhook URL (remove /webhook-test/)',
                            ]);
                        } else {
                            Log::warning($errorMessage, [
                                'status' => $response->status(),
                                'response_body' => $responseBody,
                                'url' => $n8nWebhookUrl,
                            ]);
                        }
                    } else {
                        Log::warning('n8n webhook returned non-success status', [
                            'status' => $response->status(),
                            'response_body' => substr($responseBody, 0, 500),
                            'url' => $n8nWebhookUrl,
                        ]);
                    }
                }
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                Log::error('n8n webhook connection failed - network error', [
                    'error' => $e->getMessage(),
                    'url' => $n8nWebhookUrl,
                    'timeout' => 10,
                ]);
            } catch (\Illuminate\Http\Client\RequestException $e) {
                Log::error('n8n webhook request exception', [
                    'error' => $e->getMessage(),
                    'response' => $e->response ? [
                        'status' => $e->response->status(),
                        'body' => substr($e->response->body(), 0, 500),
                    ] : null,
                    'url' => $n8nWebhookUrl,
                ]);
            } catch (\Exception $e) {
                Log::error('n8n webhook request failed - unexpected error', [
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                    'url' => $n8nWebhookUrl,
                ]);
            }

            return response()->json([
                'message' => 'Invoice email sent successfully',
            ]);

        } catch (\Exception $e) {
            // Log error but don't fail the request
            Log::error('Failed to send invoice email via n8n webhook: ' . $e->getMessage(), [
                'invoice_id' => $invoiceUnit->id,
                'owner_email' => $email,
                'exception' => $e,
            ]);

            return response()->json([
                'message' => 'Invoice email queued. An error occurred but the request was processed.',
            ], 200);
        }
    }

    /**
     * Get invoices for a specific unit.
     */
    public function forUnit(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the unit belongs to the user's tenant
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $invoices = InvoiceUnit::forUnit($unit->id)
            ->with(['creator', 'notes', 'payments', 'schedule'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $invoices,
            'meta' => [
                'unit_id' => $unit->id,
                'total' => $invoices->count(),
            ],
        ]);
    }

    /**
     * Create a single invoice unit.
     */
    private function createInvoiceUnit(array $validated, int $unitId, $user, ?int $parentInvoiceId = null): InvoiceUnit
    {
        $unit = Unit::find($unitId);
        
        $invoiceUnit = new InvoiceUnit([
            'tenant_id' => $user->tenant_id,
            'unit_id' => $unitId,
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'remaining_cycles' => $validated['remaining_cycles'],
            'due_date' => $validated['due_date'],
            'discount_amount' => $validated['discount_amount'] ?? 0,
            'discount_type' => $validated['discount_type'],
            'auto_bill' => $validated['auto_bill'],
            'items' => $validated['items'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'paid_to_date' => $validated['paid_to_date'] ?? 0,
            'status' => 'draft',
            'parent_invoice_id' => $parentInvoiceId,
            'created_by' => $user->id,
        ]);

        // Generate invoice number
        $invoiceUnit->generateInvoiceNumber();
        
        // Calculate totals
        $invoiceUnit->calculateTotals();
        
        $invoiceUnit->save();

        // Create notes if provided
        if (isset($validated['notes'])) {
            $this->createInvoiceNotes($invoiceUnit, $validated['notes']);
        }

        // Create schedule for recurring invoices
        if ($validated['frequency'] !== 'one-time') {
            $this->createInvoiceSchedule($invoiceUnit);
        }

        return $invoiceUnit;
    }

    /**
     * Create invoice notes.
     */
    private function createInvoiceNotes(InvoiceUnit $invoiceUnit, array $notes): void
    {
        foreach ($notes as $type => $content) {
            if (!empty($content)) {
                $invoiceUnit->notes()->create([
                    'type' => $type,
                    'content' => $content,
                ]);
            }
        }
    }

    /**
     * Update invoice notes.
     */
    private function updateInvoiceNotes(InvoiceUnit $invoiceUnit, array $notes): void
    {
        foreach ($notes as $type => $content) {
            $note = $invoiceUnit->notes()->where('type', $type)->first();
            
            if (empty($content)) {
                if ($note) {
                    $note->delete();
                }
            } else {
                if ($note) {
                    $note->update(['content' => $content]);
                } else {
                    $invoiceUnit->notes()->create([
                        'type' => $type,
                        'content' => $content,
                    ]);
                }
            }
        }
    }

    /**
     * Create invoice schedule for recurring invoices.
     */
    private function createInvoiceSchedule(InvoiceUnit $invoiceUnit): void
    {
        $schedule = new \App\Models\InvoiceSchedule([
            'invoice_unit_id' => $invoiceUnit->id,
            'next_due_date' => $invoiceUnit->start_date,
            'remaining_cycles' => $invoiceUnit->remaining_cycles === 'endless' ? null : (int)$invoiceUnit->remaining_cycles,
            'is_active' => true,
        ]);

        $schedule->calculateNextDueDate();
        $schedule->save();
    }
}