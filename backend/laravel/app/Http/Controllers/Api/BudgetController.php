<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BudgetAuditLog;
use App\Models\BudgetCategory;
use App\Models\BudgetEntry;
use App\Models\Charge;
use App\Models\Expense;
use App\Models\InvoicePayment;
use App\Models\InvoiceUnit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BudgetController extends Controller
{
    /**
     * Get all budget categories.
     */
    public function getCategories(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $type = $request->get('type'); // 'income' or 'expense'
        
        $query = BudgetCategory::forTenant($user->tenant_id)
            ->with(['creator:id,name,email'])
            ->ordered();

        if ($type) {
            $query->byType($type);
        }

        $categories = $query->get();

        return response()->json([
            'data' => $categories,
        ]);
    }

    /**
     * Create a new budget category (admin only).
     */
    public function createCategory(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->isResident()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'display_order' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $maxOrder = BudgetCategory::forTenant($user->tenant_id)
            ->byType($request->type)
            ->max('display_order') ?? 0;

        $category = BudgetCategory::create([
            'tenant_id' => $user->tenant_id,
            'name' => $request->name,
            'type' => $request->type,
            'display_order' => $request->display_order ?? ($maxOrder + 1),
            'created_by' => $user->id,
        ]);

        $category->load(['creator:id,name,email']);

        // Log audit
        $this->logAudit(
            $user,
            $request->get('year', date('Y')),
            'create_category',
            'category',
            $category->id,
            null,
            ['name' => $category->name, 'type' => $category->type],
            "Created budget category: {$category->name}"
        );

        return response()->json([
            'data' => $category,
            'message' => 'Budget category created successfully',
        ], 201);
    }

    /**
     * Update a budget category (admin only).
     */
    public function updateCategory(Request $request, BudgetCategory $category): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->isResident()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($category->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'display_order' => 'sometimes|nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldValues = [
            'name' => $category->name,
            'display_order' => $category->display_order,
        ];

        $category->update($request->only(['name', 'display_order']));
        $category->load(['creator:id,name,email']);

        // Log audit
        $this->logAudit(
            $user,
            $request->get('year', date('Y')),
            'update_category',
            'category',
            $category->id,
            $oldValues,
            ['name' => $category->name, 'display_order' => $category->display_order],
            "Updated budget category: {$category->name}"
        );

        return response()->json([
            'data' => $category,
            'message' => 'Budget category updated successfully',
        ]);
    }

    /**
     * Delete a budget category (admin only).
     */
    public function deleteCategory(Request $request, BudgetCategory $category): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->isResident()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($category->tenant_id !== $user->tenant_id) {
            return response()->json(['message' => 'Category not found'], 404);
        }

        $categoryName = $category->name;
        $categoryType = $category->type;
        $year = $request->get('year', date('Y'));

        $category->delete();

        // Log audit
        $this->logAudit(
            $user,
            $year,
            'delete_category',
            'category',
            $category->id,
            ['name' => $categoryName, 'type' => $categoryType],
            null,
            "Deleted budget category: {$categoryName}"
        );

        return response()->json([
            'message' => 'Budget category deleted successfully',
        ]);
    }

    /**
     * Get budget data for a specific year.
     */
    public function getBudget(Request $request, int $year): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Get all categories
        $incomeCategories = BudgetCategory::forTenant($user->tenant_id)
            ->byType('income')
            ->ordered()
            ->get();

        $expenseCategories = BudgetCategory::forTenant($user->tenant_id)
            ->byType('expense')
            ->ordered()
            ->get();

        // Get all entries for the year
        $entries = BudgetEntry::forTenant($user->tenant_id)
            ->forYear($year)
            ->with('category')
            ->get()
            ->keyBy(function ($entry) {
                return "{$entry->budget_category_id}_{$entry->month}";
            });

        // Calculate actual values
        $incomeActuals = $this->calculateIncomeActuals($user->tenant_id, $year);
        $expenseActuals = $this->calculateExpenseActuals($user->tenant_id, $year);

        // Build response structure
        $incomeData = $incomeCategories->map(function ($category) use ($entries, $incomeActuals, $year) {
            return $this->buildCategoryData($category, $entries, $incomeActuals, $year);
        });

        $expenseData = $expenseCategories->map(function ($category) use ($entries, $expenseActuals, $year) {
            return $this->buildCategoryData($category, $entries, $expenseActuals, $year);
        });

        return response()->json([
            'data' => [
                'year' => $year,
                'income' => $incomeData,
                'expense' => $expenseData,
            ],
        ]);
    }

    /**
     * Update budget entries (admin only).
     */
    public function updateEntries(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->isResident()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validator = Validator::make($request->all(), [
            'entries' => 'required|array',
            'entries.*.budget_category_id' => 'required|exists:budget_categories,id',
            'entries.*.year' => 'required|integer|min:2000|max:2100',
            'entries.*.month' => 'required|integer|min:1|max:12',
            'entries.*.forecast_amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $year = $request->get('year', date('Y'));
        $updatedEntries = [];

        DB::transaction(function () use ($user, $request, &$updatedEntries, $year) {
            foreach ($request->entries as $entryData) {
                // Verify category belongs to tenant
                $category = BudgetCategory::forTenant($user->tenant_id)
                    ->find($entryData['budget_category_id']);

                if (!$category) {
                    continue;
                }

                $existingEntry = BudgetEntry::where([
                    'tenant_id' => $user->tenant_id,
                    'budget_category_id' => $entryData['budget_category_id'],
                    'year' => $entryData['year'],
                    'month' => $entryData['month'],
                ])->first();

                $oldValue = $existingEntry ? $existingEntry->forecast_amount : 0;

                $entry = BudgetEntry::updateOrCreate(
                    [
                        'tenant_id' => $user->tenant_id,
                        'budget_category_id' => $entryData['budget_category_id'],
                        'year' => $entryData['year'],
                        'month' => $entryData['month'],
                    ],
                    [
                        'forecast_amount' => $entryData['forecast_amount'],
                    ]
                );

                // Log audit if value changed
                if ($oldValue != $entry->forecast_amount) {
                    $this->logAudit(
                        $user,
                        $year,
                        'update_forecast',
                        'entry',
                        $entry->id,
                        ['forecast_amount' => $oldValue, 'category' => $category->name, 'month' => $entryData['month']],
                        ['forecast_amount' => $entry->forecast_amount, 'category' => $category->name, 'month' => $entryData['month']],
                        "Updated forecast for {$category->name} - " . $this->getMonthName($entryData['month']) . ": $" . number_format($oldValue, 2) . " → $" . number_format($entry->forecast_amount, 2)
                    );
                }

                $updatedEntries[] = $entry;
            }
        });

        return response()->json([
            'data' => $updatedEntries,
            'message' => 'Budget entries updated successfully',
        ]);
    }

    /**
     * Copy budget from one year to another (admin only).
     */
    public function copyBudget(Request $request, int $fromYear, int $toYear): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        if ($user->isResident()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Get all entries from source year
        $sourceEntries = BudgetEntry::forTenant($user->tenant_id)
            ->forYear($fromYear)
            ->get();

        $copiedEntries = [];

        DB::transaction(function () use ($user, $sourceEntries, $toYear, &$copiedEntries) {
            foreach ($sourceEntries as $sourceEntry) {
                $newEntry = BudgetEntry::updateOrCreate(
                    [
                        'tenant_id' => $user->tenant_id,
                        'budget_category_id' => $sourceEntry->budget_category_id,
                        'year' => $toYear,
                        'month' => $sourceEntry->month,
                    ],
                    [
                        'forecast_amount' => $sourceEntry->forecast_amount,
                    ]
                );

                $copiedEntries[] = $newEntry;
            }
        });

        // Log audit
        $this->logAudit(
            $user,
            $toYear,
            'copy_budget',
            'entry',
            null,
            ['source_year' => $fromYear],
            ['target_year' => $toYear],
            "Copied budget from {$fromYear} to {$toYear}"
        );

        return response()->json([
            'data' => $copiedEntries,
            'message' => "Budget copied from {$fromYear} to {$toYear} successfully",
        ]);
    }

    /**
     * Get audit logs for a specific year.
     */
    public function getAuditLogs(Request $request, int $year): JsonResponse
    {
        $user = $request->user();
        
        if (!$user || !$user->tenant_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $logs = BudgetAuditLog::forTenant($user->tenant_id)
            ->forYear($year)
            ->with(['user:id,name,email'])
            ->recent()
            ->get();

        return response()->json([
            'data' => $logs,
        ]);
    }

    /**
     * Calculate actual income values by category and month.
     */
    private function calculateIncomeActuals(int $tenantId, int $year): array
    {
        // Get all approved invoice payments for the year
        $payments = InvoicePayment::whereHas('invoiceUnit', function ($query) use ($tenantId) {
                $query->where('tenant_id', $tenantId);
            })
            ->whereYear('payment_date', $year)
            ->where('status', 'approved')
            ->with('invoiceUnit')
            ->get();

        $actuals = [];

        foreach ($payments as $payment) {
            if (!$payment->invoiceUnit) {
                continue;
            }

            $month = (int) $payment->payment_date->format('n');
            
            // Get charge IDs from invoice items
            $items = $payment->invoiceUnit->items ?? [];
            if (empty($items) || !is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $chargeId = $item['charge_id'] ?? null;
                if (!$chargeId) {
                    continue;
                }

                // Get charge and its budget category
                $charge = Charge::find($chargeId);
                if (!$charge || !$charge->budget_category_id) {
                    continue;
                }

                $categoryId = $charge->budget_category_id;
                $key = "{$categoryId}_{$month}";
                
                if (!isset($actuals[$key])) {
                    $actuals[$key] = 0;
                }

                // Distribute payment amount proportionally if multiple items
                $itemAmount = $item['amount'] ?? 0;
                $totalItemsAmount = array_sum(array_column($items, 'amount'));
                $proportionalAmount = $totalItemsAmount > 0 
                    ? ($payment->amount * ($itemAmount / $totalItemsAmount))
                    : ($payment->amount / count($items));

                $actuals[$key] += $proportionalAmount;
            }
        }

        return $actuals;
    }

    /**
     * Calculate actual expense values by category and month.
     */
    private function calculateExpenseActuals(int $tenantId, int $year): array
    {
        $expenses = Expense::forTenant($tenantId)
            ->whereYear('paid_date', $year)
            ->whereNotNull('budget_category_id')
            ->whereNotNull('paid_date')
            ->where('status', 'paid')
            ->get();

        $actuals = [];

        foreach ($expenses as $expense) {
            $month = (int) $expense->paid_date->format('n');
            $categoryId = $expense->budget_category_id;
            $key = "{$categoryId}_{$month}";

            if (!isset($actuals[$key])) {
                $actuals[$key] = 0;
            }

            $actuals[$key] += $expense->paid_amount;
        }

        return $actuals;
    }

    /**
     * Build category data structure with forecast and actual values.
     */
    private function buildCategoryData($category, $entries, $actuals, int $year): array
    {
        $months = [];
        $totalForecast = 0;
        $totalActual = 0;

        for ($month = 1; $month <= 12; $month++) {
            $key = "{$category->id}_{$month}";
            $entryKey = "{$category->id}_{$month}";
            
            $forecast = $entries[$entryKey]->forecast_amount ?? 0;
            $actual = $actuals[$key] ?? 0;

            $months[$month] = [
                'forecast' => (float) $forecast,
                'actual' => (float) $actual,
            ];

            $totalForecast += $forecast;
            $totalActual += $actual;
        }

        return [
            'id' => $category->id,
            'name' => $category->name,
            'type' => $category->type,
            'display_order' => $category->display_order,
            'months' => $months,
            'total' => [
                'forecast' => $totalForecast,
                'actual' => $totalActual,
            ],
        ];
    }

    /**
     * Log an audit entry.
     */
    private function logAudit($user, int $year, string $action, string $entityType, ?int $entityId, ?array $oldValues, ?array $newValues, string $description): void
    {
        BudgetAuditLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'year' => $year,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'description' => $description,
        ]);
    }

    /**
     * Get month name from number.
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
        ];

        return $months[$month] ?? '';
    }
}

