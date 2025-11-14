<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class TenantController extends Controller
{
    /**
     * Update tenant/community settings
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'address' => 'sometimes|nullable|string',
                'phone' => 'sometimes|nullable|string|max:20',
            ]);

            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->load('tenant');

            if (!$user->tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $tenant = $user->tenant;

            // Update only provided fields
            if (isset($validated['name'])) {
                $tenant->name = $validated['name'];
            }
            if (isset($validated['address'])) {
                $tenant->address = $validated['address'];
            }
            if (isset($validated['phone'])) {
                $tenant->phone = $validated['phone'];
            }

            $tenant->save();

            return response()->json([
                'message' => 'Tenant settings updated successfully',
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'address' => $tenant->address ?? '',
                    'phone' => $tenant->phone ?? '',
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update tenant settings failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update tenant settings'], 500);
        }
    }

    /**
     * Update localization settings
     */
    public function updateLocalization(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'currency' => 'sometimes|string|in:USD,INR',
                'currency_format' => 'sometimes|string|max:50',
                'timezone' => 'sometimes|string|max:100',
                'date_format' => 'sometimes|string|in:MM/DD/YYYY,DD/MM/YYYY,YYYY-MM-DD',
                'first_month_of_year' => 'sometimes|string|in:January,February,March,April,May,June,July,August,September,October,November,December',
            ]);

            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->load('tenant');

            if (!$user->tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $tenant = $user->tenant;
            $settings = $tenant->settings ?? [];

            // Update only provided settings
            foreach ($validated as $key => $value) {
                $settings[$key] = $value;
            }

            $tenant->settings = $settings;
            $tenant->save();

            return response()->json([
                'message' => 'Localization settings updated successfully',
                'settings' => $settings,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update localization settings failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update localization settings'], 500);
        }
    }

    /**
     * Update PayPal settings
     */
    public function updatePayPalSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'enabled' => 'sometimes|boolean',
                'client_id' => 'required_if:enabled,true|nullable|string|max:255',
                'client_secret' => 'required_if:enabled,true|nullable|string|max:255',
                'mode' => 'sometimes|string|in:sandbox,live',
                'webhook_id' => 'nullable|string|max:255',
            ]);

            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->load('tenant');

            if (!$user->tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $tenant = $user->tenant;
            $settings = $tenant->settings ?? [];
            
            // Get existing PayPal config or initialize
            $paypalConfig = $settings['paypal'] ?? [
                'enabled' => false,
                'client_id' => null,
                'client_secret' => null,
                'mode' => 'sandbox',
                'webhook_id' => null,
            ];

            // Update only provided fields
            if (isset($validated['enabled'])) {
                $paypalConfig['enabled'] = (bool) $validated['enabled'];
            }
            if (isset($validated['client_id'])) {
                $paypalConfig['client_id'] = $validated['client_id'];
            }
            if (isset($validated['client_secret'])) {
                $paypalConfig['client_secret'] = $validated['client_secret'];
            }
            if (isset($validated['mode'])) {
                $paypalConfig['mode'] = $validated['mode'];
            }
            if (isset($validated['webhook_id'])) {
                $paypalConfig['webhook_id'] = $validated['webhook_id'];
            }

            // If enabling PayPal, ensure required fields are present
            if ($paypalConfig['enabled'] && (!$paypalConfig['client_id'] || !$paypalConfig['client_secret'])) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => [
                        'client_id' => ['Client ID is required when PayPal is enabled'],
                        'client_secret' => ['Client Secret is required when PayPal is enabled'],
                    ],
                ], 422);
            }

            // Save PayPal config
            $settings['paypal'] = $paypalConfig;
            $tenant->settings = $settings;
            $tenant->save();

            return response()->json([
                'message' => 'PayPal settings updated successfully',
                'paypal' => $paypalConfig,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update PayPal settings failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update PayPal settings'], 500);
        }
    }

    /**
     * Update Stripe-specific settings for a tenant.
     */
    public function updateStripeSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'enabled' => 'sometimes|boolean',
                'key' => 'required_if:enabled,true|nullable|string|max:255',
                'secret' => 'required_if:enabled,true|nullable|string|max:255',
                'webhook_secret' => 'nullable|string|max:255',
            ]);

            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }

            $user->load('tenant');

            if (!$user->tenant) {
                return response()->json(['error' => 'Tenant not found'], 404);
            }

            $tenant = $user->tenant;
            $settings = $tenant->settings ?? [];
            
            // Get existing Stripe config or initialize
            $stripeConfig = $settings['stripe'] ?? [
                'enabled' => false,
                'key' => null,
                'secret' => null,
                'webhook_secret' => null,
            ];

            // Update only provided fields
            if (isset($validated['enabled'])) {
                $stripeConfig['enabled'] = (bool) $validated['enabled'];
            }
            if (isset($validated['key'])) {
                $stripeConfig['key'] = $validated['key'];
            }
            if (isset($validated['secret'])) {
                $stripeConfig['secret'] = $validated['secret'];
            }
            if (isset($validated['webhook_secret'])) {
                $stripeConfig['webhook_secret'] = $validated['webhook_secret'];
            }

            // If enabling Stripe, ensure required fields are present
            if ($stripeConfig['enabled'] && (!$stripeConfig['key'] || !$stripeConfig['secret'])) {
                return response()->json([
                    'error' => 'Validation failed',
                    'errors' => [
                        'key' => ['Publishable Key is required when Stripe is enabled'],
                        'secret' => ['Secret Key is required when Stripe is enabled'],
                    ],
                ], 422);
            }

            // Save Stripe config
            $settings['stripe'] = $stripeConfig;
            $tenant->settings = $settings;
            $tenant->save();

            return response()->json([
                'message' => 'Stripe settings updated successfully',
                'stripe' => $stripeConfig,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update Stripe settings failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update Stripe settings'], 500);
        }
    }
}

