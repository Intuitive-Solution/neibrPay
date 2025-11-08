<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ResidentController extends Controller
{

    /**
     * Display a listing of residents.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $includeDeleted = $request->boolean('include_deleted', false);
        
        // If user is a resident, return empty array (residents cannot see other residents)
        if ($user->isResident()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'include_deleted' => $includeDeleted,
                ]
            ]);
        }
        
        $query = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->with('tenant');
            
        if ($includeDeleted) {
            $query->withTrashed();
        }
        
        $residents = $query->orderBy('created_at', 'desc')->get();
        
        // Transform phone_number to phone for frontend compatibility
        $transformedResidents = $residents->map(function ($resident) {
            $resident->phone = $resident->phone_number;
            unset($resident->phone_number);
            return $resident;
        });

        return response()->json([
            'data' => $transformedResidents,
            'meta' => [
                'total' => $residents->count(),
                'include_deleted' => $includeDeleted,
            ]
        ]);
    }

    /**
     * Store a newly created resident.
     */
    public function store(ResidentRequest $request): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'type' => $request->type ?? 'owner',
            'member_role' => $request->member_role ?? 'member',
            'role' => 'resident',
            'tenant_id' => $user->tenant_id,
            'is_active' => true,
        ]);
        
        $resident->load('tenant');
        
        // Transform phone_number to phone for frontend compatibility
        $resident->phone = $resident->phone_number;
        unset($resident->phone_number);
        
        return response()->json([
            'data' => $resident,
            'message' => 'Resident created successfully'
        ], 201);
    }

    /**
     * Display the specified resident.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->withTrashed()
            ->with('tenant')
            ->findOrFail($id);
        
        // Transform phone_number to phone for frontend compatibility
        $resident->phone = $resident->phone_number;
        unset($resident->phone_number);
        
        return response()->json(['data' => $resident]);
    }

    /**
     * Get units owned by the specified resident.
     */
    public function units(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        $units = $resident->ownedUnits()
            ->with('tenant')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'data' => $units,
            'meta' => [
                'total' => $units->count(),
                'resident_id' => $resident->id,
            ]
        ]);
    }

    /**
     * Remove a unit from the specified resident.
     */
    public function removeUnit(Request $request, string $id, string $unitId): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        // Check if the unit exists and belongs to the same tenant
        $unit = \App\Models\Unit::forTenant($user->tenant_id)->findOrFail($unitId);
        
        // Remove the relationship
        $resident->ownedUnits()->detach($unitId);
        
        return response()->json([
            'message' => 'Unit removed from resident successfully',
            'data' => [
                'resident_id' => $resident->id,
                'unit_id' => $unitId,
            ]
        ]);
    }

    /**
     * Get units available to be assigned to the specified resident.
     */
    public function availableUnits(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        // Get all units for the tenant
        $allUnits = \App\Models\Unit::forTenant($user->tenant_id)
            ->where('is_active', true)
            ->orderBy('title', 'asc')
            ->get();
        
        // Get units already owned by this resident
        $ownedUnitIds = $resident->ownedUnits()->get()->pluck('id')->toArray();
        
        // Filter out units already owned by this resident
        $availableUnits = $allUnits->reject(function ($unit) use ($ownedUnitIds) {
            return in_array($unit->id, $ownedUnitIds);
        });
        
        return response()->json([
            'data' => $availableUnits->values(),
            'meta' => [
                'total' => $availableUnits->count(),
                'resident_id' => $resident->id,
                'owned_units_count' => count($ownedUnitIds),
            ]
        ]);
    }

    /**
     * Add units to the specified resident.
     */
    public function addUnits(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        // Validate the request
        $request->validate([
            'unit_ids' => 'required|array|min:1',
            'unit_ids.*' => 'integer|exists:units,id'
        ]);
        
        $unitIds = $request->input('unit_ids');
        
        // Verify all units belong to the same tenant and are active
        $units = \App\Models\Unit::forTenant($user->tenant_id)
            ->where('is_active', true)
            ->whereIn('id', $unitIds)
            ->get();
        
        if ($units->count() !== count($unitIds)) {
            return response()->json([
                'message' => 'Some units are invalid or not available',
                'errors' => ['unit_ids' => ['One or more units are invalid or not available']]
            ], 422);
        }
        
        // Check if any units are already owned by this resident
        $existingUnitIds = $resident->ownedUnits()->whereIn('units.id', $unitIds)->pluck('units.id')->toArray();
        
        if (!empty($existingUnitIds)) {
            return response()->json([
                'message' => 'Some units are already owned by this resident',
                'errors' => ['unit_ids' => ['One or more units are already owned by this resident']]
            ], 422);
        }
        
        // Add the relationships
        $resident->ownedUnits()->attach($unitIds);
        
        // Get the newly added units with their details
        $addedUnits = $resident->ownedUnits()->whereIn('units.id', $unitIds)->get();
        
        return response()->json([
            'message' => 'Units added to resident successfully',
            'data' => [
                'resident_id' => $resident->id,
                'added_units' => $addedUnits,
                'added_count' => count($unitIds),
            ]
        ], 201);
    }

    /**
     * Update the specified resident.
     */
    public function update(ResidentRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        $resident->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone_number' => $request->phone,
            'type' => $request->type,
            'member_role' => $request->member_role,
        ]);
        
        $resident->load('tenant');
        
        // Transform phone_number to phone for frontend compatibility
        $resident->phone = $resident->phone_number;
        unset($resident->phone_number);
        
        return response()->json([
            'data' => $resident,
            'message' => 'Resident updated successfully'
        ]);
    }

    /**
     * Soft delete the specified resident.
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        $resident->delete();
        
        return response()->json([
            'message' => 'Resident deleted successfully'
        ]);
    }

    /**
     * Restore a soft-deleted resident.
     */
    public function restore(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->onlyTrashed()
            ->findOrFail($id);
        
        $resident->restore();
        $resident->load('tenant');
        
        // Transform phone_number to phone for frontend compatibility
        $resident->phone = $resident->phone_number;
        unset($resident->phone_number);
        
        return response()->json([
            'data' => $resident,
            'message' => 'Resident restored successfully'
        ]);
    }

    /**
     * Permanently delete a resident (admin only).
     */
    public function forceDelete(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can permanently delete
        if (!$user->isAdmin()) {
            return response()->json([
                'message' => 'Unauthorized. Only admins can permanently delete residents.'
            ], 403);
        }
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->onlyTrashed()
            ->findOrFail($id);
        
        $resident->forceDelete();
        
        return response()->json([
            'message' => 'Resident permanently deleted'
        ]);
    }

    /**
     * Send invite email to a resident with welcome message and login link.
     */
    public function sendInvite(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        // Find the resident and ensure they belong to the user's tenant
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        // Validate that resident is active
        if (!$resident->is_active || $resident->deleted_at) {
            return response()->json([
                'message' => 'Cannot send invite email to inactive or deleted residents'
            ], 400);
        }
        
        // Load tenant information
        $resident->load('tenant');
        $tenant = $resident->tenant;
        
        if (!$tenant) {
            return response()->json([
                'message' => 'Resident tenant not found'
            ], 404);
        }
        
        // Check if n8n webhook URL is configured
        $n8nWebhookUrl = config('n8n.webhook_url');
        if (!$n8nWebhookUrl) {
            Log::warning('N8N_WEBHOOK_URL not configured. Invite email webhook not sent.');
            return response()->json([
                'message' => 'Email service not configured. Invite email not sent.',
            ], 500);
        }
        
        // Get webhook secret token for authentication
        $webhookSecret = config('n8n.webhook_secret');
        if (!$webhookSecret) {
            Log::warning('N8N_WEBHOOK_SECRET not configured. Webhook may be unsecured.');
        }
        
        try {
            // Generate magic link token (expires in 7 days)
            $magicLinkToken = Str::random(64);
            Cache::put("magic_link:{$magicLinkToken}", [
                'email' => $resident->email,
                'resident_id' => $resident->id,
                'created_at' => now(),
            ], now()->addDays(7));
            
            // Build magic link URL with email parameter for fallback
            $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
            $magicLink = rtrim($frontendUrl, '/') . '/magic-link?token=' . $magicLinkToken . '&email=' . urlencode($resident->email);
            
            // Prepare n8n webhook payload
            $payload = [
                'type' => 'resident_invite',
                'recipient' => [
                    'email' => $resident->email,
                    'name' => $resident->name ?? $resident->email,
                ],
                'magic_link' => $magicLink,
                'login_link' => $magicLink, // Keep for backward compatibility
                'tenant_name' => $tenant->name ?? 'HOA',
                'resident_name' => $resident->name ?? $resident->email,
            ];
            
            Log::info('Sending invite email webhook request', [
                'url' => $n8nWebhookUrl,
                'has_secret' => !empty($webhookSecret),
                'recipient_email' => $resident->email,
            ]);
            
            // Prepare headers for webhook request
            $headers = [
                'Content-Type' => 'application/json',
            ];
            
            // Add authentication token if configured
            if ($webhookSecret) {
                $headers['X-Webhook-Token'] = $webhookSecret;
            }
            
            // Send HTTP POST request to n8n webhook
            try {
                $response = Http::timeout(10)
                    ->retry(2, 100)
                    ->withHeaders($headers)
                    ->post($n8nWebhookUrl, $payload);
                
                if ($response->successful()) {
                    Log::info('Invite email webhook sent successfully', [
                        'status' => $response->status(),
                        'resident_id' => $resident->id,
                        'resident_email' => $resident->email,
                    ]);
                    
                    return response()->json([
                        'message' => 'Invite email sent successfully',
                        'data' => [
                            'resident_id' => $resident->id,
                            'email' => $resident->email,
                        ]
                    ]);
                } else {
                    $errorMessage = 'n8n webhook returned non-success status';
                    $responseBody = $response->body();
                    
                    if ($response->status() === 404) {
                        $errorMessage = 'n8n webhook not found - workflow may not be activated';
                        Log::warning($errorMessage, [
                            'status' => $response->status(),
                            'url' => $n8nWebhookUrl,
                        ]);
                    } else {
                        Log::warning('Invite email webhook returned non-success status', [
                            'status' => $response->status(),
                            'response_body' => substr($responseBody, 0, 500),
                            'url' => $n8nWebhookUrl,
                        ]);
                    }
                    
                    return response()->json([
                        'message' => 'Failed to send invite email',
                        'error' => $errorMessage
                    ], 500);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send invite email webhook: ' . $e->getMessage(), [
                    'resident_id' => $resident->id,
                    'resident_email' => $resident->email,
                    'error' => $e->getMessage(),
                ]);
                
                return response()->json([
                    'message' => 'Failed to send invite email',
                    'error' => $e->getMessage()
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error in sendInvite: ' . $e->getMessage(), [
                'resident_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return response()->json([
                'message' => 'Failed to send invite email',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
