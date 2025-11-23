<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Account;
use Stripe\Exception\ApiErrorException;
use Stripe\Stripe;
use Stripe\StripeClient;

class StripeConnectController extends Controller
{
    private StripeClient $stripe;

    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Create a Stripe Connect Express account for the tenant and return onboarding link
     */
    public function connect(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $tenant = $user->tenant;
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            // Only admins can set up Stripe Connect
            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Only admin users can set up Stripe Connect'], 403);
            }

            // Check if already connected
            $stripeConnectId = $tenant->getSetting('stripe_connect_id');
            if ($stripeConnectId) {
                return response()->json([
                    'message' => 'Stripe Connect already set up for this tenant',
                    'stripe_connect_id' => $stripeConnectId,
                ], 200);
            }

            // Create Express account
            $account = $this->stripe->accounts->create([
                'type' => 'express',
                'country' => $tenant->country ?? 'US',
                'email' => $tenant->email ?? $user->email,
                'business_profile' => [
                    'name' => $tenant->name,
                    'url' => config('app.frontend_url'),
                ],
            ]);

            // Save stripe_connect_id to tenant settings
            $tenant->setSetting('stripe_connect_id', $account->id);
            $tenant->setSetting('stripe_connect_status', 'pending');
            $tenant->setSetting('charges_enabled', false);
            $tenant->setSetting('details_submitted', false);

            Log::info('Stripe Express account created', [
                'tenant_id' => $tenant->id,
                'stripe_account_id' => $account->id,
            ]);

            // Get onboarding link
            $frontendUrl = config('app.frontend_url', 'http://localhost:3000');
            $onboardingLink = $this->stripe->accountLinks->create([
                'account' => $account->id,
                'type' => 'account_onboarding',
                'refresh_url' => "{$frontendUrl}/settings?stripe_connect=refresh",
                'return_url' => "{$frontendUrl}/settings?stripe_connect=success",
            ]);

            return response()->json([
                'message' => 'Stripe Express account created successfully',
                'stripe_connect_id' => $account->id,
                'onboarding_url' => $onboardingLink->url,
            ], 201);
        } catch (ApiErrorException $e) {
            Log::error('Stripe Connect API error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Stripe Connect error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to create Stripe Connect account'], 500);
        }
    }

    /**
     * Get a login link to the Stripe Express dashboard
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $tenant = $user->tenant;
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            // Only admins can access the dashboard
            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Only admin users can access the Stripe dashboard'], 403);
            }

            $stripeConnectId = $tenant->getSetting('stripe_connect_id');
            if (!$stripeConnectId) {
                return response()->json(['error' => 'Stripe Connect not set up for this tenant'], 404);
            }

            // Create login link
            $loginLink = $this->stripe->accounts->createLoginLink($stripeConnectId);

            return response()->json([
                'dashboard_url' => $loginLink->url,
            ], 200);
        } catch (ApiErrorException $e) {
            Log::error('Stripe dashboard link error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Stripe dashboard error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to get dashboard link'], 500);
        }
    }

    /**
     * Verify Stripe Connect account status
     */
    public function verify(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $tenant = $user->tenant;
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            // Only admins can verify
            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Only admin users can verify Stripe account'], 403);
            }

            $stripeConnectId = $tenant->getSetting('stripe_connect_id');
            if (!$stripeConnectId) {
                return response()->json(['error' => 'Stripe Connect not set up for this tenant'], 404);
            }

            // Fetch account details from Stripe
            $account = $this->stripe->accounts->retrieve($stripeConnectId);

            // Update tenant settings with account status
            $tenant->setSetting('charges_enabled', $account->charges_enabled);
            $tenant->setSetting('details_submitted', $account->details_submitted);
            $tenant->setSetting('stripe_connect_status', $account->charges_enabled ? 'active' : 'pending');

            // Store additional useful info
            $tenant->setSetting('stripe_account_country', $account->country);
            $tenant->setSetting('stripe_account_default_currency', $account->default_currency ?? 'usd');

            Log::info('Stripe account verified', [
                'tenant_id' => $tenant->id,
                'stripe_account_id' => $stripeConnectId,
                'charges_enabled' => $account->charges_enabled,
                'details_submitted' => $account->details_submitted,
            ]);

            return response()->json([
                'stripe_connect_id' => $stripeConnectId,
                'charges_enabled' => $account->charges_enabled,
                'details_submitted' => $account->details_submitted,
                'status' => $account->charges_enabled ? 'active' : 'pending',
            ], 200);
        } catch (ApiErrorException $e) {
            Log::error('Stripe verification error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Stripe verify error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to verify Stripe account'], 500);
        }
    }

    /**
     * Disconnect and delete Stripe Connect account
     */
    public function disconnect(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $tenant = $user->tenant;
            if (!$tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            // Only admins can disconnect Stripe
            if ($user->role !== 'admin') {
                return response()->json(['error' => 'Only admin users can disconnect Stripe'], 403);
            }

            $stripeConnectId = $tenant->getSetting('stripe_connect_id');
            if (!$stripeConnectId) {
                return response()->json(['error' => 'Stripe Connect not set up for this tenant'], 404);
            }

            try {
                // Delete the Stripe Express account
                $this->stripe->accounts->delete($stripeConnectId);
                Log::info('Stripe account deleted', [
                    'tenant_id' => $tenant->id,
                    'stripe_account_id' => $stripeConnectId,
                ]);
            } catch (ApiErrorException $e) {
                // Handle specific Stripe errors
                if (strpos($e->getMessage(), 'No such account') !== false) {
                    Log::warning('Stripe account already deleted or does not exist', [
                        'tenant_id' => $tenant->id,
                        'stripe_account_id' => $stripeConnectId,
                    ]);
                    // Continue to clear local settings even if account doesn't exist
                } else {
                    throw $e;
                }
            }

            // Clear all Stripe-related settings
            $tenant->setSetting('stripe_connect_id', null);
            $tenant->setSetting('stripe_connect_status', 'not_connected');
            $tenant->setSetting('charges_enabled', false);
            $tenant->setSetting('details_submitted', false);
            $tenant->setSetting('stripe_account_country', null);
            $tenant->setSetting('stripe_account_default_currency', null);

            Log::info('Stripe settings cleared for tenant', [
                'tenant_id' => $tenant->id,
            ]);

            return response()->json([
                'message' => 'Stripe account disconnected successfully. You can now connect a new account.',
            ], 200);
        } catch (ApiErrorException $e) {
            Log::error('Stripe disconnect API error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            Log::error('Stripe disconnect error', [
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Failed to disconnect Stripe account'], 500);
        }
    }
}

