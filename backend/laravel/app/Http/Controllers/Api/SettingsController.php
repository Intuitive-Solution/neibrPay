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
            $user = $request->get('firebase_user');

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
                    'phone' => $tenant->phone ?? '',
                    'settings' => [
                        'currency' => $settings['currency'] ?? 'USD',
                        'currency_format' => $settings['currency_format'] ?? '$#,##0.00',
                        'timezone' => $settings['timezone'] ?? 'UTC',
                        'date_format' => $settings['date_format'] ?? 'MM/DD/YYYY',
                        'first_month_of_year' => $settings['first_month_of_year'] ?? 'January',
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

