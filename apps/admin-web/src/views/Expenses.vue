<template>
  <div class="space-y-6">
    <!-- Header: Add Expense Button -->
    <div class="flex justify-end">
      <router-link to="/expenses/create" class="hidden md:inline-flex">
        <button class="btn-primary">
          <svg
            class="w-5 h-5 mr-2"
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
          Add Expense
        </button>
      </router-link>
    </div>

    <!-- Filters -->
    <div class="card">
      <h3 class="text-base font-semibold text-gray-900 mb-4">Filters</h3>
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
            class="input-field mt-1"
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
            class="input-field mt-1"
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
          <label for="category" class="block text-sm font-medium text-gray-700">
            Category
          </label>
          <select
            id="category"
            v-model="filters.category"
            class="input-field mt-1"
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
            class="input-field mt-1"
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

      <!-- Checkboxes -->
      <div class="flex items-center space-x-4 mt-4">
        <label class="flex items-center">
          <input
            v-model="includePaid"
            type="checkbox"
            class="rounded border-gray-300 text-primary focus:ring-primary"
          />
          <span class="ml-2 text-sm text-gray-600">Show paid</span>
        </label>
        <label class="flex items-center">
          <input
            v-model="includeDeleted"
            type="checkbox"
            class="rounded border-gray-300 text-primary focus:ring-primary"
          />
          <span class="ml-2 text-gray-600">Show deleted</span>
        </label>
      </div>
    </div>

    <!-- Expense Directory -->
    <div class="card">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-semibold text-gray-900">Expense Directory</h2>
        <button
          @click="refetch"
          :disabled="isLoading"
          class="btn-outline btn-sm"
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

      <div class="overflow-x-auto -mx-6">
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
              class="table-row-hover"
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
                  class="badge"
                >
                  {{ getExpenseStatusDisplayName(expense.status) }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <DropdownMenu>
                  <template #default="{ close }">
                    <button
                      @click="
                        () => {
                          viewExpense(expense.id);
                          close();
                        }
                      "
                      class="dropdown-item"
                    >
                      View
                    </button>
                    <button
                      @click="
                        () => {
                          editExpense(expense.id);
                          close();
                        }
                      "
                      class="dropdown-item"
                    >
                      Edit
                    </button>
                    <button
                      v-if="!expense.deleted_at"
                      @click="
                        () => {
                          deleteExpense(expense);
                          close();
                        }
                      "
                      :disabled="deletingExpenseId === expense.id"
                      class="dropdown-item-danger"
                    >
                      {{
                        deletingExpenseId === expense.id
                          ? 'Deleting...'
                          : 'Delete'
                      }}
                    </button>
                    <button
                      v-else
                      @click="
                        () => {
                          showRestoreConfirmation(expense);
                          close();
                        }
                      "
                      :disabled="restoringExpenseId === expense.id"
                      class="dropdown-item"
                    >
                      {{
                        restoringExpenseId === expense.id
                          ? 'Restoring...'
                          : 'Restore'
                      }}
                    </button>
                  </template>
                </DropdownMenu>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile Fixed Bottom Button -->
    <div
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/expenses/create" class="block">
        <button class="btn-primary w-full">
          <svg
            class="w-5 h-5 mr-2"
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
          Add Expense
        </button>
      </router-link>
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
import DropdownMenu from '../components/DropdownMenu.vue';

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
