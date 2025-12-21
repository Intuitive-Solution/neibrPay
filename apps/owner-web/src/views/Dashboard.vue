<template>
  <div class="dashboard">
    <h2 class="text-xl font-semibold mb-4">Owner Dashboard</h2>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Dues Summary</h3>
        <p class="text-gray-600">Current dues status will go here</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Quick Pay</h3>
        <p class="text-gray-600">Payment options will go here</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Documents</h3>
        <p class="text-gray-600">Community documents will go here</p>
      </div>
    </div>

    <!-- Expenses Section Placeholder -->
    <div class="mt-6">
      <h3 class="text-lg font-semibold mb-4">Expenses</h3>
      <div class="bg-white p-6 rounded-lg shadow">
        <p class="text-gray-600">Expenses information will be displayed here</p>
      </div>
    </div>

    <!-- Budget Summary Card -->
    <div class="mt-6">
      <div
        class="bg-white p-6 rounded-lg shadow cursor-pointer hover:shadow-lg transition-shadow duration-200"
        @click="navigateToBudget"
      >
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg">
            <svg
              class="w-6 h-6 text-purple-600"
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
            <h3 class="text-sm font-medium text-gray-600">
              Budget ({{ currentYear }})
            </h3>
            <div v-if="!budgetLoading && budgetData" class="mt-2 space-y-1">
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Forecast</span>
                <span class="text-sm font-semibold text-gray-900">
                  {{ formatCurrency(budgetForecast) }}
                </span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs text-gray-500">Actual</span>
                <span
                  class="text-sm font-semibold"
                  :class="
                    budgetActual >= budgetForecast
                      ? 'text-green-600'
                      : 'text-red-600'
                  "
                >
                  {{ formatCurrency(budgetActual) }}
                </span>
              </div>
            </div>
            <p v-else class="text-2xl font-bold text-gray-900 mt-1">-</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useBudget } from '../composables/useBudget';

const router = useRouter();

// Budget data for current year
const currentYear = new Date().getFullYear();
const { data: budgetData, isLoading: budgetLoading } = useBudget(currentYear);

// Budget summary calculations
const budgetForecast = computed(() => {
  if (!budgetData.value) return 0;
  const incomeForecast = budgetData.value.income.reduce(
    (sum, cat) => sum + cat.total.forecast,
    0
  );
  const expenseForecast = budgetData.value.expense.reduce(
    (sum, cat) => sum + cat.total.forecast,
    0
  );
  return incomeForecast - expenseForecast;
});

const budgetActual = computed(() => {
  if (!budgetData.value) return 0;
  const incomeActual = budgetData.value.income.reduce(
    (sum, cat) => sum + cat.total.actual,
    0
  );
  const expenseActual = budgetData.value.expense.reduce(
    (sum, cat) => sum + cat.total.actual,
    0
  );
  return incomeActual - expenseActual;
});

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount);
};

const navigateToBudget = () => {
  router.push('/budget');
};
</script>
