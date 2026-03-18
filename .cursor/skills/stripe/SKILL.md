---
name: stripe-neibrpay
description: Documents how Stripe is used in the NeibrPay HOA platform—Stripe Connect Express per tenant, Checkout Sessions for invoice payments, webhooks, and fee handling. Use when working on Stripe payments, invoice checkout, Stripe Connect, webhooks, or payment method (card/ACH) logic.
---

# Stripe in NeibrPay

## When to use this skill

Apply when modifying or debugging:

- Invoice payment flow (Stripe Checkout, fees, card/ACH)
- Stripe Connect (tenant onboarding, dashboard, verify, disconnect)
- Webhook handling (checkout.session.completed, charge.succeeded, etc.)
- Frontend: StripePaymentModal, StripeCheckoutButton, payments API, settings
- Backend: StripePaymentController, StripeConnectController, InvoicePayment model

For general Stripe API best practices (Checkout vs Payment Intents, no Charges API, etc.), use the **stripe-best-practices** skill if available.

---

## Architecture overview

- **Stripe Connect**: One **Express** connected account per **tenant**. Platform pays fees and losses (`controller.fees.payer = application`, `controller.losses.payments = application`).
- **Payments**: Invoice payments use **Stripe Checkout** (hosted). Sessions are created on the **connected account** via `stripe_account`. No direct Charges API; payment confirmation is driven by **charge.succeeded** after Checkout.
- **API version**: `2025-10-29.clover` (see `StripePaymentController::STRIPE_API_VERSION` and `StripeConnectController::STRIPE_API_VERSION`).

---

## Key files

| Area                                             | Location                                                                           |
| ------------------------------------------------ | ---------------------------------------------------------------------------------- |
| Checkout + webhooks                              | `backend/laravel/app/Http/Controllers/Api/StripePaymentController.php`             |
| Connect (onboard, dashboard, verify, disconnect) | `backend/laravel/app/Http/Controllers/Api/StripeConnectController.php`             |
| Payment model (Stripe fields)                    | `backend/laravel/app/Models/InvoicePayment.php`                                    |
| API client – payments                            | `packages/api-client/src/payments.ts`                                              |
| API client – Connect                             | `packages/api-client/src/stripe.ts`                                                |
| Checkout UI                                      | `apps/admin-web/src/components/StripeCheckoutButton.vue`, `StripePaymentModal.vue` |
| Webhook route (no auth)                          | `backend/laravel/routes/api.php` – `POST stripe/webhook`                           |

---

## Checkout flow

1. **Create session** (backend): `StripePaymentController::createCheckoutSession`.
   - Resolve tenant’s `stripe_connect_id` and ensure `charges_enabled`.
   - Amount: request amount or invoice `balance_due`; min $0.50; cannot exceed balance.
   - **Fees**: Platform 1%; card 2.9% + $0.30; ACH 0.8% capped at $5. Two line items: (1) HOA dues, (2) processing fee (if any).
   - **Cents**: Use `(int)round($amount * 100)` (avoid float truncation).
   - **Connected account**: Pass as second argument: `['stripe_account' => $stripeConnectId]` when calling `$this->stripe->checkout->sessions->create($params, ['stripe_account' => $stripeConnectId])`.
   - **Metadata** (session + payment_intent_data): `invoice_id`, `invoice_number`, `tenant_id`, `unit_id`, `user_id`, `payment_method`, `invoice_amount`, `processing_fee`. Payment intent also gets `application_fee_amount` (platform share only, in cents).
   - **payment_method_types**: `['card']` or `['us_bank_account']`; for ACH, set `payment_method_options.us_bank_account.financial_connections.permissions = ['payment_method']`.
   - Create **InvoicePayment** with `status = 'pending'`, `stripe_checkout_session_id = session.id`, `payment_reference = session.id`, `payment_method = 'stripe_card' | 'stripe_ach'`.

2. **Frontend**: `paymentsApi.createStripeCheckout(invoiceId, 'card' | 'ach', amount?)` then redirect to `response.checkout_url`. Optional amount; omit for full balance.

3. **Success/cancel URLs**: `success_url` includes `?payment=success&session_id={CHECKOUT_SESSION_ID}`; `cancel_url` points back to invoice with `?payment=cancelled`.

---

## Webhooks

- **Endpoint**: `POST /api/stripe/webhook` (no auth; verify with `Stripe-Signature` and `config('services.stripe.webhook_secret')`).
- **Handled events**:
  - `checkout.session.completed`: Find payment by `stripe_checkout_session_id`; store `payment_intent` on payment; ensure payment intent metadata has `checkout_session_id` (for charge.succeeded lookup). Use `['stripe_account' => $stripeConnectId]` when updating payment intent if tenant has Connect.
  - `payment_intent.succeeded`: Find payment by `metadata.checkout_session_id` or `stripe_payment_intent_id`; set `stripe_payment_intent_id`; do **not** mark payment approved here (wait for charge.succeeded).
  - `charge.succeeded`: Find payment by `stripe_payment_intent_id` or via payment intent’s `metadata.checkout_session_id`. Update payment: `payment_reference = charge.id`, `status = 'approved'`, `payment_method` from charge details (`us_bank_account` → `stripe_ach`, else `stripe_card`). Recompute invoice `balance_due` and set invoice `status` to `paid` or `partial`. Idempotency: skip if `payment.payment_reference === charge.id`. For connected accounts, use `['stripe_account' => $stripeAccountId]` when retrieving payment intent/sessions.
  - `payment_intent.payment_failed`: Update payment notes; do not change status to a “failed” enum if not defined—keep as pending or document behavior.
  - `checkout.session.expired`: Delete **pending** payment with that `stripe_checkout_session_id` and no `stripe_payment_intent_id` (orphan cleanup).

**Critical**: Payment intent metadata must include `checkout_session_id` so `charge.succeeded` can resolve the correct InvoicePayment when only `charge.payment_intent` is available.

---

## Fee and amount rules

- **Platform**: 1% of payment amount.
- **Card**: 2.9% + $0.30 (Stripe).
- **ACH**: 0.8% capped at $5.00 (Stripe).
- **Application fee**: Only the platform’s 1% (in cents) via `payment_intent_data.application_fee_amount`.
- **Minimum payment**: $0.50.
- **Stored amount**: InvoicePayment `amount` is the **invoice amount** only; fees are in metadata/notes.

---

## Payment method values

- Backend/DB: `stripe_card`, `stripe_ach` (InvoicePayment.payment_method).
- Checkout request: `card` or `ach` (createCheckoutSession body and metadata).
- Frontend: `paymentsApi.createStripeCheckout(invoiceId, 'card' | 'ach', amount?)`.

---

## Stripe Connect (tenant)

- **Setup**: Admin only. `StripeConnectController::connect` creates Express account with `controller.stripe_dashboard.type = express`, `controller.fees.payer = application`, `controller.losses.payments = application`; capabilities include `card_payments`, `transfers`, `us_bank_account_ach_payments`, `link_payments`. Tenant settings: `stripe_connect_id`, `stripe_connect_status`, `charges_enabled`, `details_submitted`.
- **Onboarding**: Redirect to `accountLinks` (account_onboarding); refresh/return URLs point to frontend settings (#payments).
- **Verify**: `StripeConnectController::verify` updates tenant settings from Stripe account; frontend uses `stripeApi.verifyStatus()` and invalidates settings query.
- **Dashboard**: `stripeApi.getDashboardLink()` → open Express dashboard.
- **Disconnect**: `stripeApi.disconnect()`; backend deletes Connect account and clears Stripe tenant settings.
- **Payments**: Checkout is only allowed when tenant has `stripe_connect_id` and `charges_enabled`; otherwise return user-friendly error (e.g. “Stripe payment processing is not available”).

---

## Frontend usage

- **Checkout**: Use `paymentsApi.createStripeCheckout(invoiceId, paymentMethod, amount?)` then redirect to `checkout_url`. For fee display, use `paymentsApi.calculateFees(invoiceId, amount?)`.
- **Status**: `paymentsApi.getStripePaymentStatus(invoiceId)` for pending and latest approved payment.
- **Connect**: `stripeApi.connect()`, `stripeApi.getDashboardLink()`, `stripeApi.verifyStatus()`, `stripeApi.disconnect()`; use `useVerifyStripeStatus()` and `useDisconnectStripe()` for mutations with settings invalidation.

---

## Checklist for changes

- [ ] All amounts to Stripe in **cents** via `(int)round($amount * 100)`.
- [ ] Checkout and Stripe API calls for a tenant use **connected account** where applicable: `['stripe_account' => $stripeConnectId]`.
- [ ] Payment intent metadata includes **checkout_session_id** for webhook lookup.
- [ ] **charge.succeeded** is the event that approves the payment and updates invoice status; **payment_intent.succeeded** only links payment intent ID.
- [ ] Webhook handler returns 200 after signature verification even on processing errors (log and return `received: true` to avoid Stripe retry storms).
- [ ] New webhook events: add to `handleWebhook` switch and consider idempotency and connected-account context.
