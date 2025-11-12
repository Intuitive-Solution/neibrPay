<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use App\Models\InvoiceUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    private StripeClient $stripe;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Checkout session for an invoice.
     */
    public function createCheckoutSession(Request $request, int $invoiceId): JsonResponse
    {
        $user = $request->user();

        // Verify invoice exists and belongs to user's tenant
        $invoice = InvoiceUnit::forTenant($user->tenant_id)->findOrFail($invoiceId);

        // If user is a resident, verify they own the unit associated with the invoice
        if ($user->isResident()) {
            $ownedUnitIds = $user->ownedUnits()->get()->pluck('id')->toArray();
            if (!in_array($invoice->unit_id, $ownedUnitIds)) {
                return response()->json([
                    'message' => 'You do not have permission to pay this invoice.',
                ], 403);
            }
        }

        // Validate request
        $validated = $request->validate([
            'amount' => 'nullable|numeric|min:0.01',
        ]);

        // Determine payment amount (default to balance due)
        $amount = $validated['amount'] ?? $invoice->balance_due;

        // Validate amount doesn't exceed balance
        if ($amount > $invoice->balance_due) {
            return response()->json([
                'message' => 'Payment amount cannot exceed the remaining balance.',
                'errors' => [
                    'amount' => ['Payment amount cannot exceed the remaining balance of $' . number_format((float)$invoice->balance_due, 2)],
                ],
            ], 422);
        }

        // Validate minimum amount
        if ($amount < 0.50) {
            return response()->json([
                'message' => 'Payment amount must be at least $0.50.',
                'errors' => [
                    'amount' => ['Minimum payment amount is $0.50'],
                ],
            ], 422);
        }

        try {
            // Get frontend URL from env (set in .env file)
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            // Create Stripe Checkout Session
            $checkoutSession = $this->stripe->checkout->sessions->create([
                'payment_method_types' => ['card', 'us_bank_account', 'link'], // Card, ACH, and Link
                'payment_method_options' => [
                    'us_bank_account' => [
                        'financial_connections' => [
                            'permissions' => ['payment_method', 'balances'],
                        ],
                    ],
                ],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'HOA Dues Payment',
                            'description' => "Invoice #{$invoice->invoice_number} - {$invoice->unit->title}",
                        ],
                        'unit_amount' => (int)($amount * 100), // Convert to cents
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => "{$frontendUrl}/invoices/{$invoiceId}?payment=success&session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => "{$frontendUrl}/invoices/{$invoiceId}?payment=cancelled",
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant_id' => $invoice->tenant_id,
                    'unit_id' => $invoice->unit_id,
                    'user_id' => $user->id,
                ],
                'customer_email' => $user->email,
            ]);

            // Create a pending payment record
            $payment = InvoicePayment::create([
                'invoice_unit_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => 'stripe_card', // Will be updated when webhook is received
                'payment_reference' => $checkoutSession->id,
                'stripe_checkout_session_id' => $checkoutSession->id,
                'payment_date' => now(),
                'recorded_by' => $user->id,
                'notes' => 'Stripe Checkout payment - pending confirmation',
            ]);

            return response()->json([
                'data' => [
                    'checkout_url' => $checkoutSession->url,
                    'session_id' => $checkoutSession->id,
                    'payment_id' => $payment->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Stripe Checkout Session creation failed', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Failed to create payment session.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing your request.',
            ], 500);
        }
    }

    /**
     * Handle Stripe webhook events.
     * This endpoint should NOT have auth middleware.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        if (!$webhookSecret) {
            Log::error('Stripe webhook secret not configured');
            return response()->json(['error' => 'Webhook secret not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Handle the event
        try {
            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->handleCheckoutSessionCompleted($event->data->object);
                    break;

                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event->data->object);
                    break;

                default:
                    Log::info('Unhandled Stripe webhook event', [
                        'type' => $event->type,
                    ]);
            }

            return response()->json(['received' => true]);
        } catch (\Exception $e) {
            Log::error('Error processing Stripe webhook', [
                'event_type' => $event->type,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Handle checkout.session.completed event.
     */
    private function handleCheckoutSessionCompleted(Session $session): void
    {
        $invoiceId = $session->metadata->invoice_id ?? null;
        if (!$invoiceId) {
            Log::warning('Checkout session completed without invoice_id metadata', [
                'session_id' => $session->id,
            ]);
            return;
        }

        // Find the payment record
        $payment = InvoicePayment::where('stripe_checkout_session_id', $session->id)->first();

        if (!$payment) {
            Log::warning('Payment record not found for checkout session', [
                'session_id' => $session->id,
                'invoice_id' => $invoiceId,
            ]);
            return;
        }

        // Verify invoice belongs to correct tenant
        $invoice = InvoiceUnit::find($invoiceId);
        if (!$invoice || $invoice->tenant_id != $session->metadata->tenant_id) {
            Log::warning('Invoice tenant mismatch in webhook', [
                'session_id' => $session->id,
                'invoice_id' => $invoiceId,
            ]);
            return;
        }

        // Update payment with payment intent ID if available
        if ($session->payment_intent) {
            $payment->stripe_payment_intent_id = $session->payment_intent;
        }

        // Payment is confirmed, but wait for payment_intent.succeeded for final confirmation
        // This is especially important for ACH which can take time
        Log::info('Checkout session completed', [
            'session_id' => $session->id,
            'payment_id' => $payment->id,
            'invoice_id' => $invoiceId,
        ]);
    }

    /**
     * Handle payment_intent.succeeded event.
     */
    private function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent): void
    {
        // Find payment by payment intent ID
        $payment = InvoicePayment::where('stripe_payment_intent_id', $paymentIntent->id)
            ->orWhere('stripe_checkout_session_id', $paymentIntent->metadata->checkout_session_id ?? '')
            ->first();

        if (!$payment) {
            Log::warning('Payment record not found for payment intent', [
                'payment_intent_id' => $paymentIntent->id,
            ]);
            return;
        }

        $invoice = $payment->invoiceUnit;

        DB::beginTransaction();
        try {
            // Determine payment method type
            $paymentMethodType = 'stripe_card';
            $stripePaymentMethod = 'card';

            if ($paymentIntent->payment_method_types) {
                foreach ($paymentIntent->payment_method_types as $type) {
                    if ($type === 'us_bank_account') {
                        $paymentMethodType = 'stripe_ach';
                        $stripePaymentMethod = 'ach_debit';
                        break;
                    }
                }
            }

            // Update payment record
            $payment->payment_method = $paymentMethodType;
            $payment->stripe_payment_intent_id = $paymentIntent->id;
            $payment->stripe_payment_method = $stripePaymentMethod;
            $payment->payment_reference = $paymentIntent->id;
            $payment->notes = 'Stripe payment confirmed - ' . ($stripePaymentMethod === 'ach_debit' ? 'ACH' : 'Card');
            $payment->save();

            // Recalculate invoice balance
            $totalPaid = $invoice->payments()->sum('amount');
            $balanceDue = $invoice->total - $totalPaid;

            // Update invoice status
            if ($balanceDue <= 0) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();

            DB::commit();

            Log::info('Payment processed successfully', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $payment->amount,
                'method' => $paymentMethodType,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing payment intent succeeded', [
                'payment_intent_id' => $paymentIntent->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment_intent.payment_failed event.
     */
    private function handlePaymentIntentFailed(PaymentIntent $paymentIntent): void
    {
        $payment = InvoicePayment::where('stripe_payment_intent_id', $paymentIntent->id)
            ->orWhere('stripe_checkout_session_id', $paymentIntent->metadata->checkout_session_id ?? '')
            ->first();

        if (!$payment) {
            Log::warning('Payment record not found for failed payment intent', [
                'payment_intent_id' => $paymentIntent->id,
            ]);
            return;
        }

        // Update payment notes with failure reason
        $failureReason = $paymentIntent->last_payment_error->message ?? 'Payment failed';
        $payment->notes = "Stripe payment failed: {$failureReason}";
        $payment->save();

        Log::warning('Payment failed', [
            'payment_id' => $payment->id,
            'payment_intent_id' => $paymentIntent->id,
            'failure_reason' => $failureReason,
        ]);
    }

    /**
     * Get payment status for an invoice.
     */
    public function getPaymentStatus(Request $request, int $invoiceId): JsonResponse
    {
        $user = $request->user();

        // Verify invoice exists and belongs to user's tenant
        $invoice = InvoiceUnit::forTenant($user->tenant_id)->findOrFail($invoiceId);

        // If user is a resident, verify they own the unit
        if ($user->isResident()) {
            $ownedUnitIds = $user->ownedUnits()->get()->pluck('id')->toArray();
            if (!in_array($invoice->unit_id, $ownedUnitIds)) {
                return response()->json([
                    'message' => 'You do not have permission to view this invoice.',
                ], 403);
            }
        }

        // Get pending Stripe payments for this invoice
        $pendingPayments = InvoicePayment::where('invoice_unit_id', $invoiceId)
            ->whereNotNull('stripe_checkout_session_id')
            ->whereNull('stripe_payment_intent_id')
            ->get();

        return response()->json([
            'data' => [
                'has_pending_payments' => $pendingPayments->count() > 0,
                'pending_payments' => $pendingPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'session_id' => $payment->stripe_checkout_session_id,
                        'created_at' => $payment->created_at,
                    ];
                }),
            ],
        ]);
    }
}

