<template>
  <div class="space-y-6">
    <!-- Controls Section -->
    <div class="card-modern bg-white rounded-lg shadow-sm">
      <div class="px-6 py-4 border-b border-gray-200">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <div class="flex items-center gap-3">
            <!-- Year Selector -->
            <select
              v-model="selectedYear"
              class="input-field text-sm"
              @change="handleYearChange"
            >
              <option v-for="year in availableYears" :key="year" :value="year">
                {{ year }}
              </option>
            </select>

            <!-- Admin-only buttons -->
            <template v-if="!isResident">
              <button
                @click="showCopyModal = true"
                class="btn-secondary btn-sm whitespace-nowrap"
                :disabled="isCopying"
              >
                <svg
                  class="w-4 h-4 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                  />
                </svg>
                Copy Budget
              </button>
              <button
                @click="showCategoryManager = true"
                class="btn-primary btn-sm whitespace-nowrap"
              >
                <svg
                  class="w-4 h-4 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                  />
                </svg>
                Manage Categories
              </button>
            </template>
          </div>
        </div>
      </div>
    </div>

    <!-- Summary Cards -->
    <template v-if="budgetData && !isLoading && !error">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Summary Card -->
        <div class="card card-hover">
          <div class="flex items-center">
            <div class="p-3 bg-blue-100 rounded-lg">
              <svg
                class="w-6 h-6 text-blue-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
                />
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-medium text-gray-600">Summary</h3>
              <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Forecast</span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ formatCurrency(summaryForecast) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Actual</span>
                  <span
                    class="text-sm font-semibold"
                    :class="
                      summaryActual >= summaryForecast
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                  >
                    {{ formatCurrency(summaryActual) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Income Card -->
        <div class="card card-hover">
          <div class="flex items-center">
            <div class="p-3 bg-green-100 rounded-lg">
              <svg
                class="w-6 h-6 text-green-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                />
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-medium text-gray-600">Income</h3>
              <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Forecast</span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ formatCurrency(incomeForecast) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Actual</span>
                  <span
                    class="text-sm font-semibold"
                    :class="
                      incomeActual >= incomeForecast
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                  >
                    {{ formatCurrency(incomeActual) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Expense Card -->
        <div class="card card-hover">
          <div class="flex items-center">
            <div class="p-3 bg-red-100 rounded-lg">
              <svg
                class="w-6 h-6 text-red-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
                />
              </svg>
            </div>
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-medium text-gray-600">Expense</h3>
              <div class="mt-2 space-y-1">
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Forecast</span>
                  <span class="text-sm font-semibold text-gray-900">
                    {{ formatCurrency(expenseForecast) }}
                  </span>
                </div>
                <div class="flex items-center justify-between">
                  <span class="text-xs text-gray-500">Actual</span>
                  <span
                    class="text-sm font-semibold"
                    :class="
                      expenseActual <= expenseForecast
                        ? 'text-green-600'
                        : 'text-red-600'
                    "
                  >
                    {{ formatCurrency(expenseActual) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </template>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div class="flex items-center space-x-2">
        <svg
          class="animate-spin h-5 w-5 text-primary"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        <span class="text-sm text-gray-600">Loading budget data...</span>
      </div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="card-modern bg-white rounded-lg shadow-sm p-6"
    >
      <div class="text-center">
        <svg
          class="mx-auto h-12 w-12 text-red-400"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          Error loading budget
        </h3>
        <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
      </div>
    </div>

    <!-- Budget Content -->
    <template v-else-if="budgetData">
      <!-- Running Balance Chart and Table -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">
            Running Balance – HOA Account ({{ selectedYear }})
          </h2>
        </div>
        <div class="p-6 space-y-6">
          <!-- Chart -->
          <div v-if="runningBalanceChartData.length > 0" class="relative">
            <div
              class="relative w-full overflow-x-auto"
              style="min-height: 280px"
            >
              <svg
                :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                class="w-full min-h-[280px]"
                preserveAspectRatio="xMidYMid meet"
              >
                <!-- Y-axis grid and labels (max at top, min at bottom) -->
                <g v-for="(tick, i) in chartYAxisTicks" :key="'grid-' + i">
                  <line
                    :x1="chartPadding.left"
                    :y1="
                      chartPadding.top +
                      chartInnerHeight * (1 - i / (chartYAxisTicks.length - 1))
                    "
                    :x2="chartWidth - chartPadding.right"
                    :y2="
                      chartPadding.top +
                      chartInnerHeight * (1 - i / (chartYAxisTicks.length - 1))
                    "
                    stroke="#E5E7EB"
                    stroke-width="1"
                    stroke-dasharray="2,2"
                  />
                  <text
                    :x="chartPadding.left - 6"
                    :y="
                      chartPadding.top +
                      chartInnerHeight * (1 - i / (chartYAxisTicks.length - 1))
                    "
                    text-anchor="end"
                    dominant-baseline="middle"
                    class="text-[10px] fill-gray-500"
                  >
                    {{ tick }}
                  </text>
                </g>
                <!-- X-axis labels -->
                <g v-for="(d, i) in runningBalanceChartData" :key="'x-' + i">
                  <text
                    :x="
                      chartPadding.left +
                      (i / 11) * chartInnerWidth +
                      chartInnerWidth / 22
                    "
                    :y="chartHeight - chartPadding.bottom + 16"
                    text-anchor="middle"
                    class="text-[10px] fill-gray-500"
                  >
                    {{ getMonthAbbr(d.month) }}
                  </text>
                </g>
                <!-- Actual line (solid) -->
                <path
                  v-if="actualPathD"
                  :d="actualPathD"
                  fill="none"
                  stroke="#374151"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <!-- Forecast line (dotted) -->
                <path
                  v-if="forecastPathD"
                  :d="forecastPathD"
                  fill="none"
                  stroke="#9CA3AF"
                  stroke-width="1.5"
                  stroke-dasharray="4,4"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </div>
            <div class="flex gap-4 mt-2 justify-center flex-wrap">
              <span class="flex items-center gap-2 text-xs text-gray-600">
                <span class="inline-block w-4 h-0.5 bg-gray-700"></span>
                Actual
              </span>
              <span class="flex items-center gap-2 text-xs text-gray-600">
                <span
                  class="inline-block w-4 h-0.5 border-t-2 border-dashed border-gray-400"
                ></span>
                Forecast
              </span>
            </div>
          </div>
          <div
            v-else-if="!isRunningBalanceLoading && runningBalanceError"
            class="py-8 text-center text-sm text-gray-500"
          >
            No running balance data. Connect bank accounts and sync transactions
            to see actuals.
          </div>
          <div
            v-else-if="isRunningBalanceLoading"
            class="py-8 text-center text-sm text-gray-500"
          >
            Loading running balance…
          </div>

          <!-- Table -->
          <div class="overflow-x-auto border border-gray-200 rounded-lg">
            <table class="w-full text-sm min-w-[800px]">
              <thead>
                <tr class="bg-red-50 border-b border-gray-200">
                  <th
                    class="px-4 py-2 text-left text-xs font-semibold text-gray-700 uppercase"
                  >
                    Running Balance
                  </th>
                  <th
                    class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase border-l border-gray-200"
                  >
                    Opening
                  </th>
                  <th
                    v-for="m in 12"
                    :key="m"
                    class="px-3 py-2 text-center text-xs font-semibold text-gray-700 uppercase border-l border-gray-200"
                  >
                    {{ getMonthAbbr(m) }}
                  </th>
                  <th
                    class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border-l border-gray-200 bg-gray-50"
                  >
                    YEAR
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr class="bg-white border-b border-gray-200">
                  <td class="px-4 py-3 font-medium text-gray-900">
                    Cash Balance
                  </td>
                  <td
                    class="px-3 py-3 text-center text-gray-900 border-l border-gray-200"
                  >
                    {{ formatTableBalance(openingBalance) }}
                  </td>
                  <td
                    v-for="m in 12"
                    :key="m"
                    class="px-3 py-3 text-center text-gray-900 border-l border-gray-200"
                  >
                    {{ formatTableBalance(runningBalanceTableRow[m]) }}
                  </td>
                  <td
                    class="px-4 py-3 text-center border-l border-gray-200 bg-gray-50"
                  >
                    <span class="font-medium text-gray-900">
                      {{ formatTableBalance(runningBalanceYearEnd) }}
                    </span>
                    <span
                      v-if="runningBalanceYearDelta !== null"
                      :class="[
                        'ml-1 text-xs',
                        runningBalanceYearDelta >= 0
                          ? 'text-green-600'
                          : 'text-red-600',
                      ]"
                    >
                      {{ runningBalanceYearDelta >= 0 ? '+' : ''
                      }}{{ formatCurrency(runningBalanceYearDelta) }}
                      {{
                        runningBalanceYearDelta >= 0 ? 'Increase' : 'Decrease'
                      }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Income Section -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Income</h2>
        </div>
        <BudgetTable
          :categories="budgetData.income"
          :is-resident="isResident"
          :year="selectedYear"
          type="income"
          @update-entry="handleUpdateEntry"
        />
      </div>

      <!-- Expense Section -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Expense</h2>
        </div>
        <BudgetTable
          :categories="budgetData.expense"
          :is-resident="isResident"
          :year="selectedYear"
          type="expense"
          @update-entry="handleUpdateEntry"
        />
      </div>

      <!-- Activity Log -->
      <div class="card-modern bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Activity Log</h2>
        </div>
        <BudgetAuditLog :year="selectedYear" />
      </div>
    </template>

    <!-- Category Manager Modal -->
    <CategoryManager
      v-if="showCategoryManager"
      :is-open="showCategoryManager"
      @close="showCategoryManager = false"
    />

    <!-- Copy Budget Modal -->
    <ConfirmDialog
      v-if="showCopyModal"
      :is-open="showCopyModal"
      title="Copy Budget"
      :message="`Copy budget from ${selectedYear} to which year?`"
      confirm-text="Copy"
      cancel-text="Cancel"
      type="warning"
      :is-loading="isCopying"
      @confirm="handleCopyBudget"
      @cancel="handleCancelCopy"
    >
      <template #default>
        <div class="mt-4 space-y-4">
          <!-- Target Year Dropdown -->
          <div>
            <label
              for="target-year"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Target Year
            </label>
            <select
              id="target-year"
              v-model.number="targetYear"
              class="input-field w-full"
            >
              <option
                v-for="year in targetYearOptions"
                :key="year"
                :value="year"
              >
                {{ year }}
              </option>
            </select>
          </div>

          <!-- Copy Type Selection -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              What to Copy
            </label>
            <div class="space-y-2">
              <label class="flex items-center">
                <input
                  v-model="copyType"
                  type="radio"
                  value="all"
                  class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700"
                  >All (Income & Expenses)</span
                >
              </label>
              <label class="flex items-center">
                <input
                  v-model="copyType"
                  type="radio"
                  value="income"
                  class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700">Income Only</span>
              </label>
              <label class="flex items-center">
                <input
                  v-model="copyType"
                  type="radio"
                  value="expense"
                  class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                />
                <span class="ml-2 text-sm text-gray-700">Expenses Only</span>
              </label>
            </div>
          </div>

          <!-- Warning Message -->
          <div class="rounded-md bg-yellow-50 p-3 border border-yellow-200">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg
                  class="h-5 w-5 text-yellow-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-yellow-800">
                  <strong>Warning:</strong> This action will overwrite existing
                  budget values for {{ targetYear }}. This cannot be undone.
                </p>
              </div>
            </div>
          </div>
        </div>
      </template>
    </ConfirmDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useBudget, useCopyBudget } from '../composables/useBudget';
import { useAuthStore } from '../stores/auth';
import { useRunningBalance } from '@neibrpay/api-client';
import BudgetTable from '../components/BudgetTable.vue';
import BudgetAuditLog from '../components/BudgetAuditLog.vue';
import CategoryManager from '../components/CategoryManager.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import {
  type BudgetEntryUpdateDto,
  getMonthAbbreviation,
} from '@neibrpay/models';

const authStore = useAuthStore();
const isResident = computed(() => authStore.isResident);

// Year selection
const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const availableYears = computed(() => {
  const years = [];
  for (let i = currentYear - 2; i <= currentYear + 5; i++) {
    years.push(i);
  }
  return years;
});

// Budget data
const { data: budgetData, isLoading, error } = useBudget(selectedYear);

// Running balance (from plaid_transactions only)
const {
  data: runningBalanceData,
  isLoading: isRunningBalanceLoading,
  error: runningBalanceError,
} = useRunningBalance(selectedYear);

// Copy budget
const showCopyModal = ref(false);
const targetYear = ref(currentYear + 1);
const copyType = ref<'all' | 'income' | 'expense'>('all');
const copyBudgetMutation = useCopyBudget();
const isCopying = computed(() => copyBudgetMutation.isPending.value);

// Available years for target year dropdown (exclude current year and past years)
const targetYearOptions = computed(() => {
  const years = [];
  for (let i = selectedYear.value + 1; i <= currentYear + 10; i++) {
    years.push(i);
  }
  return years;
});

const handleCopyBudget = async () => {
  if (!targetYear.value || targetYear.value <= selectedYear.value) {
    return;
  }

  try {
    await copyBudgetMutation.mutateAsync({
      fromYear: selectedYear.value,
      toYear: targetYear.value,
      type: copyType.value,
    });
    showCopyModal.value = false;
    selectedYear.value = targetYear.value;
    targetYear.value = currentYear + 1;
    copyType.value = 'all';
  } catch (error: any) {
    console.error('Failed to copy budget:', error);
  }
};

const handleCancelCopy = () => {
  showCopyModal.value = false;
  targetYear.value = currentYear + 1;
  copyType.value = 'all';
};

// Category manager
const showCategoryManager = ref(false);

// Update entry handler
const handleUpdateEntry = (entry: BudgetEntryUpdateDto) => {
  // Entry update is handled by BudgetTable component via useUpdateBudgetEntries
  // This emit is just for potential future use
};

// Year change handler
const handleYearChange = () => {
  // The query will automatically refetch when selectedYear changes
};

watch(selectedYear, () => {
  // Ensure we refetch when year changes
});

// Calculate summary totals
const incomeForecast = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.income.reduce(
    (sum, cat) => sum + cat.total.forecast,
    0
  );
});

const incomeActual = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.income.reduce(
    (sum, cat) => sum + cat.total.actual,
    0
  );
});

const expenseForecast = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.expense.reduce(
    (sum, cat) => sum + cat.total.forecast,
    0
  );
});

const expenseActual = computed(() => {
  if (!budgetData.value) return 0;
  return budgetData.value.expense.reduce(
    (sum, cat) => sum + cat.total.actual,
    0
  );
});

const summaryForecast = computed(() => {
  return incomeForecast.value - expenseForecast.value;
});

const summaryActual = computed(() => {
  return incomeActual.value - expenseActual.value;
});

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

// --- Running Balance chart and table ---
const currentMonth = new Date().getMonth() + 1; // 1-12

const lastCompletedMonth = computed(() => {
  const y = selectedYear.value;
  if (y < currentYear) return 12;
  if (y > currentYear) return 0;
  return Math.max(0, currentMonth - 1);
});

const runningBalanceByMonth = computed(() => {
  const list = runningBalanceData.value?.monthly_balances ?? [];
  const map: Record<number, number> = {};
  for (const { month, balance } of list) {
    map[month] = balance;
  }
  return map;
});

const openingBalance = computed(
  () => runningBalanceData.value?.opening_balance ?? 0
);

const monthlyNetForecast = computed(() => {
  if (!budgetData.value) return {} as Record<number, number>;
  const net: Record<number, number> = {};
  for (let m = 1; m <= 12; m++) {
    let income = 0;
    let expense = 0;
    for (const cat of budgetData.value!.income) {
      income += cat.months[m]?.forecast ?? 0;
    }
    for (const cat of budgetData.value!.expense) {
      expense += cat.months[m]?.forecast ?? 0;
    }
    net[m] = income - expense;
  }
  return net;
});

const runningBalanceChartData = computed(() => {
  const last = lastCompletedMonth.value;
  const byMonth = runningBalanceByMonth.value;
  const netForecast = monthlyNetForecast.value;
  const opening = openingBalance.value;
  const result: Array<{
    month: number;
    actual: number | null;
    forecast: number | null;
  }> = [];
  let forecastRunning = last > 0 ? (byMonth[last] ?? opening) : opening;
  for (let m = 1; m <= 12; m++) {
    const actual = last >= m ? (byMonth[m] ?? null) : null;
    if (m > last) {
      forecastRunning += netForecast[m] ?? 0;
      result.push({ month: m, actual, forecast: forecastRunning });
    } else {
      result.push({ month: m, actual, forecast: null });
    }
  }
  return result;
});

const chartPadding = { top: 20, right: 20, bottom: 36, left: 52 };
const chartWidth = 700;
const chartHeight = 280;
const chartInnerWidth = chartWidth - chartPadding.left - chartPadding.right;
const chartInnerHeight = chartHeight - chartPadding.top - chartPadding.bottom;

const chartExtent = computed(() => {
  let min = 0;
  let max = 0;
  for (const d of runningBalanceChartData.value) {
    const v = d.actual ?? d.forecast ?? 0;
    if (v < min) min = v;
    if (v > max) max = v;
  }
  if (min === max) {
    min = Math.min(0, min - 1000);
    max = Math.max(0, max + 1000);
  }
  const pad = (max - min) * 0.05 || 1000;
  return [min - pad, max + pad];
});

const chartYAxisTicks = computed(() => {
  const [min, max] = chartExtent.value;
  const count = 6;
  const step = (max - min) / (count - 1);
  return Array.from({ length: count }, (_, i) =>
    formatCurrency(min + i * step)
  );
});

const chartScaleY = (value: number) => {
  const [min, max] = chartExtent.value;
  const t = (value - min) / (max - min);
  return chartPadding.top + chartInnerHeight * (1 - t);
};

const actualPathD = computed(() => {
  const points: string[] = [];
  const data = runningBalanceChartData.value;
  for (let i = 0; i < data.length; i++) {
    const v = data[i].actual;
    if (v === null) continue;
    const x =
      chartPadding.left + (i / 11) * chartInnerWidth + chartInnerWidth / 22;
    const y = chartScaleY(v);
    points.push(`${i === 0 ? 'M' : 'L'} ${x} ${y}`);
  }
  return points.join(' ');
});

const forecastPathD = computed(() => {
  const points: string[] = [];
  const data = runningBalanceChartData.value;
  const last = lastCompletedMonth.value;
  if (last >= 12) return '';
  // Start from last actual month so the dotted line connects to the solid line
  const startIdx = last > 0 ? last - 1 : 0;
  for (let i = startIdx; i < data.length; i++) {
    const v = i <= last - 1 ? data[i].actual : data[i].forecast;
    if (v === null) continue;
    const x =
      chartPadding.left + (i / 11) * chartInnerWidth + chartInnerWidth / 22;
    const y = chartScaleY(v);
    points.push(`${points.length === 0 ? 'M' : 'L'} ${x} ${y}`);
  }
  return points.join(' ');
});

const runningBalanceTableRow = computed(() => {
  const last = lastCompletedMonth.value;
  const byMonth = runningBalanceByMonth.value;
  const netForecast = monthlyNetForecast.value;
  const opening = openingBalance.value;
  const row: Record<number, number> = {};
  let forecastRunning = last > 0 ? (byMonth[last] ?? opening) : opening;
  for (let m = 1; m <= 12; m++) {
    if (m <= last) {
      row[m] = byMonth[m] ?? opening;
    } else {
      forecastRunning += netForecast[m] ?? 0;
      row[m] = forecastRunning;
    }
  }
  return row;
});

const runningBalanceYearEnd = computed(() => {
  return runningBalanceTableRow.value[12] ?? 0;
});

const runningBalanceYearDelta = computed(() => {
  const open = openingBalance.value;
  const end = runningBalanceYearEnd.value;
  if (open === 0 && end === 0) return null;
  return end - open;
});

function getMonthAbbr(month: number): string {
  return getMonthAbbreviation(month);
}

function formatTableBalance(value: number | undefined): string {
  if (value === undefined) return '—';
  return formatCurrency(value);
}
</script>
