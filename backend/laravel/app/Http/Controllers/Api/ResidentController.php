<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ResidentRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    /**
     * Display a listing of residents.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->get('firebase_user');
        $includeDeleted = $request->boolean('include_deleted', false);
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
        $resident = User::forTenant($user->tenant_id)
            ->byRole('resident')
            ->findOrFail($id);
        
        // Get all units for the tenant
        $allUnits = \App\Models\Unit::forTenant($user->tenant_id)
            ->where('is_active', true)
            ->orderBy('title', 'asc')
            ->get();
        
        // Get units already owned by this resident
        $ownedUnitIds = $resident->ownedUnits()->pluck('units.id')->toArray();
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
        $user = $request->get('firebase_user');
        
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
}
