<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreVendorRequest;
use App\Http\Requests\UpdateVendorRequest;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        // If user is a resident, return empty array (residents cannot see vendors)
        if ($user->isResident()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'category' => $request->category,
                    'include_deleted' => false,
                ],
            ]);
        }
        
        $query = Vendor::forTenant($user->tenant_id)
            ->with(['creator:id,name,email']);

        // Apply filters
        if ($request->has('category') && $request->category !== '') {
            $query->byCategory($request->category);
        }

        if ($request->has('include_deleted') && $request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        // Search by name
        if ($request->has('search') && $request->search !== '') {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $vendors = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $vendors,
            'meta' => [
                'total' => $vendors->count(),
                'category' => $request->category,
                'include_deleted' => $request->boolean('include_deleted'),
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVendorRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $vendor = Vendor::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
            'description' => $request->description,
            'category' => $request->category,
            'ein' => $request->ein,
            'street_address' => $request->street_address,
            'city' => $request->city,
            'state' => $request->state,
            'zip_code' => $request->zip_code,
            'website' => $request->website,
            'notes' => $request->notes,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'created_by' => $user->id,
        ]);

        $vendor->load(['creator:id,name,email']);

        return response()->json([
            'data' => $vendor,
            'message' => 'Vendor created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($vendor->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor->load(['creator:id,name,email']);

        return response()->json([
            'data' => $vendor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($vendor->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor->update($request->validated());
        $vendor->load(['creator:id,name,email']);

        return response()->json([
            'data' => $vendor,
            'message' => 'Vendor updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($vendor->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        $vendor->delete();

        return response()->json([
            'message' => 'Vendor deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted vendor.
     */
    public function restore(Vendor $vendor): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($vendor->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Vendor not found'], 404);
        }

        if (!$vendor->trashed()) {
            return response()->json([
                'message' => 'Vendor is not deleted',
            ], 400);
        }

        $vendor->restore();
        $vendor->load(['creator:id,name,email']);

        return response()->json([
            'data' => $vendor,
            'message' => 'Vendor restored successfully',
        ]);
    }
}