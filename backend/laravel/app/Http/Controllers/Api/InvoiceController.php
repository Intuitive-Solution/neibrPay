<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoiceUnit;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->get('firebase_user');
        $includeDeleted = $request->boolean('include_deleted', false);
        $unitId = $request->get('unit_id');
        $status = $request->get('status');
        
        $query = InvoiceUnit::forTenant($user->tenant_id)
            ->with(['unit', 'creator', 'notes', 'payments', 'schedule']);
            
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
        $user = $request->get('firebase_user');
        
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
            'po_number' => 'nullable|string|max:255',
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
    public function show(Request $request, InvoiceUnit $invoiceUnit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
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
        $user = $request->get('firebase_user');
        
        // Ensure the invoice belongs to the user's tenant
        if ($invoiceUnit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // Prevent updating sent/paid invoices
        if (in_array($invoiceUnit->status, ['sent', 'paid'])) {
            return response()->json(['message' => 'Cannot update sent or paid invoices'], 400);
        }

        $validated = $request->validate([
            'po_number' => 'nullable|string|max:255',
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
            'notes' => 'nullable|array',
            'notes.public_notes' => 'nullable|string',
            'notes.private_notes' => 'nullable|string',
            'notes.terms' => 'nullable|string',
            'notes.footer' => 'nullable|string',
        ]);

        DB::beginTransaction();
        
        try {
            // Update invoice fields
            $invoiceUnit->update(array_filter($validated, function($key) {
                return !in_array($key, ['items', 'notes']);
            }, ARRAY_FILTER_USE_KEY));

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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
     * Get invoices for a specific unit.
     */
    public function forUnit(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
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