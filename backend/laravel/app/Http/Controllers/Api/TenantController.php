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
}

