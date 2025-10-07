<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnitsController extends Controller
{
    /**
     * Display a listing of units.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->get('firebase_user');
        $includeDeleted = $request->boolean('include_deleted', false);
        
        $query = Unit::forTenant($user->tenant_id)
            ->with(['tenant', 'owners']);
            
        if ($includeDeleted) {
            $query->withTrashed();
        }
        
        $units = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'data' => $units,
            'meta' => [
                'total' => $units->count(),
                'include_deleted' => $includeDeleted,
            ],
        ]);
    }

    /**
     * Get units with resident information for invoice creation.
     */
    public function forInvoices(Request $request): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        $units = Unit::forTenant($user->tenant_id)
            ->where('is_active', true)
            ->with(['owners' => function ($query) {
                $query->select('users.id', 'users.name', 'users.email');
            }])
            ->orderBy('title', 'asc')
            ->get()
            ->map(function ($unit) {
                // Get the first owner's name (assuming one owner per unit for now)
                $residentName = $unit->owners->first()?->name ?? 'No Resident';
                
                return [
                    'id' => $unit->id,
                    'title' => $unit->title,
                    'resident_name' => $residentName,
                    'address' => $unit->address,
                    'city' => $unit->city,
                    'state' => $unit->state,
                    'zip_code' => $unit->zip_code,
                    'starting_balance' => $unit->starting_balance,
                    'balance_as_of_date' => $unit->balance_as_of_date,
                ];
            });
        
        return response()->json([
            'data' => $units,
            'meta' => [
                'total' => $units->count(),
            ],
        ]);
    }

    /**
     * Store a newly created unit in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'required|string|size:2',
            'zip_code' => 'required|string|max:10',
            'starting_balance' => 'required|numeric|min:-999999.99|max:999999.99',
            'balance_as_of_date' => 'required|date',
        ]);

        $unit = Unit::create([
            ...$validated,
            'tenant_id' => $user->tenant_id,
            'is_active' => true,
        ]);

        $unit->load(['tenant', 'owners']);

        return response()->json([
            'data' => $unit,
            'message' => 'Unit created successfully',
        ], 201);
    }

    /**
     * Display the specified unit.
     */
    public function show(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Ensure the unit belongs to the user's tenant
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->load(['tenant', 'owners', 'documents.uploader']);

        return response()->json([
            'data' => $unit,
        ]);
    }

    /**
     * Update the specified unit in storage.
     */
    public function update(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Ensure the unit belongs to the user's tenant
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|size:2',
            'zip_code' => 'sometimes|string|max:10',
            'starting_balance' => 'sometimes|numeric|min:-999999.99|max:999999.99',
            'balance_as_of_date' => 'sometimes|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $unit->update($validated);
        $unit->load(['tenant', 'owners', 'documents.uploader']);

        return response()->json([
            'data' => $unit,
            'message' => 'Unit updated successfully',
        ]);
    }

    /**
     * Remove the specified unit from storage (soft delete).
     */
    public function destroy(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Ensure the unit belongs to the user's tenant
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->delete();

        return response()->json([
            'message' => 'Unit deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted unit.
     */
    public function restore(Request $request, int $id): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        $unit = Unit::withTrashed()
            ->where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        if (!$unit->trashed()) {
            return response()->json(['message' => 'Unit is not deleted'], 400);
        }

        $unit->restore();
        $unit->load(['tenant', 'owners']);

        return response()->json([
            'data' => $unit,
            'message' => 'Unit restored successfully',
        ]);
    }

    /**
     * Permanently delete a unit (admin only).
     */
    public function forceDelete(Request $request, int $id): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        $unit = Unit::withTrashed()
            ->where('id', $id)
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $unit->forceDelete();

        return response()->json([
            'message' => 'Unit permanently deleted',
        ]);
    }

    /**
     * Add owners to a unit.
     */
    public function addOwners(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Ensure the unit belongs to the user's tenant
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $validated = $request->validate([
            'owner_ids' => 'required|array',
            'owner_ids.*' => 'integer|exists:users,id',
        ]);

        // Ensure all owners belong to the same tenant
        $ownerIds = $validated['owner_ids'];
        $validOwners = \App\Models\User::whereIn('id', $ownerIds)
            ->where('tenant_id', $user->tenant_id)
            ->pluck('id')
            ->toArray();

        if (count($validOwners) !== count($ownerIds)) {
            return response()->json(['message' => 'Some owners do not belong to your tenant'], 400);
        }

        // Sync owners (this will add new ones and keep existing ones)
        $unit->owners()->syncWithoutDetaching($validOwners);
        $unit->load(['tenant', 'owners']);

        return response()->json([
            'data' => $unit,
            'message' => 'Owners added successfully',
        ]);
    }

    /**
     * Remove owners from a unit.
     */
    public function removeOwners(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Ensure the unit belongs to the user's tenant
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $validated = $request->validate([
            'owner_ids' => 'required|array',
            'owner_ids.*' => 'integer|exists:users,id',
        ]);

        $unit->owners()->detach($validated['owner_ids']);
        $unit->load(['tenant', 'owners']);

        return response()->json([
            'data' => $unit,
            'message' => 'Owners removed successfully',
        ]);
    }

    /**
     * Sync owners for a unit (replace all owners).
     */
    public function syncOwners(Request $request, Unit $unit): JsonResponse
    {
        $user = $request->get('firebase_user');
        
        // Ensure the unit belongs to the user's tenant
        if ($unit->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        $validated = $request->validate([
            'owner_ids' => 'array',
            'owner_ids.*' => 'integer|exists:users,id',
        ]);

        $ownerIds = $validated['owner_ids'] ?? [];
        
        // Ensure all owners belong to the same tenant
        if (!empty($ownerIds)) {
            $validOwners = \App\Models\User::whereIn('id', $ownerIds)
                ->where('tenant_id', $user->tenant_id)
                ->pluck('id')
                ->toArray();

            if (count($validOwners) !== count($ownerIds)) {
                return response()->json(['message' => 'Some owners do not belong to your tenant'], 400);
            }
        }

        $unit->owners()->sync($ownerIds);
        $unit->load(['tenant', 'owners']);

        return response()->json([
            'data' => $unit,
            'message' => 'Owners synced successfully',
        ]);
    }
}