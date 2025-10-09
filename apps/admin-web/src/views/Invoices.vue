<template>
  <div class="space-y-6">
    <!-- Quick Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Add Invoice Card -->
      <router-link to="/invoices/create" class="block">
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
              <h3 class="text-lg font-semibold text-gray-900">Add Invoice</h3>
              <p class="text-sm text-gray-600">Create new invoice</p>
            </div>
          </div>
        </div>
      </router-link>

      <!-- View All Invoices Card -->
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
              View All Invoices
            </h3>
            <p class="text-sm text-gray-600">Browse invoice directory</p>
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

    <!-- Invoice Directory Section -->
    <div class="bg-white rounded-lg shadow">
      <!-- Header Section -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Invoice Directory</h1>
            <p class="text-sm text-gray-600 mt-1">
              Manage community invoices and billing
            </p>
          </div>

          <!-- Header Controls -->
          <div class="flex items-center space-x-4">
            <!-- Show Deleted Checkbox -->
            <div class="flex items-center">
              <input
                id="show-deleted"
                v-model="includeDeleted"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
              />
              <label for="show-deleted" class="ml-2 text-sm text-gray-700">
                Show deleted
              </label>
            </div>

            <!-- Refresh Button -->
            <button
              @click="refetch"
              :disabled="isLoading"
              class="inline-flex items-center p-2 border border-gray-300 rounded-full shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
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
            <span class="text-sm text-gray-600">Loading invoices...</span>
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
              Error loading invoices
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
          v-else-if="!filteredInvoices || filteredInvoices.length === 0"
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
              No invoices found
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                includeDeleted
                  ? 'No deleted invoices found.'
                  : 'Get started by creating your first invoice.'
              }}
            </p>
            <div v-if="!includeDeleted" class="mt-4">
              <router-link to="/invoices/create">
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
                  Create Invoice
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
                UNIT
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
              v-for="invoice in filteredInvoices"
              :key="invoice.id"
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
                      {{ invoice.invoice_number || `#${invoice.id}` }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ formatDate(invoice.created_at) }}
                    </div>
                  </div>
                </div>
              </td>

              <!-- Unit Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ invoice.unit?.title || 'N/A' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ invoice.unit?.address || '' }}
                </div>
              </td>

              <!-- Amount Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  ${{ formatCurrency(invoice.total) }}
                </div>
                <div class="text-sm text-gray-500">
                  Balance: ${{ formatCurrency(invoice.balance_due) }}
                </div>
              </td>

              <!-- Due Date Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(invoice.start_date) }}
              </td>

              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="getStatusBadgeClass(invoice.status)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ getStatusText(invoice.status) }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="viewInvoice(invoice.id)"
                    class="text-primary hover:text-primary-600"
                  >
                    View
                  </button>
                  <button
                    @click="editInvoice(invoice.id)"
                    class="text-blue-600 hover:text-blue-900"
                  >
                    Edit
                  </button>
                  <button
                    v-if="!invoice.deleted_at"
                    @click="deleteInvoice(invoice.id)"
                    :disabled="deletingInvoiceId === invoice.id"
                    class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{
                      deletingInvoiceId === invoice.id
                        ? 'Deleting...'
                        : 'Delete'
                    }}
                  </button>
                  <button
                    v-else
                    @click="restoreInvoice(invoice.id)"
                    :disabled="restoringInvoiceId === invoice.id"
                    class="text-green-600 hover:text-green-900 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{
                      restoringInvoiceId === invoice.id
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
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  useInvoices,
  useDeleteInvoice,
  useRestoreInvoice,
} from '@/composables/useInvoices';
import type { InvoiceUnit } from '@neibrpay/models';

const router = useRouter();

// Local state
const includeDeleted = ref(false);
const deletingInvoiceId = ref<number | null>(null);
const restoringInvoiceId = ref<number | null>(null);

// Queries and mutations
const {
  data: invoices,
  isLoading,
  error,
  refetch: refetchInvoices,
} = useInvoices({ include_deleted: includeDeleted });

const deleteInvoiceMutation = useDeleteInvoice();
const restoreInvoiceMutation = useRestoreInvoice();

// Computed properties - invoices are already filtered by the API based on include_deleted
const filteredInvoices = computed(() => {
  return invoices.value || [];
});

// Methods
const refetch = () => {
  refetchInvoices();
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

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    sent: 'Sent',
    paid: 'Paid',
    overdue: 'Overdue',
    cancelled: 'Cancelled',
  };
  return statusMap[status] || 'Unknown';
};

const getStatusBadgeClass = (status: string) => {
  const statusClasses: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-yellow-100 text-yellow-800',
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

const viewInvoice = (invoiceId: number) => {
  router.push(`/invoices/${invoiceId}`);
};

const editInvoice = (invoiceId: number) => {
  router.push(`/invoices/${invoiceId}/edit`);
};

const deleteInvoice = async (invoiceId: number) => {
  if (
    confirm(
      'Are you sure you want to delete this invoice? This action can be undone.'
    )
  ) {
    deletingInvoiceId.value = invoiceId;
    try {
      console.log('Starting delete for invoice:', invoiceId);
      const result = await deleteInvoiceMutation.mutateAsync(invoiceId);
      console.log('Delete result:', result);
      // Show success message
      console.log('Invoice deleted successfully');
      // Refetch data to update the list
      refetch();
    } catch (error: any) {
      console.error('Error deleting invoice:', error);

      // Check if it's an authentication error
      if (error.message && error.message.includes('Invoice not found')) {
        // This might be an authentication issue
        alert('Authentication error. Please refresh the page and try again.');
        // Optionally redirect to login
        // router.push('/login');
      } else {
        // Show error message to user
        alert(`Failed to delete invoice: ${error.message || 'Unknown error'}`);
      }
    } finally {
      // Always reset the loading state
      deletingInvoiceId.value = null;
      deleteInvoiceMutation.reset();
    }
  }
};

const restoreInvoice = async (invoiceId: number) => {
  restoringInvoiceId.value = invoiceId;
  try {
    await restoreInvoiceMutation.mutateAsync(invoiceId);
    // Show success message
    console.log('Invoice restored successfully');
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error restoring invoice:', error);
    // Show error message to user
    alert(`Failed to restore invoice: ${error.message || 'Unknown error'}`);
  } finally {
    // Always reset the loading state
    restoringInvoiceId.value = null;
    restoreInvoiceMutation.reset();
  }
};

// Watch for changes in includeDeleted to refetch data
watch(includeDeleted, () => {
  // Refetch with new filter
  refetchInvoices();
});

// Reset mutation states on component mount to clear any stuck states
onMounted(() => {
  deleteInvoiceMutation.reset();
  restoreInvoiceMutation.reset();
});

// Global reset function for debugging (can be called from browser console)
(window as any).resetInvoiceMutations = () => {
  deleteInvoiceMutation.reset();
  restoreInvoiceMutation.reset();
  deletingInvoiceId.value = null;
  restoringInvoiceId.value = null;
  console.log('Invoice mutations and local state reset');
};
</script>
