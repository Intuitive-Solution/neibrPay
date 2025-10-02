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
        
        return response()->json([
            'data' => $residents,
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
            'role' => 'resident',
            'tenant_id' => $user->tenant_id,
            'is_active' => true,
        ]);
        
        $resident->load('tenant');
        
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
        
        return response()->json(['data' => $resident]);
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
        ]);
        
        $resident->load('tenant');
        
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
