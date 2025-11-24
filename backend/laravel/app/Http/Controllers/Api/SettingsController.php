<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    /**
     * Get all settings (tenant + user + localization)
     */
    public function index(Request $request): JsonResponse
    {
        try {
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

            return response()->json([
                'tenant' => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'address' => $tenant->address ?? '',
                    'city' => $tenant->city ?? '',
                    'state' => $tenant->state ?? '',
                    'zip_code' => $tenant->zip_code ?? '',
                    'phone' => $tenant->phone ?? '',
                    'email' => $tenant->email ?? '',
                    'settings' => [
                        'currency' => $settings['currency'] ?? 'USD',
                        'currency_format' => $settings['currency_format'] ?? '$#,##0.00',
                        'timezone' => $settings['timezone'] ?? 'UTC',
                        'date_format' => $settings['date_format'] ?? 'MM/DD/YYYY',
                        'first_month_of_year' => $settings['first_month_of_year'] ?? 'January',
                        'stripe_connect_id' => $settings['stripe_connect_id'] ?? null,
                        'stripe_connect_status' => $settings['stripe_connect_status'] ?? 'not_connected',
                        'charges_enabled' => $settings['charges_enabled'] ?? false,
                        'details_submitted' => $settings['details_submitted'] ?? false,
                    ],
                ],
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $user->phone_number ?? '',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Get settings failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get settings'], 500);
        }
    }
}

