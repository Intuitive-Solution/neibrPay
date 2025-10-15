<template>
  <div class="space-y-6">
    <!-- Quick Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Add Expense Card -->
      <router-link to="/expenses/create" class="block">
        <div
          class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow duration-200 cursor-pointer"
        >
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
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                />
              </svg>
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-semibold text-gray-900">Add Expense</h3>
              <p class="text-sm text-gray-600">Create new expense</p>
            </div>
          </div>
        </div>
      </router-link>

      <!-- View All Expenses Card -->
      <div class="bg-white rounded-lg shadow p-6">
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
                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
              />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">
              View All Expenses
            </h3>
            <p class="text-sm text-gray-600">Browse expense directory</p>
          </div>
        </div>
      </div>

      <!-- Manage Reports Card -->
      <div class="bg-white rounded-lg shadow p-6">
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
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">Manage Reports</h3>
            <p class="text-sm text-gray-600">Generate financial reports</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Expense Directory Section -->
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">
              Expense Directory
            </h2>
          </div>

          <!-- Header Controls -->
          <div class="flex items-center space-x-4">
            <!-- Show Paid Checkbox -->
            <div class="flex items-center">
              <input
                id="show-paid"
                v-model="includePaid"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
              />
              <label for="show-paid" class="ml-2 text-sm text-gray-600">
                Show paid
              </label>
            </div>
            <!-- Show Deleted Checkbox -->
            <div class="flex items-center">
              <input
                id="show-deleted"
                v-model="includeDeleted"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
              />
              <label for="show-deleted" class="ml-2 text-sm text-gray-600">
                Show deleted
              </label>
            </div>

            <!-- Refresh Button -->
            <button
              @click="refetch"
              :disabled="isLoading"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm leading-4 font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
            >
              <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                :class="{ 'animate-spin': isLoading }"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700">
              Search
            </label>
            <input
              id="search"
              v-model="filters.search"
              type="text"
              placeholder="Search by invoice number or note..."
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              @input="debouncedSearch"
            />
          </div>

          <!-- Vendor Filter -->
          <div>
            <label for="vendor" class="block text-sm font-medium text-gray-700">
              Vendor
            </label>
            <select
              id="vendor"
              v-model="filters.vendor_id"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              @change="applyFilters"
            >
              <option value="">All Vendors</option>
              <option
                v-for="vendor in vendors"
                :key="vendor.id"
                :value="vendor.id"
              >
                {{ vendor.name }}
              </option>
            </select>
          </div>

          <!-- Category Filter -->
          <div>
            <label
              for="category"
              class="block text-sm font-medium text-gray-700"
            >
              Category
            </label>
            <select
              id="category"
              v-model="filters.category"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              @change="applyFilters"
            >
              <option value="">All Categories</option>
              <option
                v-for="option in categoryOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>

          <!-- Status Filter -->
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">
              Status
            </label>
            <select
              id="status"
              v-model="filters.status"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              @change="applyFilters"
            >
              <option value="">All Status</option>
              <option
                v-for="option in statusOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="overflow-x-auto">
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
            <span class="text-sm text-gray-600">Loading expenses...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex items-center justify-center py-12">
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
              Error loading expenses
            </h3>
            <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
            <div class="mt-4">
              <button
                @click="refetch"
                class="text-sm text-primary hover:text-primary-600"
              >
                Try again
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!filteredExpenses || filteredExpenses.length === 0"
          class="flex items-center justify-center py-12"
        >
          <div class="text-center">
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              {{
                includeDeleted
                  ? 'No deleted expenses found'
                  : includePaid
                    ? 'No expenses found'
                    : 'No unpaid expenses found'
              }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                includeDeleted
                  ? 'No deleted expenses found.'
                  : includePaid
                    ? 'No expenses found.'
                    : 'No unpaid expenses found. Toggle "Show paid" to see all expenses.'
              }}
            </p>
            <div v-if="!includeDeleted" class="mt-4">
              <router-link to="/expenses/create">
                <button
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                  <svg
                    class="-ml-1 mr-2 h-4 w-4"
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
                  Create Expense
                </button>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Table with Data -->
        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                INVOICE
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                VENDOR
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                AMOUNT
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                DUE DATE
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                STATUS
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                ACTIONS
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="expense in filteredExpenses"
              :key="expense.id"
              class="hover:bg-gray-50"
            >
              <!-- Invoice Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div
                      class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center"
                    >
                      <svg
                        class="h-5 w-5 text-gray-600"
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
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ expense.invoice_number }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ formatDate(expense.invoice_date) }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Vendor Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ expense.vendor?.name || 'N/A' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ getExpenseCategoryDisplayName(expense.category) }}
                </div>
              </td>

              <!-- Amount Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  ${{ formatCurrency(expense.invoice_amount) }}
                </div>
                <div class="text-sm text-gray-500">
                  Balance: ${{
                    formatCurrency(expense.invoice_amount - expense.paid_amount)
                  }}
                </div>
              </td>

              <!-- Due Date Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(expense.invoice_due_date) }}
              </td>

              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="getExpenseStatusBadgeClass(expense.status)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ getExpenseStatusDisplayName(expense.status) }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="viewExpense(expense.id)"
                    class="text-primary hover:text-primary-600"
                  >
                    View
                  </button>
                  <button
                    @click="editExpense(expense.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </button>
                  <button
                    v-if="!expense.deleted_at"
                    @click="deleteExpense(expense)"
                    :disabled="deletingExpenseId === expense.id"
                    class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{
                      deletingExpenseId === expense.id
                        ? 'Deleting...'
                        : 'Delete'
                    }}
                  </button>
                  <button
                    v-else
                    @click="showRestoreConfirmation(expense)"
                    :disabled="restoringExpenseId === expense.id"
                    class="text-green-600 hover:text-green-900 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{
                      restoringExpenseId === expense.id
                        ? 'Restoring...'
                        : 'Restore'
                    }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showDeleteModal"
      title="Delete Expense"
      :message="`Are you sure you want to delete ${expenseToDelete?.invoice_number || 'this expense'}? This action can be undone by restoring the expense.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletingExpenseId === expenseToDelete?.id"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Restore Confirmation Modal -->
    <ConfirmDialog
      :is-open="showRestoreModal"
      title="Restore Expense"
      :message="`Are you sure you want to restore ${expenseToRestore?.invoice_number || 'this expense'}? This will make the expense active again.`"
      confirm-text="Restore"
      cancel-text="Cancel"
      type="info"
      :is-loading="restoringExpenseId === expenseToRestore?.id"
      @confirm="confirmRestore"
      @cancel="cancelRestore"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  useExpenses,
  useDeleteExpense,
  useRestoreExpense,
} from '../composables/useExpenses';
import { useQuery } from '@tanstack/vue-query';
import { vendorsApi, queryKeys } from '@neibrpay/api-client';
import {
  getExpenseCategoryDisplayName,
  getExpenseCategoryOptions,
  getExpenseStatusDisplayName,
  getExpenseStatusOptions,
  getExpenseStatusBadgeClass,
} from '@neibrpay/models';
import ConfirmDialog from '../components/ConfirmDialog.vue';

const router = useRouter();

// Local state
const includeDeleted = ref(false);
const includePaid = ref(false);
const deletingExpenseId = ref<number | null>(null);
const restoringExpenseId = ref<number | null>(null);
const showDeleteModal = ref(false);
const showRestoreModal = ref(false);
const expenseToDelete = ref<any>(null);
const expenseToRestore = ref<any>(null);

// Filters
const filters = ref({
  search: '',
  vendor_id: '',
  category: '',
  status: '',
});

// Queries and mutations
const {
  data: expenses,
  isLoading,
  error,
  refetch: refetchExpenses,
} = useExpenses({ include_deleted: true }); // Always fetch all expenses including deleted ones

// Get vendors for the dropdown
const { data: vendorsData } = useQuery({
  queryKey: queryKeys.vendors.list({}),
  queryFn: () => vendorsApi.list({}),
  select: data => data.data,
});

const vendors = computed(() => vendorsData.value || []);

const deleteExpenseMutation = useDeleteExpense();
const restoreExpenseMutation = useRestoreExpense();

// Category and status options
const categoryOptions = getExpenseCategoryOptions();
const statusOptions = getExpenseStatusOptions();

// Computed properties - filter expenses based on includePaid and includeDeleted
const filteredExpenses = computed(() => {
  if (!expenses.value) return [];

  return expenses.value.filter((expense: any) => {
    // Filter out paid expenses unless includePaid is true
    if (expense.status === 'paid' && !includePaid.value) {
      return false;
    }

    // Filter out deleted expenses unless includeDeleted is true
    if (expense.deleted_at && !includeDeleted.value) {
      return false;
    }

    return true;
  });
});

// Debounced search
let searchTimeout: NodeJS.Timeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

// Methods
const refetch = () => {
  refetchExpenses();
};

const applyFilters = () => {
  // The query will automatically refetch when filters change
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatCurrency = (amount: number | string) => {
  if (amount === null || amount === undefined) return '0.00';
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  return numAmount.toFixed(2);
};

const viewExpense = (expenseId: number) => {
  router.push(`/expenses/${expenseId}`);
};

const editExpense = (expenseId: number) => {
  router.push(`/expenses/${expenseId}/edit`);
};

const deleteExpense = (expense: any) => {
  // Store the expense to delete and show the modal
  expenseToDelete.value = expense;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!expenseToDelete.value) return;

  const expenseId = expenseToDelete.value.id;
  deletingExpenseId.value = expenseId;

  try {
    console.log('Starting delete for expense:', expenseId);
    const result = await deleteExpenseMutation.mutateAsync(expenseId);
    console.log('Delete result:', result);
    // Show success message
    console.log('Expense deleted successfully');
    // Close modal
    showDeleteModal.value = false;
    expenseToDelete.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error deleting expense:', error);

    // Check if it's an authentication error
    if (error.message && error.message.includes('Expense not found')) {
      // This might be an authentication issue
      alert('Authentication error. Please refresh the page and try again.');
    } else {
      // Show error message to user
      alert(`Failed to delete expense: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    deletingExpenseId.value = null;
    deleteExpenseMutation.reset();
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  expenseToDelete.value = null;
};

const showRestoreConfirmation = (expense: any) => {
  expenseToRestore.value = expense;
  showRestoreModal.value = true;
};

const confirmRestore = async () => {
  if (!expenseToRestore.value) return;

  const expenseId = expenseToRestore.value.id;
  restoringExpenseId.value = expenseId;

  try {
    console.log('Starting restore for expense:', expenseId);
    const result = await restoreExpenseMutation.mutateAsync(expenseId);
    console.log('Restore result:', result);
    // Show success message
    console.log('Expense restored successfully');
    // Close modal
    showRestoreModal.value = false;
    expenseToRestore.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error restoring expense:', error);

    // Check if it's an authentication error or expense not found
    if (error.message && error.message.includes('Expense not found')) {
      // This might be an authentication issue or the expense was already restored
      alert(
        'Expense not found. It may have already been restored or deleted permanently.'
      );
    } else {
      // Show error message to user
      alert(`Failed to restore expense: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    restoringExpenseId.value = null;
    restoreExpenseMutation.reset();
  }
};

const cancelRestore = () => {
  showRestoreModal.value = false;
  expenseToRestore.value = null;
};

// Reset mutation states on component mount to clear any stuck states
onMounted(() => {
  deleteExpenseMutation.reset();
  restoreExpenseMutation.reset();
});

// Global reset function for debugging (can be called from browser console)
(window as any).resetExpenseMutations = () => {
  deleteExpenseMutation.reset();
  restoreExpenseMutation.reset();
  deletingExpenseId.value = null;
  restoringExpenseId.value = null;
  console.log('Expense mutations and local state reset');
};
</script>
