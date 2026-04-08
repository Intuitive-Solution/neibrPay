<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Services\FileStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class TenantController extends Controller
{
    public function __construct(
        protected FileStorageService $fileStorage
    ) {
    }
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
     * Update reminder settings.
     */
    public function updateReminderSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'invoice_due.enabled' => 'sometimes|boolean',
                'invoice_due.pre_due_offsets_days' => 'sometimes|array',
                'invoice_due.pre_due_offsets_days.*' => 'integer|min:1|max:365',
                'invoice_due.post_due_interval_days' => 'sometimes|integer|min:1|max:365',
                'invoice_due.post_due_max_reminders' => 'sometimes|nullable|integer|min:1|max:365',
                'invoice_due.post_due_stop_after_days' => 'sometimes|nullable|integer|min:1|max:3650',
                'events.enabled' => 'sometimes|boolean',
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
            $reminders = (array) ($settings['reminders'] ?? []);

            if (array_key_exists('invoice_due', $validated)) {
                $invoiceDue = (array) ($reminders['invoice_due'] ?? []);
                $incoming = (array) $validated['invoice_due'];
                $invoiceDue = array_replace($invoiceDue, $incoming);

                if (array_key_exists('pre_due_offsets_days', $invoiceDue)) {
                    $offsets = array_values(array_unique(array_map('intval', (array) $invoiceDue['pre_due_offsets_days'])));
                    sort($offsets);
                    $invoiceDue['pre_due_offsets_days'] = $offsets;
                }

                $reminders['invoice_due'] = $invoiceDue;
            }

            if (array_key_exists('events', $validated)) {
                $events = (array) ($reminders['events'] ?? []);
                $events = array_replace($events, (array) $validated['events']);
                $reminders['events'] = $events;
            }

            $settings['reminders'] = $reminders;
            $tenant->settings = $settings;
            $tenant->save();

            return response()->json([
                'message' => 'Reminder settings updated successfully',
                'settings' => $reminders,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update reminder settings failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update reminder settings'], 500);
        }
    }

    /**
     * Update Zelle payment settings
     */
    public function updateZelleSettings(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'zelle_enabled' => 'sometimes|boolean',
                'zelle_email' => 'sometimes|nullable|email|max:255',
                'zelle_phone' => 'sometimes|nullable|string|max:20',
                'zelle_instructions' => 'sometimes|nullable|string|max:2000',
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
            foreach ($validated as $key => $value) {
                $settings[$key] = $value;
            }
            // Always persist zelle_enabled from request so unchecking the checkbox updates the DB
            if ($request->has('zelle_enabled')) {
                $settings['zelle_enabled'] = $request->boolean('zelle_enabled');
            }
            $tenant->settings = $settings;
            $tenant->save();

            return response()->json([
                'message' => 'Zelle settings updated successfully',
                'settings' => $settings,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Update Zelle settings failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update Zelle settings'], 500);
        }
    }

    /**
     * Upload Zelle QR code image
     */
    public function uploadZelleQr(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|max:2048|mimes:png,jpg,jpeg',
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
            $file = $validated['file'];
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'zelle-qr.' . $extension;
            $dir = 'tenant-settings/' . $tenant->id;
            $filePath = $dir . '/' . $filename;

            $stored = $this->fileStorage->store($filePath, file_get_contents($file->getRealPath()));
            if (!$stored) {
                return response()->json(['message' => 'Failed to store QR image'], 500);
            }

            $settings = $tenant->settings ?? [];
            $settings['zelle_qr_path'] = $filePath;
            $tenant->settings = $settings;
            $tenant->save();

            $zelleQrUrl = null;
            if ($this->fileStorage->exists($filePath)) {
                try {
                    $zelleQrUrl = $this->fileStorage->getUrl($filePath);
                } catch (\Throwable $e) {
                    Log::warning('Failed to get Zelle QR URL after upload', ['path' => $filePath, 'error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'message' => 'Zelle QR code uploaded successfully',
                'zelle_qr_path' => $filePath,
                'zelle_qr_url' => $zelleQrUrl,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Upload Zelle QR failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to upload Zelle QR code'], 500);
        }
    }

    /**
     * Remove Zelle QR code image
     */
    public function removeZelleQr(Request $request): JsonResponse
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

            if ($zelleQrPath) {
                $this->fileStorage->delete($zelleQrPath);
                $settings['zelle_qr_path'] = null;
                $tenant->settings = $settings;
                $tenant->save();
            }

            return response()->json([
                'message' => 'Zelle QR code removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Remove Zelle QR failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove Zelle QR code'], 500);
        }
    }

    /**
     * Upload HOA/community logo
     */
    public function uploadHoaLogo(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'file' => 'required|file|max:2048|mimes:png,jpg,jpeg',
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
            $file = $validated['file'];
            $extension = $file->getClientOriginalExtension() ?: 'png';
            $filename = 'hoa-logo.' . $extension;
            $dir = 'tenant-settings/' . $tenant->id;
            $filePath = $dir . '/' . $filename;

            $stored = $this->fileStorage->store($filePath, file_get_contents($file->getRealPath()));
            if (!$stored) {
                return response()->json(['message' => 'Failed to store logo image'], 500);
            }

            $settings = $tenant->settings ?? [];
            $settings['logo_path'] = $filePath;
            $tenant->settings = $settings;
            $tenant->save();

            $logoUrl = null;
            if ($this->fileStorage->exists($filePath)) {
                try {
                    $logoUrl = $this->fileStorage->getUrl($filePath);
                } catch (\Throwable $e) {
                    Log::warning('Failed to get HOA logo URL after upload', ['path' => $filePath, 'error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'message' => 'HOA logo uploaded successfully',
                'logo_path' => $filePath,
                'logo_url' => $logoUrl,
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Upload HOA logo failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to upload HOA logo'], 500);
        }
    }

    /**
     * Remove HOA/community logo
     */
    public function removeHoaLogo(Request $request): JsonResponse
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
            $logoPath = $settings['logo_path'] ?? null;

            if ($logoPath) {
                $this->fileStorage->delete($logoPath);
                unset($settings['logo_path']);
                $tenant->settings = $settings;
                $tenant->save();
            }

            return response()->json([
                'message' => 'HOA logo removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Remove HOA logo failed: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove HOA logo'], 500);
        }
    }

    /**
     * Get a short-lived signed URL for the tenant HOA logo (same pattern as Invoice PDF).
     */
    public function getHoaLogoUrl(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->load('tenant');
        if (!$user->tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        $tenant = $user->tenant;
        $settings = $tenant->settings ?? [];
        $logoPath = $settings['logo_path'] ?? null;

        if (!$logoPath || !$this->fileStorage->exists($logoPath)) {
            return response()->json(['message' => 'HOA logo not found'], 404);
        }

        if ($this->fileStorage->isS3Disk()) {
            $signedUrl = $this->fileStorage->getTemporaryUrl(
                $logoPath,
                6,
                ['ResponseCacheControl' => 'no-store, max-age=0']
            );
        } else {
            $signedUrl = URL::temporarySignedRoute(
                'tenant.hoa-logo.signed',
                now()->addMinutes(6),
                ['tenant' => $tenant->id],
                true
            );
            if (str_starts_with($signedUrl, 'http://') && (config('app.env') === 'production' || str_contains($signedUrl, 'neibrpay.com'))) {
                $signedUrl = str_replace('http://', 'https://', $signedUrl);
            }
        }

        return response()->json([
            'data' => [
                'file_url' => $signedUrl,
            ],
        ]);
    }

    /**
     * Serve tenant HOA logo via signed URL (local storage; no auth header needed).
     */
    public function serveHoaLogoSigned(Tenant $tenant): Response
    {
        $settings = $tenant->settings ?? [];
        $logoPath = $settings['logo_path'] ?? null;
        if (!$logoPath || !$this->fileStorage->exists($logoPath)) {
            abort(404, 'HOA logo not found');
        }
        $content = $this->fileStorage->get($logoPath);
        if ($content === null) {
            abort(404, 'HOA logo not found');
        }
        $mime = $this->mimeFromPath($logoPath);
        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="hoa-logo"',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    /**
     * Get a short-lived signed URL for the tenant Zelle QR image (same pattern as Invoice PDF).
     */
    public function getZelleQrUrl(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->load('tenant');
        if (!$user->tenant) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        $tenant = $user->tenant;
        $settings = $tenant->settings ?? [];
        $zelleQrPath = $settings['zelle_qr_path'] ?? null;

        if (!$zelleQrPath || !$this->fileStorage->exists($zelleQrPath)) {
            return response()->json(['message' => 'Zelle QR code not found'], 404);
        }

        if ($this->fileStorage->isS3Disk()) {
            $signedUrl = $this->fileStorage->getTemporaryUrl(
                $zelleQrPath,
                6,
                ['ResponseCacheControl' => 'no-store, max-age=0']
            );
        } else {
            $signedUrl = URL::temporarySignedRoute(
                'tenant.zelle-qr.signed',
                now()->addMinutes(6),
                ['tenant' => $tenant->id],
                true
            );
            if (str_starts_with($signedUrl, 'http://') && (config('app.env') === 'production' || str_contains($signedUrl, 'neibrpay.com'))) {
                $signedUrl = str_replace('http://', 'https://', $signedUrl);
            }
        }

        return response()->json([
            'data' => [
                'file_url' => $signedUrl,
            ],
        ]);
    }

    /**
     * Serve tenant Zelle QR image via signed URL (local storage; no auth header needed).
     */
    public function serveZelleQrSigned(Tenant $tenant): Response
    {
        $settings = $tenant->settings ?? [];
        $zelleQrPath = $settings['zelle_qr_path'] ?? null;
        if (!$zelleQrPath || !$this->fileStorage->exists($zelleQrPath)) {
            abort(404, 'Zelle QR code not found');
        }
        $content = $this->fileStorage->get($zelleQrPath);
        if ($content === null) {
            abort(404, 'Zelle QR code not found');
        }
        $mime = $this->mimeFromPath($zelleQrPath);
        return response($content, 200, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="zelle-qr"',
            'Cache-Control' => 'no-store, max-age=0',
        ]);
    }

    private function mimeFromPath(string $path): string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}

