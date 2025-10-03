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
            ->with('tenant');
            
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

        $unit->load('tenant');

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

        $unit->load('tenant');

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
        $unit->load('tenant');

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
        $unit->load('tenant');

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
}