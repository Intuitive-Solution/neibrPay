<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceUnit;
use App\Models\Unit;
use App\Services\InvoicePdfService;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    protected $pdfService;
    protected $analytics;

    public function __construct(InvoicePdfService $pdfService, AnalyticsService $analytics)
    {
        $this->pdfService = $pdfService;
        $this->analytics = $analytics;
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
            'items' => 'required|array|min:1',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.line_total' => 'required|numeric|min:0',
            'items.*.charge_id' => 'nullable|integer|exists:charges,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|array',
            'notes.public_notes' => 'nullable|string',
            'notes.private_notes' => 'nullable|string',
            'notes.terms' => 'nullable|string',
            'notes.footer' => 'nullable|string',
            'early_payment_discount_enabled' => 'nullable|boolean',
            'early_payment_discount_amount' => 'nullable|numeric|min:0|max:999999.99|required_if:early_payment_discount_enabled,true',
            'early_payment_discount_type' => 'nullable|in:amount,percentage|required_if:early_payment_discount_enabled,true',
            'early_payment_discount_by_date' => 'nullable|date|required_if:early_payment_discount_enabled,true',
            'late_fee_enabled' => 'nullable|boolean',
            'late_fee_amount' => 'nullable|numeric|min:0|max:999999.99|required_if:late_fee_enabled,true',
            'late_fee_type' => 'nullable|in:amount,percentage|required_if:late_fee_enabled,true',
            'late_fee_applies_on_date' => 'nullable|date|required_if:late_fee_enabled,true',
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
            
            // Track invoice creation in PostHog
            foreach ($createdInvoices as $invoice) {
                $this->analytics->captureEvent('invoice_created_backend', $user->id, [
                    'invoice_id' => $invoice->id,
                    'amount' => $invoice->total,
                    'unit_id' => $invoice->unit_id,
                    'unit_count' => count($unitIds),
                    'frequency' => $validated['frequency'],
                    'source' => 'api',
                    'tenant_id' => $user->tenant_id,
                ]);
            }
            
            // Generate PDF for each created invoice
            foreach ($createdInvoices as $invoice) {
                try {
                    // Load the invoice with necessary relationships for PDF generation
                    $invoice->load(['unit', 'notes', 'tenant']);
                    
                    // Generate HTML for the invoice
                    $html = $this->pdfService->generateInvoiceHtml($invoice);
                    
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
            $ownedUnitIds = $user->ownedUnits()->get()->pluck('id')->toArray();
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
            'items' => 'sometimes|array|min:1',
            'items.*.name' => 'required_with:items|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit_cost' => 'required_with:items|numeric|min:0',
            'items.*.quantity' => 'required_with:items|numeric|min:1',
            'items.*.line_total' => 'required_with:items|numeric|min:0',
            'items.*.charge_id' => 'nullable|integer|exists:charges,id',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|array',
            'notes.public_notes' => 'nullable|string',
            'notes.private_notes' => 'nullable|string',
            'notes.terms' => 'nullable|string',
            'notes.footer' => 'nullable|string',
            'early_payment_discount_enabled' => 'nullable|boolean',
            'early_payment_discount_amount' => 'nullable|numeric|min:0|max:999999.99|required_if:early_payment_discount_enabled,true',
            'early_payment_discount_type' => 'nullable|in:amount,percentage|required_if:early_payment_discount_enabled,true',
            'early_payment_discount_by_date' => 'nullable|date|required_if:early_payment_discount_enabled,true',
            'late_fee_enabled' => 'nullable|boolean',
            'late_fee_amount' => 'nullable|numeric|min:0|max:999999.99|required_if:late_fee_enabled,true',
            'late_fee_type' => 'nullable|in:amount,percentage|required_if:late_fee_enabled,true',
            'late_fee_applies_on_date' => 'nullable|date|required_if:late_fee_enabled,true',
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
            
            // Regenerate PDF for the updated invoice
            try {
                // Load the invoice with necessary relationships for PDF generation
                $invoiceUnit->load(['unit', 'notes', 'tenant']);
                
                // Generate HTML for the invoice
                $html = $this->pdfService->generateInvoiceHtml($invoiceUnit);
                
                // Generate and store new PDF (updates existing record)
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

        // Update invoice status (balance_due is calculated dynamically from payments)
        $invoiceUnit->update([
            'status' => 'paid',
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
            
            $html = $this->pdfService->generateInvoiceHtml($invoiceUnit, $payment);
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
                'items' => $invoiceUnit->items,
                'tax_rate' => $invoiceUnit->tax_rate,
                'early_payment_discount_enabled' => $invoiceUnit->early_payment_discount_enabled ?? false,
                'early_payment_discount_amount' => $invoiceUnit->early_payment_discount_amount,
                'early_payment_discount_type' => $invoiceUnit->early_payment_discount_type,
                'early_payment_discount_by_date' => $invoiceUnit->early_payment_discount_by_date,
                'late_fee_enabled' => $invoiceUnit->late_fee_enabled ?? false,
                'late_fee_amount' => $invoiceUnit->late_fee_amount,
                'late_fee_type' => $invoiceUnit->late_fee_type,
                'late_fee_applies_on_date' => $invoiceUnit->late_fee_applies_on_date,
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
            // Generate magic link token (expires in 30 days, similar to invitation link)
            $magicLinkToken = Str::random(64);
            Cache::put("magic_link:{$magicLinkToken}", [
                'email' => $email,
                'owner_id' => $owner->id,
                'invoice_id' => $invoiceUnit->id,
                'unit_id' => $invoiceUnit->unit_id,
                'created_at' => now(),
            ], now()->addDays(30));
            
            // Build magic link URL that will authenticate user and redirect to invoice
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            
            // Create magic link that authenticates user and redirects to invoice
            // Format: /magic-link?token=...&email=...&redirect=/invoices/{id}
            $magicLink = rtrim($frontendUrl, '/') . '/magic-link?token=' . $magicLinkToken . '&email=' . urlencode($email) . '&redirect=' . urlencode('/invoices/' . $invoiceUnit->id);
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
            'remaining_cycles' => $validated['remaining_cycles'] ?? null,
            'due_date' => $validated['due_date'],
            'items' => $validated['items'],
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'early_payment_discount_enabled' => $validated['early_payment_discount_enabled'] ?? false,
            'early_payment_discount_amount' => $validated['early_payment_discount_amount'] ?? null,
            'early_payment_discount_type' => $validated['early_payment_discount_type'] ?? null,
            'early_payment_discount_by_date' => $validated['early_payment_discount_by_date'] ?? null,
            'late_fee_enabled' => $validated['late_fee_enabled'] ?? false,
            'late_fee_amount' => $validated['late_fee_amount'] ?? null,
            'late_fee_type' => $validated['late_fee_type'] ?? null,
            'late_fee_applies_on_date' => $validated['late_fee_applies_on_date'] ?? null,
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