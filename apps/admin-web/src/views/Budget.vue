<template>
  <div class="space-y-6">
    <!-- Header Section -->
    <div class="card-modern bg-white rounded-lg shadow-sm">
      <div class="px-6 py-4 border-b border-gray-200">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Budget</h1>
            <p class="text-sm text-gray-600 mt-1">
              Track income and expense forecasts vs actuals
            </p>
          </div>
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
                class="btn-secondary btn-sm"
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
                Copy to New Year
              </button>
              <button
                @click="showCategoryManager = true"
                class="btn-primary btn-sm"
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
      title="Copy Budget to New Year"
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
</script>
