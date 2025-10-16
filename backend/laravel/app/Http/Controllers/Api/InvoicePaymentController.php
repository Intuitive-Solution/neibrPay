<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use App\Models\InvoiceUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoicePaymentController extends Controller
{
    /**
     * Display a listing of payments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->get('firebase_user');
        $invoiceId = $request->get('invoice_id');
        $paymentMethod = $request->get('payment_method');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = InvoicePayment::query()
            ->with(['invoiceUnit.unit', 'recorder'])
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            });
            
        if ($invoiceId) {
            $query->where('invoice_unit_id', $invoiceId);
        }
        
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }
        
        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('payment_date', '<=', $endDate);
        }
        
        $payments = $query->orderBy('payment_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'data' => $payments,
            'meta' => [
                'total' => $payments->count(),
                'filters' => [
                    'invoice_id' => $invoiceId,
                    'payment_method' => $paymentMethod,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created payment in storage.
     */
    public function store(Request $request, int $invoiceId): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // First, verify the invoice exists and belongs to the user's tenant
        $invoice = InvoiceUnit::forTenant($user->tenant_id)->findOrFail($invoiceId);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer,other',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payment_date' => 'required|date',
        ]);
        
        // Validate that payment amount doesn't exceed remaining balance
        if ($validated['amount'] > $invoice->balance_due) {
            return response()->json([
                'message' => 'Payment amount cannot exceed the remaining balance.',
                'errors' => [
                    'amount' => ['Payment amount cannot exceed the remaining balance of $' . number_format((float)$invoice->balance_due, 2)]
                ]
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Create the payment record
            $payment = InvoicePayment::create([
                'invoice_unit_id' => $invoiceId,
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'notes' => $validated['notes'],
                'payment_date' => $validated['payment_date'],
                'recorded_by' => $user->id,
            ]);
            
            // Update invoice payment totals
            $invoice->paid_to_date += $validated['amount'];
            $invoice->balance_due = $invoice->total - $invoice->paid_to_date;
            
            // Update invoice status based on payment
            if ($invoice->balance_due <= 0) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_to_date > 0) {
                $invoice->status = 'partial';
            }
            
            $invoice->save();
            
            DB::commit();
            
            // Load relationships for response
            $payment->load(['invoiceUnit.unit', 'recorder']);
            
            return response()->json([
                'data' => $payment,
                'message' => 'Payment recorded successfully.',
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to record payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        $payment = InvoicePayment::with(['invoiceUnit.unit', 'recorder'])
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        return response()->json([
            'data' => $payment,
        ]);
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        $payment = InvoicePayment::with('invoiceUnit')
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|in:cash,check,credit_card,bank_transfer,other',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payment_date' => 'sometimes|date',
        ]);
        
        DB::beginTransaction();
        
        try {
            $invoice = $payment->invoiceUnit;
            $oldAmount = $payment->amount;
            $newAmount = $validated['amount'] ?? $oldAmount;
            
            // Update the payment
            $payment->update($validated);
            
            // Recalculate invoice totals
            $invoice->paid_to_date = $invoice->paid_to_date - $oldAmount + $newAmount;
            $invoice->balance_due = $invoice->total - $invoice->paid_to_date;
            
            // Update invoice status based on new payment totals
            if ($invoice->balance_due <= 0) {
                $invoice->status = 'paid';
            } elseif ($invoice->paid_to_date > 0) {
                $invoice->status = 'partial';
            } else {
                // If no payments, revert to sent status
                $invoice->status = 'sent';
            }
            
            $invoice->save();
            
            DB::commit();
            
            // Load relationships for response
            $payment->load(['invoiceUnit.unit', 'recorder']);
            
            return response()->json([
                'data' => $payment,
                'message' => 'Payment updated successfully.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to update payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payment from storage.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        $payment = InvoicePayment::with('invoiceUnit')
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            $invoice = $payment->invoiceUnit;
            
            // Remove payment amount from invoice totals
            $invoice->paid_to_date -= $payment->amount;
            $invoice->balance_due = $invoice->total - $invoice->paid_to_date;
            
            // Update invoice status based on remaining payments
            if ($invoice->paid_to_date <= 0) {
                $invoice->status = 'sent';
            } elseif ($invoice->balance_due <= 0) {
                $invoice->status = 'paid';
            } else {
                $invoice->status = 'partial';
            }
            
            $invoice->save();
            
            // Delete the payment
            $payment->delete();
            
            DB::commit();
            
            return response()->json([
                'message' => 'Payment deleted successfully.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to delete payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
