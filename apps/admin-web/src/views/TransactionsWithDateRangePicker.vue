<!-- Example: Transactions.vue with DateRangePicker Integration -->
<!-- This shows how to integrate the DateRangePicker component into your Transactions view -->

<template>
  <div class="space-y-6">
    <!-- ... Bank Accounts Section ... -->
    <!-- (Keep existing bank account balance cards code) -->

    <!-- ✅ UPDATED: Filters Card with DateRangePicker -->
    <div class="card-modern">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters</h2>

      <div class="space-y-6">
        <!-- Bank Account, Status, and Search Filters (Traditional) -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <!-- Bank Account Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Bank Account
            </label>
            <select
              v-model="filters.bank_account_id"
              class="input-field"
              @change="currentPage = 1"
            >
              <option :value="null">All Accounts</option>
              <option
                v-for="account in bankAccounts"
                :key="account.id"
                :value="account.id"
              >
                {{ account.account_name }} ({{
                  account.institution_name
                }}
                ••••{{ account.account_mask }})
              </option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Status
            </label>
            <select
              v-model="filters.pending"
              class="input-field"
              @change="currentPage = 1"
            >
              <option :value="null">All</option>
              <option :value="false">Posted</option>
              <option :value="true">Pending</option>
            </select>
          </div>

          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Search
            </label>
            <input
              v-model="filters.search"
              type="text"
              class="input-field"
              placeholder="Transaction name..."
              @change="currentPage = 1"
            />
          </div>
        </div>

        <!-- ✅ NEW: DateRangePicker Component -->
        <div class="border-t border-gray-200 pt-6">
          <h3 class="text-sm font-medium text-gray-700 mb-4">Date Range</h3>
          <DateRangePicker v-model="dateRange" />
        </div>

        <!-- ✅ Display Selected Date Range Info (Optional) -->
        <div
          v-if="dateRange.startDate && dateRange.endDate"
          class="bg-blue-50 border border-blue-200 rounded-lg p-3"
        >
          <p class="text-sm text-blue-900">
            <span class="font-semibold">Showing transactions:</span>
            {{ formatDateForDisplay(dateRange.startDate) }} to
            {{ formatDateForDisplay(dateRange.endDate) }}
            <span class="text-blue-700">({{ daysDuration }} days)</span>
          </p>
        </div>
      </div>

      <!-- Filter Actions -->
      <div class="mt-4 flex gap-2">
        <button @click="resetFilters" class="btn-secondary text-sm">
          Reset Filters
        </button>
        <button
          @click="refreshTransactions"
          :disabled="isRefreshing"
          class="btn-secondary text-sm"
        >
          <span v-if="isRefreshing">Refreshing...</span>
          <span v-else>Refresh</span>
        </button>
        <button
          v-if="isAdmin"
          @click="goToBankSettings"
          class="btn-primary text-sm"
        >
          <svg
            class="w-4 h-4 inline-block mr-1.5"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 4v16m8-8H4"
            />
          </svg>
          Add Bank Account
        </button>
      </div>
    </div>

    <!-- ... Rest of transactions table and content ... -->
    <!-- (Keep existing transactions table code) -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

// ✅ Existing component data
const filters = ref({
  bank_account_id: null as string | null,
  pending: null as boolean | null,
  search: '',
  start_date: '',
  end_date: '',
});

const currentPage = ref(1);
const isRefreshing = ref(false);
const isAdmin = ref(true); // Replace with your actual admin check

// ✅ NEW: Date range state
const dateRange = ref({
  startDate: '',
  endDate: '',
});

// ✅ Computed property for days duration
const daysDuration = computed(() => {
  if (!dateRange.value.startDate || !dateRange.value.endDate) return 0;
  const start = new Date(dateRange.value.startDate);
  const end = new Date(dateRange.value.endDate);
  return (
    Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  );
});

// ✅ Format date for display (e.g., "Jan 1, 2024")
const formatDateForDisplay = (dateString: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// ✅ Watch for date range changes and update filters
watch(
  () => dateRange.value,
  newRange => {
    // Update the filter dates
    filters.value.start_date = newRange.startDate;
    filters.value.end_date = newRange.endDate;
    // Reset to first page when filters change
    currentPage.value = 1;
    // Optionally trigger API call to fetch filtered transactions
    // fetchTransactions();
  },
  { deep: true }
);

// ✅ Reset all filters including date range
const resetFilters = () => {
  filters.value = {
    bank_account_id: null,
    pending: null,
    search: '',
    start_date: '',
    end_date: '',
  };
  dateRange.value = {
    startDate: '',
    endDate: '',
  };
  currentPage.value = 1;
};

// ✅ Refresh transactions
const refreshTransactions = async () => {
  isRefreshing.value = true;
  try {
    // Your API call to refresh transactions
    // const response = await fetchTransactions(filters.value);
    // Update your transactions state
  } finally {
    isRefreshing.value = false;
  }
};

// ✅ Navigation to bank settings
const goToBankSettings = () => {
  // Your router navigation logic
  // router.push({ name: 'Settings', query: { tab: 'bank' } });
};

// ✅ Other existing methods and data
// ... Keep all your existing methods and computed properties ...
</script>

<!-- 
INTEGRATION NOTES:

1. IMPORT: Add DateRangePicker component import at top
   import DateRangePicker from '@/components/DateRangePicker.vue';

2. STATE: Add dateRange ref
   const dateRange = ref({
     startDate: '',
     endDate: '',
   });

3. WATCHER: Watch for date range changes
   watch(() => dateRange.value, (newRange) => {
     filters.value.start_date = newRange.startDate;
     filters.value.end_date = newRange.endDate;
     currentPage.value = 1;
   }, { deep: true });

4. TEMPLATE: Replace existing Start Date and End Date inputs with:
   <DateRangePicker v-model="dateRange" />

5. RESET: Update resetFilters method to include dateRange:
   dateRange.value = { startDate: '', endDate: '' };

6. API CALLS: Your existing API calls that use 
   filters.start_date and filters.end_date will automatically
   use the values set by DateRangePicker!

BENEFITS:
✅ Better UX with preset options
✅ Cleaner filter interface
✅ Shows day count automatically
✅ Responsive design
✅ No additional dependencies
✅ Drop-in replacement for date inputs
-->
