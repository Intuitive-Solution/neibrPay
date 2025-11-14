<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InvoicePayment;
use App\Models\InvoiceUnit;
use App\Models\Tenant;
use App\Services\AnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Order;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PayPalPaymentController extends Controller
{
    protected AnalyticsService $analytics;

    public function __construct(AnalyticsService $analytics)
    {
        $this->analytics = $analytics;
    }

    /**
     * Get PayPal API context for a tenant.
     */
    private function getApiContext(Tenant $tenant): ApiContext
    {
        $paypalConfig = $tenant->getPayPalConfig();
        
        if (!$paypalConfig || !$paypalConfig['client_id'] || !$paypalConfig['client_secret']) {
            throw new \Exception('PayPal is not configured for this tenant.');
        }

        $apiContext = new ApiContext(
            new OAuthTokenCredential(
                $paypalConfig['client_id'],
                $paypalConfig['client_secret']
            )
        );

        $apiContext->setConfig([
            'mode' => $paypalConfig['mode'] === 'live' ? 'live' : 'sandbox',
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'DEBUG',
            'cache.enabled' => true,
            'cache.FileName' => storage_path('logs/paypal_cache'),
        ]);

        return $apiContext;
    }

    /**
     * Create a PayPal order for an invoice.
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

        // Get tenant and check PayPal is enabled
        $tenant = $user->tenant;
        if (!$tenant->getPayPalEnabled()) {
            return response()->json([
                'message' => 'PayPal is not enabled for this organization.',
            ], 403);
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
            $apiContext = $this->getApiContext($tenant);
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');

            // Create payer
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            // Create item
            $item = new Item();
            $item->setName('HOA Dues Payment')
                ->setCurrency('USD')
                ->setQuantity(1)
                ->setPrice($amount);

            // Create item list
            $itemList = new ItemList();
            $itemList->setItems([$item]);

            // Create amount
            $amountObj = new Amount();
            $amountObj->setCurrency('USD')
                ->setTotal($amount);

            // Create transaction
            $transaction = new Transaction();
            $transaction->setAmount($amountObj)
                ->setItemList($itemList)
                ->setDescription("Invoice #{$invoice->invoice_number} - {$invoice->unit->title}")
                ->setInvoiceNumber($invoice->invoice_number)
                ->setCustom(json_encode([
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'tenant_id' => $invoice->tenant_id,
                    'unit_id' => $invoice->unit_id,
                    'user_id' => $user->id,
                ]));

            // Create redirect URLs
            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl("{$frontendUrl}/invoices/{$invoiceId}?payment=success&order_id={ORDER_ID}")
                ->setCancelUrl("{$frontendUrl}/invoices/{$invoiceId}?payment=cancelled");

            // Create payment
            $payment = new Payment();
            $payment->setIntent('sale')
                ->setPayer($payer)
                ->setRedirectUrls($redirectUrls)
                ->setTransactions([$transaction]);

            // Create payment
            $payment->create($apiContext);

            // Get approval URL
            $approvalUrl = $payment->getApprovalLink();

            // Create a pending payment record
            $paymentRecord = InvoicePayment::create([
                'invoice_unit_id' => $invoice->id,
                'amount' => $amount,
                'payment_method' => 'paypal_balance', // Will be updated when webhook is received
                'payment_reference' => $payment->getId(),
                'paypal_order_id' => $payment->getId(),
                'payment_date' => now(),
                'recorded_by' => $user->id,
                'notes' => 'PayPal payment - pending confirmation',
            ]);

            // Track payment initiation
            $this->analytics->captureEvent('payment_initiated', $user->id, [
                'payment_id' => $paymentRecord->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'method' => 'paypal',
                'currency' => 'USD',
                'tenant_id' => $user->tenant_id,
            ]);

            Log::info('PayPal payment created', ['payment' => $paymentRecord]);

            return response()->json([
                'data' => [
                    'checkout_url' => $approvalUrl,
                    'order_id' => $payment->getId(),
                    'payment_id' => $paymentRecord->id,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('PayPal order creation failed', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Failed to create payment session.',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while processing your request.',
            ], 500);
        }
    }

    /**
     * Handle PayPal webhook events.
     * This endpoint should NOT have auth middleware.
     */
    public function handleWebhook(Request $request): JsonResponse
    {
        $payload = $request->all();
        $eventType = $payload['event_type'] ?? null;

        if (!$eventType) {
            Log::warning('PayPal webhook received without event_type', ['payload' => $payload]);
            return response()->json(['error' => 'Invalid webhook payload'], 400);
        }

        try {
            // Extract tenant_id from resource metadata
            $resource = $payload['resource'] ?? [];
            $custom = $resource['custom'] ?? null;
            
            if ($custom) {
                $customData = json_decode($custom, true);
                $tenantId = $customData['tenant_id'] ?? null;
                
                if ($tenantId) {
                    $tenant = Tenant::find($tenantId);
                    if ($tenant) {
                        // Verify webhook signature using tenant's webhook_id
                        $webhookId = $tenant->getPayPalConfig()['webhook_id'] ?? null;
                        // Note: PayPal webhook verification would go here if needed
                    }
                }
            }

            // Handle the event
            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCaptured($payload);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePaymentDenied($payload);
                    break;

                default:
                    Log::info('Unhandled PayPal webhook event', [
                        'type' => $eventType,
                    ]);
            }

            return response()->json(['received' => true]);
        } catch (\Exception $e) {
            Log::error('Error processing PayPal webhook', [
                'event_type' => $eventType,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Webhook processing failed',
            ], 500);
        }
    }

    /**
     * Handle PAYMENT.CAPTURE.COMPLETED event.
     */
    private function handlePaymentCaptured(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $paymentId = $resource['id'] ?? null;
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;
        $custom = $resource['custom'] ?? null;

        if (!$paymentId || !$custom) {
            Log::warning('PayPal payment captured without required data', [
                'payment_id' => $paymentId,
                'payload' => $payload,
            ]);
            return;
        }

        $customData = json_decode($custom, true);
        $invoiceId = $customData['invoice_id'] ?? null;
        $tenantId = $customData['tenant_id'] ?? null;

        if (!$invoiceId || !$tenantId) {
            Log::warning('PayPal payment captured without invoice_id or tenant_id', [
                'payment_id' => $paymentId,
                'custom_data' => $customData,
            ]);
            return;
        }

        // Find the payment record
        $payment = InvoicePayment::where('paypal_order_id', $orderId)
            ->orWhere('paypal_payment_id', $paymentId)
            ->first();

        if (!$payment) {
            Log::warning('Payment record not found for PayPal payment', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
                'invoice_id' => $invoiceId,
            ]);
            return;
        }

        // Check if payment is already processed
        if ($payment->paypal_payment_id === $paymentId && $payment->payment_reference === $paymentId) {
            Log::info('Payment already processed for this PayPal payment', [
                'payment_id' => $payment->id,
                'paypal_payment_id' => $paymentId,
            ]);
            return;
        }

        $invoice = $payment->invoiceUnit;

        DB::beginTransaction();
        try {
            // Determine payment method type from resource
            $paymentMethodType = 'paypal_balance';
            $paypalPaymentMethod = 'paypal_balance';

            // Check payment method from resource
            $paymentMethod = $resource['payment_method'] ?? null;
            if ($paymentMethod === 'CREDIT_CARD') {
                $paymentMethodType = 'paypal_card';
                $paypalPaymentMethod = 'card';
            } elseif ($paymentMethod === 'BANK_ACCOUNT' || isset($resource['bank_reference'])) {
                $paymentMethodType = 'paypal_bank_account';
                $paypalPaymentMethod = 'bank_account';
            }

            // Update payment record
            $payment->payment_method = $paymentMethodType;
            $payment->paypal_order_id = $orderId;
            $payment->paypal_payment_id = $paymentId;
            $payment->paypal_payment_method = $paypalPaymentMethod;
            $payment->payment_reference = $paymentId;
            $payment->notes = 'PayPal payment confirmed - ' . ucfirst(str_replace('_', ' ', $paypalPaymentMethod)) . ' (Payment: ' . $paymentId . ')';
            $payment->save();

            // Reload invoice to get updated payments relationship
            $invoice->refresh();
            
            // Calculate total paid and balance due
            $totalPaid = $invoice->payments()->confirmed()->sum('amount');
            $balanceDue = $invoice->balance_due;

            // Update invoice status based on balance due
            if ($balanceDue <= 0) {
                $invoice->status = 'paid';
            } elseif ($totalPaid > 0) {
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
                    'paypal_payment_id' => $paymentId,
                    'invoice_total' => $invoice->total,
                    'balance_due' => $balanceDue,
                    'invoice_status' => $invoice->status,
                    'days_outstanding' => $invoice->created_at->diffInDays(now()),
                    'tenant_id' => $invoice->tenant_id,
                ]);
            }

            Log::info('PayPal payment processed successfully', [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'paypal_payment_id' => $paymentId,
                'amount' => $payment->amount,
                'method' => $paymentMethodType,
                'total_paid' => $totalPaid,
                'invoice_total' => $invoice->total,
                'balance_due' => $balanceDue,
                'invoice_status' => $invoice->status,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing PayPal payment captured', [
                'paypal_payment_id' => $paymentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle payment denied/refunded events.
     */
    private function handlePaymentDenied(array $payload): void
    {
        $resource = $payload['resource'] ?? [];
        $paymentId = $resource['id'] ?? null;
        $orderId = $resource['supplementary_data']['related_ids']['order_id'] ?? null;

        if (!$paymentId) {
            return;
        }

        $payment = InvoicePayment::where('paypal_payment_id', $paymentId)
            ->orWhere('paypal_order_id', $orderId)
            ->first();

        if (!$payment) {
            Log::warning('Payment record not found for denied PayPal payment', [
                'payment_id' => $paymentId,
                'order_id' => $orderId,
            ]);
            return;
        }

        // Update payment notes with failure reason
        $reason = $resource['reason_code'] ?? 'Payment denied';
        $payment->notes = "PayPal payment denied: {$reason}";
        $payment->save();

        // Track payment failure
        $invoice = $payment->invoiceUnit;
        $userId = $payment->recorder?->id ?? $invoice->creator?->id;
        if ($userId) {
            $this->analytics->captureEvent('payment_failed', $userId, [
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $payment->amount,
                'reason' => $reason,
                'paypal_payment_id' => $paymentId,
                'tenant_id' => $invoice->tenant_id,
            ]);
        }

        Log::warning('PayPal payment denied', [
            'payment_id' => $payment->id,
            'paypal_payment_id' => $paymentId,
            'reason' => $reason,
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

        // Get pending PayPal payments for this invoice
        $pendingPayments = InvoicePayment::where('invoice_unit_id', $invoiceId)
            ->whereNotNull('paypal_order_id')
            ->whereNull('paypal_payment_id')
            ->get();

        return response()->json([
            'data' => [
                'has_pending_payments' => $pendingPayments->count() > 0,
                'pending_payments' => $pendingPayments->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'amount' => $payment->amount,
                        'order_id' => $payment->paypal_order_id,
                        'created_at' => $payment->created_at,
                    ];
                }),
            ],
        ]);
    }
}


