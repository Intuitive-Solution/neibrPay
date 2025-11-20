<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use App\Models\InvoiceUnit;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoicePaymentController extends Controller
{
    protected $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }
    /**
     * Display a listing of payments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $invoiceId = $request->get('invoice_id');
        $paymentMethod = $request->get('payment_method');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $query = InvoicePayment::query()
            ->with(['invoiceUnit.unit', 'recorder', 'reviewer'])
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
                
                // If user is a resident, filter payments to only show those for invoices belonging to user's owned units
                if ($user->isResident()) {
                    $ownedUnitIds = $user->ownedUnits()->get()->pluck('id')->toArray();
                    if (empty($ownedUnitIds)) {
                        // Resident has no owned units, return empty result by adding impossible condition
                        $q->whereRaw('1 = 0');
                    } else {
                        $q->whereIn('unit_id', $ownedUnitIds);
                    }
                }
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
            ->get()
            ->map(function ($payment) {
                // Ensure status is set (for backward compatibility with old payments)
                if (!$payment->status) {
                    $payment->status = 'approved';
                }
                return $payment;
            });
        
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
        $user = $request->user();
        
        // First, verify the invoice exists and belongs to the user's tenant
        $invoice = InvoiceUnit::forTenant($user->tenant_id)->findOrFail($invoiceId);
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer,stripe_card,stripe_ach,other',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payment_date' => 'required|date',
        ]);
        
        // Check user role: residents can only pay full amount, admins can pay any amount
        $isResident = $user->isResident();
        
        if ($isResident) {
            // Residents can only submit full payment
            if ($validated['amount'] != $invoice->balance_due) {
                return response()->json([
                    'message' => 'Resident can only submit full payment amount.',
                    'errors' => [
                        'amount' => ['Amount must equal ' . number_format((float)$invoice->balance_due, 2) . ' (full balance due)']
                    ]
                ], 422);
            }
            
            $paymentStatus = 'in_review';
            $invoiceStatus = 'in_review';
        } else {
            // Admin submitting payment - validate doesn't exceed balance
            if ($validated['amount'] > $invoice->balance_due) {
                return response()->json([
                    'message' => 'Payment amount cannot exceed the remaining balance.',
                    'errors' => [
                        'amount' => ['Payment amount cannot exceed the remaining balance of $' . number_format((float)$invoice->balance_due, 2)]
                    ]
                ], 422);
            }
            
            $paymentStatus = 'approved';
            $invoiceStatus = null; // Will be set based on payment calculation
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
                'status' => $paymentStatus,
                'reviewed_by' => $isResident ? null : $user->id,
                'reviewed_at' => $isResident ? null : now(),
            ]);
            
            // Recalculate invoice balance from payments (exclude temporary Stripe payments)
            $totalPaid = $invoice->payments()->confirmed()->sum('amount');
            $balanceDue = $invoice->total - $totalPaid;
            
            // Update invoice status based on payment
            if ($isResident) {
                $invoice->status = $invoiceStatus;
            } else {
                // Admin payment
                if ($balanceDue <= 0) {
                    $invoice->status = 'paid';
                } elseif ($totalPaid > 0) {
                    $invoice->status = 'partial';
                }
            }
            
            $invoice->save();
            
            DB::commit();
            
            // Track payment in PostHog
            $this->analytics->captureEvent('invoice_paid_backend', $user->id, [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $payment->amount,
                'payment_method' => $payment->payment_method,
                'payment_status' => $payment->status,
                'invoice_total' => $invoice->total,
                'balance_due' => $balanceDue,
                'invoice_status' => $invoice->status,
                'days_outstanding' => $invoice->created_at->diffInDays(now()),
                'tenant_id' => $user->tenant_id,
                'is_resident' => $isResident,
            ]);
            
            // Load relationships for response
            $payment->load(['invoiceUnit.unit', 'recorder']);
            
            $message = $isResident 
                ? 'Payment submitted for admin review.'
                : 'Payment recorded successfully.';
            
            return response()->json([
                'data' => $payment,
                'message' => $message,
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
        $user = $request->user();
        
        $payment = InvoicePayment::with(['invoiceUnit.unit', 'recorder', 'reviewer'])
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        // Ensure status is set (for backward compatibility)
        if (!$payment->status) {
            $payment->status = 'approved';
        }
        
        return response()->json([
            'data' => $payment,
        ]);
    }

    /**
     * Update the specified payment in storage.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        $payment = InvoicePayment::with('invoiceUnit')
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        // Only allow updates if payment is pending or in_review
        if (!in_array($payment->status, ['pending', 'in_review'])) {
            return response()->json([
                'message' => 'Cannot update approved or rejected payments. Use the resubmit endpoint for rejected payments.',
            ], 422);
        }
        
        $validated = $request->validate([
            'amount' => 'sometimes|numeric|min:0.01',
            'payment_method' => 'sometimes|in:cash,check,credit_card,bank_transfer,stripe_card,stripe_ach,other',
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
            
            // Recalculate invoice balance from payments (exclude temporary Stripe payments)
            $totalPaid = $invoice->payments()->confirmed()->sum('amount');
            $balanceDue = $invoice->total - $totalPaid;
            
            // Update invoice status based on new payment totals
            if ($balanceDue <= 0) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
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
        $user = $request->user();
        
        $payment = InvoicePayment::with('invoiceUnit')
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        // Prevent deletion of approved payments
        if ($payment->status === 'approved') {
            return response()->json([
                'message' => 'Cannot delete approved payments.',
            ], 422);
        }
        
        // Non-admins can only delete their own pending/rejected payments
        if ($user->isResident() && $payment->recorded_by !== $user->id) {
            return response()->json([
                'message' => 'You can only delete your own payments.',
            ], 403);
        }
        
        DB::beginTransaction();
        
        try {
            $invoice = $payment->invoiceUnit;
            
            // Recalculate invoice balance from remaining payments (exclude temporary Stripe payments)
            $totalPaid = $invoice->payments()->confirmed()->sum('amount');
            $balanceDue = $invoice->total - $totalPaid;
            
            // Update invoice status based on remaining payments
            if ($totalPaid <= 0) {
                $invoice->status = 'sent';
            } elseif ($balanceDue <= 0) {
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

    /**
     * Approve a payment (admin only).
     */
    public function approve(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can approve payments
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can approve payments.',
            ], 403);
        }
        
        $payment = InvoicePayment::with(['invoiceUnit', 'invoiceUnit.unit'])
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        // Payment must be in review to be approved
        if (!$payment->canBeReviewed()) {
            return response()->json([
                'message' => 'Payment cannot be approved. It must be in review status.',
            ], 422);
        }
        
        $validated = $request->validate([
            'admin_comment_public' => 'nullable|string',
            'admin_comment_private' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update payment
            $payment->status = 'approved';
            $payment->admin_comment_public = $validated['admin_comment_public'] ?? null;
            $payment->admin_comment_private = $validated['admin_comment_private'] ?? null;
            $payment->reviewed_by = $user->id;
            $payment->reviewed_at = now();
            $payment->save();
            
            // Recalculate invoice balance from payments
            $invoice = $payment->invoiceUnit;
            $totalPaid = $invoice->payments()->confirmed()->sum('amount');
            $balanceDue = $invoice->total - $totalPaid;
            
            // Update invoice status based on payment
            if ($balanceDue <= 0) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->status = 'partial';
            }
            
            $invoice->save();
            
            DB::commit();
            
            // Send approval notification email
            $this->sendPaymentApprovalEmail($payment, $invoice);
            
            // Load relationships for response
            $payment->load(['invoiceUnit.unit', 'reviewer']);
            
            return response()->json([
                'data' => $payment,
                'message' => 'Payment approved successfully.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to approve payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a payment (admin only).
     */
    public function reject(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can reject payments
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can reject payments.',
            ], 403);
        }
        
        $payment = InvoicePayment::with(['invoiceUnit', 'invoiceUnit.unit'])
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        // Payment must be in review to be rejected
        if (!$payment->canBeReviewed()) {
            return response()->json([
                'message' => 'Payment cannot be rejected. It must be in review status.',
            ], 422);
        }
        
        $validated = $request->validate([
            'admin_comment_public' => 'required|string',
            'admin_comment_private' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            // Update payment
            $payment->status = 'rejected';
            $payment->admin_comment_public = $validated['admin_comment_public'];
            $payment->admin_comment_private = $validated['admin_comment_private'] ?? null;
            $payment->reviewed_by = $user->id;
            $payment->reviewed_at = now();
            $payment->save();
            
            // Update invoice status to payment_rejected
            $invoice = $payment->invoiceUnit;
            $invoice->status = 'payment_rejected';
            $invoice->save();
            
            DB::commit();
            
            // Send rejection notification email
            $this->sendPaymentRejectionEmail($payment, $invoice);
            
            // Load relationships for response
            $payment->load(['invoiceUnit.unit', 'reviewer']);
            
            return response()->json([
                'data' => $payment,
                'message' => 'Payment rejected successfully.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to reject payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resubmit a rejected payment (resident only).
     */
    public function resubmit(Request $request, int $id): JsonResponse
    {
        $user = $request->user();
        
        // Residents can only resubmit their own payments
        $payment = InvoicePayment::with('invoiceUnit')
            ->whereHas('invoiceUnit', function ($q) use ($user) {
                $q->where('tenant_id', $user->tenant_id);
            })
            ->findOrFail($id);
        
        // Payment must be rejected to be resubmitted
        if (!$payment->canBeResubmitted()) {
            return response()->json([
                'message' => 'Payment cannot be resubmitted. It must be in rejected status.',
            ], 422);
        }
        
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,check,credit_card,bank_transfer,stripe_card,stripe_ach,other',
            'payment_reference' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'payment_date' => 'required|date',
        ]);
        
        // Validate that amount equals balance due (full payment only for residents)
        $invoice = $payment->invoiceUnit;
        if ($validated['amount'] != $invoice->balance_due) {
            return response()->json([
                'message' => 'Resident can only submit full payment amount.',
                'errors' => [
                    'amount' => ['Amount must equal ' . $invoice->balance_due . ' (full balance due)']
                ]
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            // Update the payment
            $payment->update([
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_reference'],
                'notes' => $validated['notes'],
                'payment_date' => $validated['payment_date'],
                'status' => 'in_review',
                'admin_comment_public' => null,
                'admin_comment_private' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]);
            
            // Update invoice status back to in_review
            $invoice->status = 'in_review';
            $invoice->save();
            
            DB::commit();
            
            // Load relationships for response
            $payment->load(['invoiceUnit.unit', 'recorder']);
            
            return response()->json([
                'data' => $payment,
                'message' => 'Payment resubmitted successfully for review.',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'message' => 'Failed to resubmit payment.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send approval notification email to resident.
     */
    private function sendPaymentApprovalEmail(InvoicePayment $payment, InvoiceUnit $invoice): void
    {
        try {
            $resident = $payment->recorder;
            if (!$resident || !$resident->email) {
                return;
            }
            
            $subject = "Payment Approved - Invoice {$invoice->invoice_number}";
            
            $emailBody = "
Dear {$resident->name},

Your payment of \${$payment->amount} for Invoice #{$invoice->invoice_number} has been approved.

Payment Details:
- Amount: \${$payment->amount}
- Payment Method: " . $this->formatPaymentMethod($payment->payment_method) . "
- Payment Date: {$payment->payment_date}
- Invoice Number: {$invoice->invoice_number}

";
            
            if ($payment->admin_comment_public) {
                $emailBody .= "Admin Comment: {$payment->admin_comment_public}\n\n";
            }
            
            $emailBody .= "
If you have any questions, please contact the HOA administrator.

Best regards,
NeibrPay Team
";
            
            \Mail::raw($emailBody, function ($message) use ($subject, $resident) {
                $message->to($resident->email)
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            // Log email error but don't fail the payment approval
            \Log::error('Failed to send payment approval email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send rejection notification email to resident.
     */
    private function sendPaymentRejectionEmail(InvoicePayment $payment, InvoiceUnit $invoice): void
    {
        try {
            $resident = $payment->recorder;
            if (!$resident || !$resident->email) {
                return;
            }
            
            $subject = "Payment Rejected - Invoice {$invoice->invoice_number}";
            
            $emailBody = "
Dear {$resident->name},

Your payment of \${$payment->amount} for Invoice #{$invoice->invoice_number} has been rejected and requires revision.

Payment Details:
- Amount: \${$payment->amount}
- Payment Method: " . $this->formatPaymentMethod($payment->payment_method) . "
- Payment Date: {$payment->payment_date}
- Invoice Number: {$invoice->invoice_number}

Reason for Rejection:
{$payment->admin_comment_public}

Please review the rejection reason above and resubmit your payment with the necessary corrections.

If you have any questions, please contact the HOA administrator.

Best regards,
NeibrPay Team
";
            
            \Mail::raw($emailBody, function ($message) use ($subject, $resident) {
                $message->to($resident->email)
                    ->subject($subject);
            });
        } catch (\Exception $e) {
            // Log email error but don't fail the payment rejection
            \Log::error('Failed to send payment rejection email', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Format payment method for display.
     */
    private function formatPaymentMethod(string $method): string
    {
        $methods = [
            'cash' => 'Cash',
            'check' => 'Check',
            'credit_card' => 'Credit Card',
            'bank_transfer' => 'Bank Transfer',
            'stripe_card' => 'Stripe (Card)',
            'stripe_ach' => 'Stripe (ACH)',
            'other' => 'Other',
        ];
        
        return $methods[$method] ?? $method;
    }
}
