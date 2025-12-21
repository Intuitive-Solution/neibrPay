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
      type="info"
      :is-loading="isCopying"
      @confirm="handleCopyBudget"
      @cancel="showCopyModal = false"
    >
      <template #default>
        <div class="mt-4">
          <label
            for="target-year"
            class="block text-sm font-medium text-gray-700 mb-2"
          >
            Target Year
          </label>
          <input
            id="target-year"
            v-model.number="targetYear"
            type="number"
            :min="selectedYear + 1"
            :max="selectedYear + 10"
            class="input-field w-full"
            placeholder="Enter year"
          />
        </div>
      </template>
    </ConfirmDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useBudget, useCopyBudget } from '../composables/useBudget';
import { useAuthStore } from '../stores/auth';
import BudgetTable from '../components/BudgetTable.vue';
import BudgetAuditLog from '../components/BudgetAuditLog.vue';
import CategoryManager from '../components/CategoryManager.vue';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import type { BudgetEntryUpdateDto } from '@neibrpay/models';

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

// Copy budget
const showCopyModal = ref(false);
const targetYear = ref(currentYear + 1);
const copyBudgetMutation = useCopyBudget();
const isCopying = computed(() => copyBudgetMutation.isPending.value);

const handleCopyBudget = async () => {
  if (!targetYear.value || targetYear.value <= selectedYear.value) {
    return;
  }

  try {
    await copyBudgetMutation.mutateAsync({
      fromYear: selectedYear.value,
      toYear: targetYear.value,
    });
    showCopyModal.value = false;
    selectedYear.value = targetYear.value;
    targetYear.value = currentYear + 1;
  } catch (error: any) {
    console.error('Failed to copy budget:', error);
  }
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
</script>
