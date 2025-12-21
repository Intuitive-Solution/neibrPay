<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChargeRequest;
use App\Http\Requests\UpdateChargeRequest;
use App\Models\Charge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChargeController extends Controller
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
        
        // If user is a resident, return empty array (residents cannot see charges)
        if ($user->isResident()) {
            return response()->json([
                'data' => [],
                'meta' => [
                    'total' => 0,
                    'budget_category_id' => $request->budget_category_id,
                    'include_deleted' => false,
                ],
            ]);
        }
        
        $query = Charge::forTenant($user->tenant_id)
            ->with(['creator:id,name,email', 'budgetCategory:id,name,type']);

        // Apply filters
        if ($request->has('budget_category_id') && $request->budget_category_id !== '') {
            $query->byBudgetCategory($request->budget_category_id);
        }

        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('include_deleted') && $request->boolean('include_deleted')) {
            $query->withTrashed();
        }

        // Search by title
        if ($request->has('search') && $request->search !== '') {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        $charges = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $charges,
            'meta' => [
                'total' => $charges->count(),
                'budget_category_id' => $request->budget_category_id,
                'is_active' => $request->is_active,
                'include_deleted' => $request->boolean('include_deleted'),
                'search' => $request->search,
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreChargeRequest $request): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $charge = Charge::create([
            'tenant_id' => $user->tenant_id,
            'title' => $request->title,
            'description' => $request->description,
            'amount' => $request->amount,
            'budget_category_id' => $request->budget_category_id,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => $user->id,
        ]);

        $charge->load(['creator:id,name,email', 'budgetCategory:id,name,type']);

        return response()->json([
            'data' => $charge,
            'message' => 'Charge created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Charge $charge): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($charge->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Charge not found'], 404);
        }

        $charge->load(['creator:id,name,email', 'budgetCategory:id,name,type']);

        return response()->json([
            'data' => $charge,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateChargeRequest $request, Charge $charge): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($charge->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Charge not found'], 404);
        }

        $charge->update($request->validated());
        $charge->load(['creator:id,name,email', 'budgetCategory:id,name,type']);

        return response()->json([
            'data' => $charge,
            'message' => 'Charge updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Charge $charge): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($charge->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Charge not found'], 404);
        }

        $charge->delete();

        return response()->json([
            'message' => 'Charge deleted successfully',
        ]);
    }

    /**
     * Restore a soft-deleted charge.
     */
    public function restore(Charge $charge): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Ensure tenant isolation
        if ($charge->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Charge not found'], 404);
        }

        if (!$charge->trashed()) {
            return response()->json([
                'message' => 'Charge is not deleted',
            ], 400);
        }

        $charge->restore();
        $charge->load(['creator:id,name,email', 'budgetCategory:id,name,type']);

        return response()->json([
            'data' => $charge,
            'message' => 'Charge restored successfully',
        ]);
    }
}
