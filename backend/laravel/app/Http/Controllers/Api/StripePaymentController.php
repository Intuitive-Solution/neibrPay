<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use App\Models\InvoiceUnit;
use App\Services\AnalyticsService;
use App\Services\InvoicePdfService;
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
    protected InvoicePdfService $pdfService;

    public function __construct(AnalyticsService $analytics, InvoicePdfService $pdfService)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient(config('services.stripe.secret'));
        $this->analytics = $analytics;
        $this->pdfService = $pdfService;
    }

    /**
     * Calculate fees for an invoice payment (Card vs ACH).
     */
    public function calculateFees(Request $request, int $invoiceId): JsonResponse
    {
        $user = $request->user();

        // Verify invoice exists and belongs to user's tenant
        $invoice = InvoiceUnit::forTenant($user->tenant_id)->findOrFail($invoiceId);

        // If user is a resident, verify they own the unit associated with the invoice
        if ($user->isResident()) {
            $ownedUnitIds = $user->ownedUnits()->get()->pluck('id')->toArray();
            if (!in_array($invoice->unit_id, $ownedUnitIds)) {
                return response()->json([
                    'message' => 'You do not have permission to calculate fees for this invoice.',
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
            ], 422);
        }

        // Validate minimum amount
        if ($amount < 0.50) {
            return response()->json([
                'message' => 'Payment amount must be at least $0.50.',
            ], 422);
        }

        // Calculate fees
        $platformFeePercent = 0.01; // 1%
        $cardStripeFeePercent = 0.029; // 2.9%
        $cardStripeFeeFixed = 0.30; // $0.30
        $achStripeFeePercent = 0.008; // 0.8%
        $achStripeFeeMax = 5.00; // $5.00 cap

        // Platform fee (same for both methods)
        $platformFee = round($amount * $platformFeePercent, 2);

        // Card: 1% (platform) + 2.9% + $0.30 (Stripe)
        $cardStripeFee = round(($amount * $cardStripeFeePercent) + $cardStripeFeeFixed, 2);
        $cardProcessingFee = round($platformFee + $cardStripeFee, 2);
        $cardTotal = round($amount + $cardProcessingFee, 2);

        // ACH: 1% (platform) + 0.8% capped at $5 (Stripe)
        $achStripeFee = min(round($amount * $achStripeFeePercent, 2), $achStripeFeeMax);
        $achProcessingFee = round($platformFee + $achStripeFee, 2);
        $achTotal = round($amount + $achProcessingFee, 2);

        return response()->json([
            'data' => [
                'invoice_amount' => $amount,
                'card' => [
                    'processing_fee' => $cardProcessingFee,
                    'total' => $cardTotal,
                    'breakdown' => [
                        'platform_fee' => $platformFee,
                        'stripe_fee' => $cardStripeFee,
                    ],
                ],
                'ach' => [
                    'processing_fee' => $achProcessingFee,
                    'total' => $achTotal,
                    'breakdown' => [
                        'platform_fee' => $platformFee,
                        'stripe_fee' => $achStripeFee,
                    ],
                ],
            ],
        ]);
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
            'payment_method' => 'nullable|in:card,ach',
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

            // Get payment method (default to card)
            $paymentMethod = $validated['payment_method'] ?? 'card';

            // Calculate fees based on payment method
            $platformFeePercent = 0.01; // 1%
            $platformFee = round($amount * $platformFeePercent, 2);

            if ($paymentMethod === 'ach') {
                // ACH: 1% (platform) + 0.8% capped at $5 (Stripe)
                $achStripeFeePercent = 0.008;
                $achStripeFeeMax = 5.00;
                $stripeFee = min(round($amount * $achStripeFeePercent, 2), $achStripeFeeMax);
            } else {
                // Card (default): 1% (platform) + 2.9% + $0.30 (Stripe)
                $cardStripeFeePercent = 0.029;
                $cardStripeFeeFixed = 0.30;
                $stripeFee = round(($amount * $cardStripeFeePercent) + $cardStripeFeeFixed, 2);
            }

            $totalFee = round($platformFee + $stripeFee, 2);
            $totalChargeAmount = round($amount + $totalFee, 2);

            // Convert to cents for Stripe
            $amountInCents = (int)($amount * 100);
            $totalChargeInCents = (int)($totalChargeAmount * 100);
            $platformFeeInCents = (int)round($platformFee * 100);

            // Build checkout session params with fee structure
            $lineItems = [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'HOA Dues Payment',
                            'description' => "Invoice #{$invoice->invoice_number} - {$invoice->unit->title}",
                        ],
                        'unit_amount' => $amountInCents,
                    ],
                    'quantity' => 1,
                ],
            ];

            // Add processing fee line item if there are fees
            if ($totalFee > 0) {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Processing Fee',
                            'description' => $paymentMethod === 'ach' 
                                ? 'Platform fee (1%) + ACH processing fee (0.8% capped at $5)'
                                : 'Platform fee (1%) + Card processing fee (2.9% + $0.30)',
                        ],
                        'unit_amount' => (int)round($totalFee * 100),
                    ],
                    'quantity' => 1,
                ];
            }

            $checkoutParams = [
                'payment_method_types' => $paymentMethod === 'ach' 
                    ? ['us_bank_account'] 
                    : ['card'],
                'payment_method_options' => $paymentMethod === 'ach' 
                    ? [
                        'us_bank_account' => [
                            'financial_connections' => [
                                'permissions' => ['payment_method', 'balances'],
                            ],
                        ],
                    ]
                    : [],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => "{$frontendUrl}/invoices/{$invoiceId}?payment=success&session_id={CHECKOUT_SESSION_ID}",
                'cancel_url' => "{$frontendUrl}/invoices/{$invoiceId}?payment=cancelled",
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant_id' => $invoice->tenant_id,
                    'unit_id' => $invoice->unit_id,
                    'user_id' => $user->id,
                    'payment_method' => $paymentMethod,
                    'invoice_amount' => (string)$amount,
                    'processing_fee' => (string)$totalFee,
                ],
                'customer_email' => $user->email,
            ];

            // Add payment intent data with application fee for platform
            // Application fee is only the platform's 1% cut
            // The Stripe fee is paid from the platform's cut
            $paymentIntentData = [
                'metadata' => [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant_id' => $invoice->tenant_id,
                    'unit_id' => $invoice->unit_id,
                    'user_id' => $user->id,
                    'payment_method' => $paymentMethod,
                    'invoice_amount' => (string)$amount,
                    'processing_fee' => (string)$totalFee,
                ],
            ];
            
            if ($platformFeeInCents > 0) {
                $paymentIntentData['application_fee_amount'] = $platformFeeInCents;
            }
            
            $checkoutParams['payment_intent_data'] = $paymentIntentData;

            // Create Stripe Checkout Session
            // stripe_account must be passed as second parameter, not in the params array
            $checkoutSession = $this->stripe->checkout->sessions->create(
                $checkoutParams,
                ['stripe_account' => $stripeConnectId]
            );

            // Create a pending payment record
            // Note: amount stored is the invoice amount only (not including fees)
            // Fees are tracked separately in metadata and notes
            $payment = InvoicePayment::create([
                'invoice_unit_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => $paymentMethod === 'ach' ? 'stripe_ach' : 'stripe_card',
                'payment_reference' => $checkoutSession->id,
                'stripe_checkout_session_id' => $checkoutSession->id,
                'payment_date' => now(),
                'recorded_by' => $user->id,
                'notes' => "Stripe Checkout payment ({$paymentMethod}) - pending confirmation\nProcessing fee: \${$totalFee}",
                'status' => 'pending', // Will be updated to 'approved' when charge succeeds
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
            
            // Determine if we need connected account context
            $stripeAccountId = $invoice->tenant->getSetting('stripe_connect_id');
            
            // Update payment intent metadata with checkout session ID for reliable lookup
            try {
                $updateParams = [
                    'metadata' => [
                        'checkout_session_id' => $session->id,
                        'invoice_id' => $invoiceId,
                        'tenant_id' => $session->metadata->tenant_id ?? null,
                    ],
                ];
                
                if ($stripeAccountId) {
                    $this->stripe->paymentIntents->update(
                        $session->payment_intent,
                        $updateParams,
                        ['stripe_account' => $stripeAccountId]
                    );
                } else {
                    $this->stripe->paymentIntents->update($session->payment_intent, $updateParams);
                }
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
                // Get connected account context from payment's invoice
                $invoice = $payment->invoiceUnit;
                $stripeAccountId = $invoice ? $invoice->tenant->getSetting('stripe_connect_id') : null;
                
                $updateParams = [
                    'metadata' => array_merge($paymentIntent->metadata->toArray(), [
                        'checkout_session_id' => $payment->stripe_checkout_session_id,
                    ]),
                ];
                
                if ($stripeAccountId) {
                    $this->stripe->paymentIntents->update(
                        $paymentIntent->id,
                        $updateParams,
                        ['stripe_account' => $stripeAccountId]
                    );
                } else {
                    $this->stripe->paymentIntents->update($paymentIntent->id, $updateParams);
                }
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
                // Determine if this is a connected account charge by checking if application is set
                $stripeAccountId = null;
                if (isset($charge->application) && $charge->application) {
                    // This is a charge on a connected account
                    // We need to find which tenant this belongs to
                    try {
                        // Try to find invoice from charge metadata first (most direct)
                        $invoiceId = $charge->metadata->invoice_id ?? null;
                        
                        if (!$invoiceId) {
                            // Try payment intent from platform account if charge metadata is empty
                            try {
                                $tempPaymentIntent = $this->stripe->paymentIntents->retrieve($charge->payment_intent);
                                $invoiceId = $tempPaymentIntent->metadata->invoice_id ?? null;
                            } catch (\Exception $piError) {
                                // Ignore - will try other methods
                            }
                        }
                        
                        if ($invoiceId) {
                            $invoice = InvoiceUnit::find($invoiceId);
                            if ($invoice && $invoice->tenant) {
                                $stripeAccountId = $invoice->tenant->getSetting('stripe_connect_id');
                                Log::info('Found stripe account from metadata', [
                                    'invoice_id' => $invoiceId,
                                    'stripe_account_id' => $stripeAccountId,
                                ]);
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning('Could not determine stripe account from charge/payment intent', [
                            'charge_id' => $charge->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
                
                // Retrieve payment intent with or without connected account context
                if ($stripeAccountId) {
                    $paymentIntent = $this->stripe->paymentIntents->retrieve(
                        $charge->payment_intent,
                        [],
                        ['stripe_account' => $stripeAccountId]
                    );
                } else {
                    $paymentIntent = $this->stripe->paymentIntents->retrieve($charge->payment_intent);
                }
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
                        // Search for checkout sessions with this payment intent (with account context if available)
                        $sessionListParams = [
                            'payment_intent' => $charge->payment_intent,
                            'limit' => 1,
                        ];
                        
                        if ($stripeAccountId) {
                            $checkoutSessions = $this->stripe->checkout->sessions->all(
                                $sessionListParams,
                                ['stripe_account' => $stripeAccountId]
                            );
                        } else {
                            $checkoutSessions = $this->stripe->checkout->sessions->all($sessionListParams);
                        }
                        
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
            // Set status to 'approved' since Stripe has confirmed the payment
            $payment->status = 'approved';
            $payment->save();

            // Reload invoice and explicitly reload payments relationship to recalculate balance
            $invoice->refresh();
            $invoice->load('payments');
            
            // Calculate total paid and balance due (exclude temporary Stripe payments)
            $totalPaid = $invoice->payments()->confirmed()->sum('amount');
            $balanceDue = $invoice->balance_due; // Use accessor which calculates: total - sum(payments)

            Log::info('Invoice balance calculation after payment', [
                'invoice_id' => $invoice->id,
                'invoice_total' => $invoice->total,
                'total_paid' => $totalPaid,
                'balance_due' => $balanceDue,
                'current_status' => $invoice->status,
            ]);

            // Update invoice status based on balance due
            // If balance due is 0 or less (amount paid equals or exceeds total), mark as paid
            if ($balanceDue <= 0) {
                $invoice->status = 'paid';
                Log::info('Marking invoice as paid', [
                    'invoice_id' => $invoice->id,
                    'balance_due' => $balanceDue,
                ]);
            } elseif ($totalPaid > 0) {
                // If there are payments but balance remains, mark as partial
                $invoice->status = 'partial';
                Log::info('Marking invoice as partial', [
                    'invoice_id' => $invoice->id,
                    'total_paid' => $totalPaid,
                    'balance_due' => $balanceDue,
                ]);
            }

            $invoice->save();
            
            Log::info('Invoice status updated', [
                'invoice_id' => $invoice->id,
                'new_status' => $invoice->status,
            ]);

            DB::commit();

            // Regenerate PDF with payment details if invoice is fully paid
            if ($invoice->status === 'paid') {
                try {
                    // Refresh the payment object to ensure we have the latest data
                    $payment->refresh();
                    
                    // Load invoice with necessary relationships for PDF generation
                    $invoice->load(['unit', 'notes', 'tenant']);
                    
                    // Generate HTML with payment details
                    $html = $this->pdfService->generateInvoiceHtml($invoice, $payment);
                    
                    // Generate and store new PDF
                    $userId = $payment->recorder?->id ?? $invoice->creator?->id ?? 1;
                    $this->pdfService->generatePdf($invoice, $html, $userId);
                    
                    Log::info("PDF regenerated with payment details for invoice {$invoice->id} after Stripe payment {$payment->id}");
                } catch (\Exception $e) {
                    // Log error but don't fail the payment processing
                    Log::error('Failed to regenerate PDF after Stripe payment: ' . $e->getMessage(), [
                        'payment_id' => $payment->id,
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

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

