<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function __construct(
        protected FileStorageService $fileStorage
    ) {
    }

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

            $zelleQrPath = $settings['zelle_qr_path'] ?? null;
            $hasZelleQr = $zelleQrPath && $this->fileStorage->exists($zelleQrPath);

            $logoPath = $settings['logo_path'] ?? null;
            $hasLogo = $logoPath && $this->fileStorage->exists($logoPath);

            $reminderDefaults = [
                'invoice_due' => [
                    'enabled' => true,
                    'pre_due_offsets_days' => [30, 15, 7, 3, 2, 1],
                    'post_due_interval_days' => 3,
                    'post_due_max_reminders' => null,
                    'post_due_stop_after_days' => null,
                ],
                'events' => [
                    'enabled' => false,
                ],
            ];
            $reminders = array_replace_recursive($reminderDefaults, (array) ($settings['reminders'] ?? []));

            // Logo and Zelle QR are fetched via signed-URL endpoints (tenant/hoa-logo/url, tenant/zelle-qr/url)
            // like Invoice PDFs; no long-lived URLs in settings response.

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
                        'zelle_enabled' => (bool) ($settings['zelle_enabled'] ?? false),
                        'zelle_email' => $settings['zelle_email'] ?? null,
                        'zelle_phone' => $settings['zelle_phone'] ?? null,
                        'has_zelle_qr' => $hasZelleQr,
                        'zelle_instructions' => $settings['zelle_instructions'] ?? null,
                        'has_logo' => $hasLogo,
                        'reminders' => $reminders,
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

