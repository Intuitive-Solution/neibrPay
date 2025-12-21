<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $includeDeleted = $request->boolean('include_deleted', false);
        $vendorId = $request->get('vendor_id');
        $budgetCategoryId = $request->get('budget_category_id');
        $status = $request->get('status');
        $search = $request->get('search');
        
        $query = Expense::forTenant($user->tenant_id)
            ->with(['vendor', 'creator', 'attachments', 'budgetCategory']);
            
        if ($includeDeleted) {
            $query->withTrashed();
        }
        
        if ($vendorId) {
            $query->byVendor($vendorId);
        }
        
        if ($budgetCategoryId) {
            $query->where('budget_category_id', $budgetCategoryId);
        }
        
        if ($status) {
            $query->byStatus($status);
        }
        
        if ($search) {
            $query->search($search);
        }
        
        // Residents can view all expenses (read-only) - no additional filtering needed
        // All expenses are visible to residents regardless of status
        
        $expenses = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'data' => $expenses,
            'meta' => [
                'total' => $expenses->count(),
                'include_deleted' => $includeDeleted,
                'filters' => [
                    'vendor_id' => $vendorId,
                    'budget_category_id' => $budgetCategoryId,
                    'status' => $status,
                    'search' => $search,
                ],
            ],
        ]);
    }

    /**
     * Store a newly created expense in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'vendor_id' => 'required|integer|exists:vendors,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'invoice_due_date' => 'required|date|after_or_equal:invoice_date',
            'invoice_amount' => 'required|numeric|min:0',
            'budget_category_id' => 'nullable|integer|exists:budget_categories,id',
            'note' => 'nullable|string',
            'status' => 'required|in:unpaid,partial,paid',
            'payment_details' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,check,credit_card,bank_transfer,other',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
        ]);

        // Ensure the vendor belongs to the user's tenant
        $vendor = Vendor::where('id', $validated['vendor_id'])
            ->where('tenant_id', $user->tenant_id)
            ->first();
        
        if (!$vendor) {
            return response()->json(['message' => 'Vendor does not belong to your tenant'], 400);
        }

        // Ensure the budget category belongs to the user's tenant if provided
        if (isset($validated['budget_category_id'])) {
            $budgetCategory = \App\Models\BudgetCategory::where('id', $validated['budget_category_id'])
                ->where('tenant_id', $user->tenant_id)
                ->where('type', 'expense')
                ->first();
            
            if (!$budgetCategory) {
                return response()->json(['message' => 'Budget category does not belong to your tenant or is not an expense category'], 400);
            }
        }

        // Validate payment fields based on status
        if ($validated['status'] === 'partial') {
            if (!$validated['paid_amount'] || $validated['paid_amount'] <= 0) {
                return response()->json(['message' => 'Paid amount is required for partial payments'], 400);
            }
            if ($validated['paid_amount'] >= $validated['invoice_amount']) {
                return response()->json(['message' => 'Paid amount must be less than invoice amount for partial payments'], 400);
            }
        }

        if (in_array($validated['status'], ['partial', 'paid'])) {
            if (!$validated['payment_method']) {
                return response()->json(['message' => 'Payment method is required for paid or partial payments'], 400);
            }
            if (!$validated['paid_date']) {
                return response()->json(['message' => 'Paid date is required for paid or partial payments'], 400);
            }
        }

        if ($validated['status'] === 'paid') {
            $validated['paid_amount'] = $validated['invoice_amount'];
        }

        $expense = Expense::create([
            ...$validated,
            'tenant_id' => $user->tenant_id,
            'created_by' => $user->id,
        ]);

        $expense->load(['vendor', 'creator', 'attachments']);

        return response()->json([
            'data' => $expense,
            'message' => 'Expense created successfully',
        ], 201);
    }

    /**
     * Display the specified expense.
     */
    public function show(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $expense->load([
            'vendor',
            'creator',
            'attachments.uploader',
            'budgetCategory',
        ]);

        return response()->json([
            'data' => $expense,
        ]);
    }

    /**
     * Update the specified expense in storage.
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $validated = $request->validate([
            'vendor_id' => 'sometimes|integer|exists:vendors,id',
            'invoice_number' => 'sometimes|string|max:255',
            'invoice_date' => 'sometimes|date',
            'invoice_due_date' => 'sometimes|date|after_or_equal:invoice_date',
            'invoice_amount' => 'sometimes|numeric|min:0',
            'budget_category_id' => 'nullable|integer|exists:budget_categories,id',
            'note' => 'nullable|string',
            'status' => 'sometimes|in:unpaid,partial,paid',
            'payment_details' => 'nullable|string',
            'payment_method' => 'nullable|in:cash,check,credit_card,bank_transfer,other',
            'paid_amount' => 'nullable|numeric|min:0',
            'paid_date' => 'nullable|date',
        ]);

        // Ensure the vendor belongs to the user's tenant if provided
        if (isset($validated['vendor_id'])) {
            $vendor = Vendor::where('id', $validated['vendor_id'])
                ->where('tenant_id', $user->tenant_id)
                ->first();
            
            if (!$vendor) {
                return response()->json(['message' => 'Vendor does not belong to your tenant'], 400);
            }
        }

        // Ensure the budget category belongs to the user's tenant if provided
        if (isset($validated['budget_category_id'])) {
            $budgetCategory = \App\Models\BudgetCategory::where('id', $validated['budget_category_id'])
                ->where('tenant_id', $user->tenant_id)
                ->where('type', 'expense')
                ->first();
            
            if (!$budgetCategory) {
                return response()->json(['message' => 'Budget category does not belong to your tenant or is not an expense category'], 400);
            }
        }

        // Validate payment fields based on status
        if (isset($validated['status'])) {
            if ($validated['status'] === 'partial') {
                $paidAmount = $validated['paid_amount'] ?? $expense->paid_amount;
                $invoiceAmount = $validated['invoice_amount'] ?? $expense->invoice_amount;
                
                if (!$paidAmount || $paidAmount <= 0) {
                    return response()->json(['message' => 'Paid amount is required for partial payments'], 400);
                }
                if ($paidAmount >= $invoiceAmount) {
                    return response()->json(['message' => 'Paid amount must be less than invoice amount for partial payments'], 400);
                }
            }

            if (in_array($validated['status'], ['partial', 'paid'])) {
                if (!isset($validated['payment_method']) && !$expense->payment_method) {
                    return response()->json(['message' => 'Payment method is required for paid or partial payments'], 400);
                }
                if (!isset($validated['paid_date']) && !$expense->paid_date) {
                    return response()->json(['message' => 'Paid date is required for paid or partial payments'], 400);
                }
            }

            if ($validated['status'] === 'paid') {
                $validated['paid_amount'] = $validated['invoice_amount'] ?? $expense->invoice_amount;
            }
        }

        $expense->update($validated);
        $expense->load(['vendor', 'creator', 'attachments', 'budgetCategory']);

        return response()->json([
            'data' => $expense,
            'message' => 'Expense updated successfully',
        ]);
    }

    /**
     * Remove the specified expense from storage.
     */
    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully',
        ]);
    }

    /**
     * Restore the specified expense.
     */
    public function restore(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();
        
        // Ensure the expense belongs to the user's tenant
        if ($expense->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Expense not found'], 404);
        }

        $expense->restore();
        $expense->load(['vendor', 'creator', 'attachments', 'budgetCategory']);

        return response()->json([
            'data' => $expense,
            'message' => 'Expense restored successfully',
        ]);
    }
}