<template>
  <div class="space-y-6">
    <!-- Controls Section -->
    <div class="bg-white rounded-lg shadow-sm p-6">
      <div class="flex items-center gap-3">
        <!-- Year Selector -->
        <select
          v-model="selectedYear"
          class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary"
          @change="handleYearChange"
        >
          <option v-for="year in availableYears" :key="year" :value="year">
            {{ year }}
          </option>
        </select>
      </div>
    </div>

    <!-- Summary Cards -->
    <template v-if="budgetData && !isLoading && !error">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Summary Card -->
        <div class="bg-white p-6 rounded-lg shadow">
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
        <div class="bg-white p-6 rounded-lg shadow">
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
        <div class="bg-white p-6 rounded-lg shadow">
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
    <div v-else-if="error" class="bg-white rounded-lg shadow-sm p-6">
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
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Income</h2>
        </div>
        <div class="p-6">
          <p class="text-sm text-gray-500">
            Budget details will be displayed here
          </p>
        </div>
      </div>

      <!-- Expense Section -->
      <div class="bg-white rounded-lg shadow-sm">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Expense</h2>
        </div>
        <div class="p-6">
          <p class="text-sm text-gray-500">
            Budget details will be displayed here
          </p>
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useBudget } from '../composables/useBudget';

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

// Year change handler
const handleYearChange = () => {
  // The query will automatically refetch when selectedYear changes
};

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
