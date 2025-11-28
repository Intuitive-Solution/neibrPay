<template>
  <div class="space-y-6">
    <!-- No Bank Accounts Connected -->
    <div
      v-if="bankAccounts.length === 0"
      class="p-8 bg-blue-50 border border-blue-200 rounded-lg text-center"
    >
      <svg
        class="mx-auto h-12 w-12 text-blue-400 mb-4"
        fill="none"
        stroke="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
        />
      </svg>
      <h2 class="text-lg font-medium text-blue-900 mb-2">
        No Bank Accounts Connected
      </h2>
      <p class="text-sm text-blue-800 mb-6">
        Connect a bank account in Settings to start viewing transactions.
      </p>
      <router-link
        :to="{ name: 'Settings', query: { tab: 'bank' } }"
        class="btn-primary inline-block"
      >
        Add Bank Account
      </router-link>
    </div>

    <!-- Transactions Section -->
    <div v-else class="space-y-6">
      <!-- Bank Balance Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Total Account Balance Card -->
        <div
          class="card card-hover cursor-pointer transition-all"
          :class="{
            'ring-2 ring-primary': filters.bank_account_id === null,
          }"
          @click="handleTotalAccountClick"
        >
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-medium text-gray-600">
                Total Account Balance
              </h3>
            </div>
            <div class="p-3 bg-primary-100 rounded-lg">
              <svg
                class="w-6 h-6 text-primary"
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
          </div>
          <div class="mt-4">
            <p class="text-2xl font-bold text-gray-900">
              {{ formatCurrency(totalCurrentBalance) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              Available: {{ formatCurrency(totalAvailableBalance) }}
            </p>
          </div>
        </div>

        <!-- Bank Account Balance Cards -->
        <div
          v-for="account in bankAccounts"
          :key="account.id"
          class="card card-hover cursor-pointer transition-all"
          :class="{
            'ring-2 ring-primary': filters.bank_account_id === account.id,
          }"
          @click="handleAccountClick(account.id)"
        >
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-medium text-gray-600">
                {{ account.account_name }}
              </h3>
              <p class="text-xs text-gray-500 mt-1">
                ••••{{ account.account_mask }}
              </p>
            </div>
            <div class="p-3 bg-primary-100 rounded-lg">
              <svg
                class="w-6 h-6 text-primary"
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
          </div>
          <div class="mt-4">
            <p class="text-2xl font-bold text-gray-900">
              {{ formatCurrency(Number(account.current_balance || 0)) }}
            </p>
            <p class="text-xs text-gray-500 mt-1">
              Available:
              {{ formatCurrency(Number(account.available_balance || 0)) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Filters Card -->
      <div class="card-modern">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
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
                {{ account.account_name }} (••••{{ account.account_mask }})
              </option>
            </select>
          </div>

          <!-- Start Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Start Date
            </label>
            <input
              v-model="filters.start_date"
              type="date"
              class="input-field"
              @change="currentPage = 1"
            />
          </div>

          <!-- End Date -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              End Date
            </label>
            <input
              v-model="filters.end_date"
              type="date"
              class="input-field"
              @change="currentPage = 1"
            />
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

      <!-- Transactions Table -->
      <div class="card-modern overflow-hidden">
        <!-- Loading State -->
        <div v-if="isLoadingTransactions" class="p-12 text-center">
          <div
            class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary mx-auto"
          ></div>
          <p class="mt-4 text-sm text-gray-600">Loading transactions...</p>
        </div>

        <!-- No Transactions -->
        <div v-else-if="transactions.length === 0" class="p-12 text-center">
          <svg
            class="mx-auto h-12 w-12 text-gray-300 mb-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
            />
          </svg>
          <p class="text-gray-600">No transactions found</p>
        </div>

        <!-- Transactions Table -->
        <div v-else class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
              <tr>
                <th
                  class="px-6 py-3 text-left font-semibold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors select-none group"
                  @click="handleSort('date')"
                >
                  <div class="flex items-center gap-2">
                    DATE
                    <span
                      v-if="sortBy === 'date'"
                      class="text-primary font-normal"
                    >
                      {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </span>
                    <span
                      v-else
                      class="text-gray-400 opacity-0 group-hover:opacity-50"
                    >
                      ↕
                    </span>
                  </div>
                </th>
                <th
                  class="px-6 py-3 text-left font-semibold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors select-none group"
                  @click="handleSort('name')"
                >
                  <div class="flex items-center gap-2">
                    DESCRIPTION
                    <span
                      v-if="sortBy === 'name'"
                      class="text-primary font-normal"
                    >
                      {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </span>
                    <span
                      v-else
                      class="text-gray-400 opacity-0 group-hover:opacity-50"
                    >
                      ↕
                    </span>
                  </div>
                </th>
                <th
                  class="px-6 py-3 text-left font-semibold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors select-none group"
                  @click="handleSort('category')"
                >
                  <div class="flex items-center gap-2">
                    CATEGORY
                    <span
                      v-if="sortBy === 'category'"
                      class="text-primary font-normal"
                    >
                      {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </span>
                    <span
                      v-else
                      class="text-gray-400 opacity-0 group-hover:opacity-50"
                    >
                      ↕
                    </span>
                  </div>
                </th>
                <th
                  class="px-6 py-3 text-left font-semibold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors select-none group"
                  @click="handleSort('amount')"
                >
                  <div class="flex items-center gap-2">
                    AMOUNT
                    <span
                      v-if="sortBy === 'amount'"
                      class="text-primary font-normal"
                    >
                      {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </span>
                    <span
                      v-else
                      class="text-gray-400 opacity-0 group-hover:opacity-50"
                    >
                      ↕
                    </span>
                  </div>
                </th>
                <th
                  class="px-6 py-3 text-left font-semibold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors select-none group"
                  @click="handleSort('pending')"
                >
                  <div class="flex items-center gap-2">
                    STATUS
                    <span
                      v-if="sortBy === 'pending'"
                      class="text-primary font-normal"
                    >
                      {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </span>
                    <span
                      v-else
                      class="text-gray-400 opacity-0 group-hover:opacity-50"
                    >
                      ↕
                    </span>
                  </div>
                </th>
                <th
                  class="px-6 py-3 text-left font-semibold text-gray-900 cursor-pointer hover:bg-gray-100 transition-colors select-none group"
                  @click="handleSort('plaid_bank_account_id')"
                >
                  <div class="flex items-center gap-2">
                    ACCOUNT
                    <span
                      v-if="sortBy === 'plaid_bank_account_id'"
                      class="text-primary font-normal"
                    >
                      {{ sortOrder === 'asc' ? '↑' : '↓' }}
                    </span>
                    <span
                      v-else
                      class="text-gray-400 opacity-0 group-hover:opacity-50"
                    >
                      ↕
                    </span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(transaction, idx) in transactions"
                :key="transaction.id"
                :class="[
                  'border-b border-gray-200 hover:bg-gray-50 transition-colors',
                  idx % 2 === 0 ? 'bg-white' : 'bg-gray-50',
                ]"
              >
                <td
                  class="px-6 py-4 text-gray-900 font-medium whitespace-nowrap"
                >
                  {{ formatDate(transaction.date) }}
                </td>
                <td class="px-6 py-4 text-gray-900">
                  <div class="font-medium">{{ transaction.name }}</div>
                  <div
                    v-if="transaction.merchant_name"
                    class="text-xs text-gray-500 mt-1"
                  >
                    {{ transaction.merchant_name }}
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-600 text-xs">
                  <span
                    v-if="transaction.category"
                    class="px-2 py-1 bg-gray-100 rounded"
                  >
                    {{ formatCategory(transaction.category) }}
                  </span>
                  <span v-else class="text-gray-400">—</span>
                </td>
                <td class="px-6 py-4 font-medium whitespace-nowrap">
                  <span
                    :class="[
                      Number(transaction.amount) >= 0
                        ? 'text-green-600'
                        : 'text-red-600',
                    ]"
                  >
                    {{ Number(transaction.amount) >= 0 ? '+' : ''
                    }}{{ formatCurrency(Number(transaction.amount)) }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="[
                      'px-2 py-1 rounded text-xs font-medium',
                      transaction.pending
                        ? 'bg-yellow-50 text-yellow-700'
                        : 'bg-green-50 text-green-700',
                    ]"
                  >
                    {{ transaction.pending ? 'Pending' : 'Posted' }}
                  </span>
                </td>
                <td class="px-6 py-4 text-gray-600 text-xs">
                  <div v-if="transaction.bank_account">
                    {{ transaction.bank_account.account_name }} ({{
                      transaction.bank_account.account_mask
                    }})
                  </div>
                  <div v-else class="text-gray-400">—</div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div
          v-if="pagination.total > 0"
          class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between"
        >
          <div class="text-sm text-gray-600">
            Showing
            <span class="font-medium">{{ pagination.from }}</span>
            to
            <span class="font-medium">{{ pagination.to }}</span>
            of
            <span class="font-medium">{{ pagination.total }}</span>
            transactions
          </div>
          <div class="flex gap-2">
            <button
              @click="currentPage = Math.max(1, currentPage - 1)"
              :disabled="currentPage === 1 || isLoadingTransactions"
              class="px-3 py-2 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Previous
            </button>
            <div class="flex items-center gap-2">
              <span class="text-sm text-gray-600">
                Page
                <span class="font-medium">{{ currentPage }}</span>
                of
                <span class="font-medium">{{ pagination.last_page }}</span>
              </span>
            </div>
            <button
              @click="
                currentPage = Math.min(pagination.last_page, currentPage + 1)
              "
              :disabled="
                currentPage >= pagination.last_page || isLoadingTransactions
              "
              class="px-3 py-2 border border-gray-300 rounded text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import {
  useBankAccounts,
  useTransactions,
  type BankAccount,
} from '@neibrpay/api-client';
import { useAuthStore } from '../stores/auth';

// Router and Auth
const router = useRouter();
const authStore = useAuthStore();
const isAdmin = computed(() => authStore.isAdmin);

// Queries
const { data: bankAccountsData } = useBankAccounts();
const bankAccounts = computed(
  () => bankAccountsData.value?.bank_accounts || []
);

// Filter state
const filters = ref({
  bank_account_id: null as number | null,
  start_date: null as string | null,
  end_date: null as string | null,
  search: null as string | null,
  pending: null as boolean | null,
});

// Sorting state
const sortBy = ref<
  | 'date'
  | 'name'
  | 'amount'
  | 'category'
  | 'pending'
  | 'plaid_bank_account_id'
  | null
>('date');
const sortOrder = ref<'asc' | 'desc'>('desc');

// Pagination
const currentPage = ref(1);
const isRefreshing = ref(false);

// Compute transactions query parameters
const queryParams = computed(() => ({
  ...filters.value,
  page: currentPage.value,
  per_page: 20,
  sort_by: sortBy.value,
  sort_order: sortOrder.value,
}));

// Fetch transactions with reactive query parameters
const { data: transactionsData, isLoading: isLoadingTransactions } =
  useTransactions(queryParams);

// Transactions and pagination
const transactions = computed(() => transactionsData.value?.data || []);
const pagination = computed(() => ({
  total: transactionsData.value?.pagination?.total || 0,
  per_page: transactionsData.value?.pagination?.per_page || 20,
  current_page: transactionsData.value?.pagination?.current_page || 1,
  last_page: transactionsData.value?.pagination?.last_page || 1,
  from: transactionsData.value?.pagination?.from || 0,
  to: transactionsData.value?.pagination?.to || 0,
}));

// Calculate total balances across all accounts
const totalCurrentBalance = computed(() => {
  return bankAccounts.value.reduce((sum: number, account: BankAccount) => {
    return sum + Number(account.current_balance || 0);
  }, 0);
});

const totalAvailableBalance = computed(() => {
  return bankAccounts.value.reduce((sum: number, account: BankAccount) => {
    return sum + Number(account.available_balance || 0);
  }, 0);
});

// Helper Functions
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  });
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
};

const formatCategory = (category: string) => {
  return category
    .split('_')
    .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1))
    .join(' ');
};

// Actions
const resetFilters = () => {
  filters.value = {
    bank_account_id: null,
    start_date: null,
    end_date: null,
    search: null,
    pending: null,
  };
  sortBy.value = 'date';
  sortOrder.value = 'desc';
  currentPage.value = 1;
};

const handleSort = (
  column:
    | 'date'
    | 'name'
    | 'amount'
    | 'category'
    | 'pending'
    | 'plaid_bank_account_id'
) => {
  if (sortBy.value === column) {
    // Toggle sort order if clicking the same column
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column and default to descending
    sortBy.value = column;
    sortOrder.value = 'desc';
  }
  currentPage.value = 1; // Reset to first page when sorting changes
};

const refreshTransactions = async () => {
  isRefreshing.value = true;
  // The query will automatically refetch due to the computed queryParams dependency
  setTimeout(() => {
    isRefreshing.value = false;
  }, 500);
};

// Handle account card clicks
const handleAccountClick = (accountId: number) => {
  filters.value.bank_account_id = accountId;
  currentPage.value = 1;
};

// Handle total account balance card click (show all)
const handleTotalAccountClick = () => {
  filters.value.bank_account_id = null;
  currentPage.value = 1;
};

// Navigate to bank settings tab
const goToBankSettings = () => {
  router.push({ name: 'Settings', query: { tab: 'bank' } });
};
</script>
