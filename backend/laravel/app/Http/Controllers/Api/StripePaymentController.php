<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use App\Models\InvoiceUnit;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripePaymentController extends Controller
{
    private StripeClient $stripe;
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->analytics = $analytics;
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

            // Check if tenant has Stripe Connect set up
            $tenant = $invoice->tenant;
            $stripeConnectId = $tenant->getSetting('stripe_connect_id');
            $chargesEnabled = $tenant->getSetting('charges_enabled', false);

            if (!$stripeConnectId || !$chargesEnabled) {
                return response()->json([
                    'message' => 'Stripe payment processing is not available. Please contact the HOA administrator.',
                    'error' => 'stripe_not_configured',
                ], 400);
            }

            // Calculate platform fee (1% of payment amount)
            $amountInCents = (int)($amount * 100);
            $platformFeeInCents = (int)round($amountInCents * 0.01);

            // Build checkout session params
            $checkoutParams = [
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
                        'unit_amount' => $amountInCents,
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
            ];

            // Add payment intent data with application fee for platform
            if ($platformFeeInCents > 0) {
                $checkoutParams['payment_intent_data'] = [
                    'application_fee_amount' => $platformFeeInCents,
                ];
            }

            // Create Stripe Checkout Session
            // stripe_account must be passed as second parameter, not in the params array
            $checkoutSession = $this->stripe->checkout->sessions->create(
                $checkoutParams,
                ['stripe_account' => $stripeConnectId]
            );

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

            // Track payment initiation
            $this->analytics->captureEvent('payment_initiated', $user->id, [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => 'stripe',
                'currency' => 'USD',
                'tenant_id' => $user->tenant_id,
            ]);

            Log::info('Payment created', ['payment' => $payment]);
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
                    // Only update payment intent ID, don't process payment yet
                    $this->handlePaymentIntentSucceeded($event->data->object);
                    break;

                case 'charge.succeeded':
                    // Record payment and update invoice status when charge succeeds
                    $this->handleChargeSucceeded($event->data->object);
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
            $payment->save();
            
            // Update payment intent metadata with checkout session ID for reliable lookup
            try {
                $this->stripe->paymentIntents->update($session->payment_intent, [
                    'metadata' => [
                        'checkout_session_id' => $session->id,
                        'invoice_id' => $invoiceId,
                        'tenant_id' => $session->metadata->tenant_id ?? null,
                    ],
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to update payment intent metadata', [
                    'payment_intent_id' => $session->payment_intent,
                    'error' => $e->getMessage(),
                ]);
            }
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
     * This only updates the payment intent ID, but doesn't process the payment yet.
     * Payment is only processed when charge.succeeded is received.
     */
    private function handlePaymentIntentSucceeded(PaymentIntent $paymentIntent): void
    {
        Log::info('Payment intent succeeded  stripe_checkout_session_id', ['payment_intent' => $paymentIntent->metadata->checkout_session_id]);
        // Find payment by checkout session ID (payment intent might not be set yet)
        $payment = InvoicePayment::where('stripe_checkout_session_id', $paymentIntent->metadata->checkout_session_id ?? '')
            ->orWhere('stripe_payment_intent_id', $paymentIntent->id)
            ->first();

        Log::info('Payment found in Payment intent succeeded', ['payment' => $payment]);

        if (!$payment) {
            Log::warning('Payment record not found for payment intent', [
                'payment_intent_id' => $paymentIntent->id,
            ]);
            return;
        }

        // Only update payment intent ID, don't process payment yet
        // Payment will be processed when charge.succeeded is received
        $payment->stripe_payment_intent_id = $paymentIntent->id;
        $payment->save();

        // Ensure payment intent metadata includes checkout session ID for reliable lookup
        if ($payment->stripe_checkout_session_id && !isset($paymentIntent->metadata->checkout_session_id)) {
            try {
                $this->stripe->paymentIntents->update($paymentIntent->id, [
                    'metadata' => array_merge($paymentIntent->metadata->toArray(), [
                        'checkout_session_id' => $payment->stripe_checkout_session_id,
                    ]),
                ]);
            } catch (\Exception $e) {
                Log::warning('Failed to update payment intent metadata in payment_intent.succeeded', [
                    'payment_intent_id' => $paymentIntent->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Payment intent succeeded - waiting for charge.succeeded', [
            'payment_id' => $payment->id,
            'payment_intent_id' => $paymentIntent->id,
        ]);
    }

    /**
     * Handle charge.succeeded event.
     * This is when the payment is actually confirmed and funds are available.
     */
    private function handleChargeSucceeded(Charge $charge): void
    {
        Log::info('Charge succeeded', ['charge' => $charge]);
        
        // Try to find payment by payment intent ID first
        $payment = InvoicePayment::where('stripe_payment_intent_id', $charge->payment_intent)
            ->first();

        // If not found, try to find by checkout session ID via payment intent metadata
        if (!$payment && $charge->payment_intent) {
            try {
                // Retrieve payment intent to get checkout session ID from metadata
                $paymentIntent = $this->stripe->paymentIntents->retrieve($charge->payment_intent);
                $checkoutSessionId = $paymentIntent->metadata->checkout_session_id ?? null;
                
                if ($checkoutSessionId) {
                    $payment = InvoicePayment::where('stripe_checkout_session_id', $checkoutSessionId)
                        ->first();
                    
                    Log::info('Payment found by checkout session ID from metadata', [
                        'payment' => $payment,
                        'checkout_session_id' => $checkoutSessionId,
                    ]);
                }
                
                // If still not found, try to retrieve checkout session from Stripe
                if (!$payment) {
                    try {
                        // Search for checkout sessions with this payment intent
                        $checkoutSessions = $this->stripe->checkout->sessions->all([
                            'payment_intent' => $charge->payment_intent,
                            'limit' => 1,
                        ]);
                        
                        if (!empty($checkoutSessions->data)) {
                            $checkoutSessionId = $checkoutSessions->data[0]->id;
                            $payment = InvoicePayment::where('stripe_checkout_session_id', $checkoutSessionId)
                                ->first();
                            
                            Log::info('Payment found by checkout session ID from Stripe API', [
                                'payment' => $payment,
                                'checkout_session_id' => $checkoutSessionId,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to retrieve checkout session from Stripe', [
                            'payment_intent_id' => $charge->payment_intent,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to retrieve payment intent for charge', [
                    'charge_id' => $charge->id,
                    'payment_intent_id' => $charge->payment_intent,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Payment found', ['payment' => $payment]);

        if (!$payment) {
            Log::warning('Payment record not found for charge', [
                'charge_id' => $charge->id,
                'payment_intent_id' => $charge->payment_intent,
                'charge_amount' => $charge->amount / 100,
            ]);
            return;
        }

        // Check if payment is already processed (has this charge ID as reference)
        // Payment reference is updated to charge ID when payment is confirmed
        if ($payment->payment_reference === $charge->id) {
            Log::info('Payment already processed for this charge', [
                'payment_id' => $payment->id,
                'charge_id' => $charge->id,
            ]);
            return;
        }

        $invoice = $payment->invoiceUnit;

        DB::beginTransaction();
        try {
            // Determine payment method type from charge
            $paymentMethodType = 'stripe_card';
            $stripePaymentMethod = 'card';

            if ($charge->payment_method_details) {
                $paymentMethodDetails = $charge->payment_method_details;
                
                if (isset($paymentMethodDetails->type)) {
                    if ($paymentMethodDetails->type === 'us_bank_account') {
                        $paymentMethodType = 'stripe_ach';
                        $stripePaymentMethod = 'ach_debit';
                    } elseif ($paymentMethodDetails->type === 'card') {
                        $paymentMethodType = 'stripe_card';
                        $stripePaymentMethod = 'card';
                    }
                }
            }

            // Update payment record with charge information
            $payment->payment_method = $paymentMethodType;
            $payment->stripe_payment_intent_id = $charge->payment_intent;
            $payment->stripe_payment_method = $stripePaymentMethod;
            $payment->payment_reference = $charge->id; // Use charge ID as reference
            $payment->notes = 'Stripe payment confirmed - ' . ($stripePaymentMethod === 'ach_debit' ? 'ACH' : 'Card') . ' (Charge: ' . $charge->id . ')';
            $payment->save();

            // Reload invoice to get updated payments relationship
            $invoice->refresh();
            
            // Calculate total paid and balance due (exclude temporary Stripe payments)
            $totalPaid = $invoice->payments()->confirmed()->sum('amount');
            $balanceDue = $invoice->balance_due; // Use accessor which calculates: total - sum(payments)

            // Update invoice status based on balance due
            // If balance due is 0 or less (amount paid equals or exceeds total), mark as paid
            if ($balanceDue <= 0) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
                // If there are payments but balance remains, mark as partial
                $invoice->status = 'partial';
            }

            $invoice->save();

            DB::commit();

            // Track successful payment
            $userId = $payment->recorder?->id ?? $invoice->creator?->id;
            if ($userId) {
                $this->analytics->captureEvent('payment_succeeded', $userId, [
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'amount' => $payment->amount,
                    'method' => $paymentMethodType,
                    'charge_id' => $charge->id,
                    'invoice_total' => $invoice->total,
                    'balance_due' => $balanceDue,
                    'invoice_status' => $invoice->status,
                    'days_outstanding' => $invoice->created_at->diffInDays(now()),
                    'tenant_id' => $invoice->tenant_id,
                ]);
            }

            Log::info('Payment processed successfully via charge.succeeded', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'charge_id' => $charge->id,
                'amount' => $payment->amount,
                'method' => $paymentMethodType,
                'total_paid' => $totalPaid,
                'invoice_total' => $invoice->total,
                'balance_due' => $balanceDue,
                'invoice_status' => $invoice->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing charge succeeded', [
                'charge_id' => $charge->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
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

        // Track payment failure
        $invoice = $payment->invoiceUnit;
        $userId = $payment->recorder?->id ?? $invoice->creator?->id;
        if ($userId) {
            $this->analytics->captureEvent('payment_failed', $userId, [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $payment->amount,
                'reason' => $failureReason,
                'error_code' => $paymentIntent->last_payment_error->code ?? 'unknown',
                'payment_intent_id' => $paymentIntent->id,
                'tenant_id' => $invoice->tenant_id,
            ]);
        }

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

