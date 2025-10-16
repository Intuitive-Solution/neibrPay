<template>
  <div class="space-y-6">
    <!-- Invoice Directory Section -->
    <div class="bg-white rounded-lg shadow-sm">
      <!-- Header Section -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
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
          </div>

          <!-- Header Controls -->
          <div class="flex items-center space-x-3">
            <!-- Refresh Button -->
            <button
              @click="refetch"
              :disabled="isLoading"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 transition-colors duration-200"
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

            <!-- New Invoice Button (Desktop) -->
            <router-link to="/invoices/create" class="hidden md:inline-flex">
              <button class="btn-primary btn-sm">
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
                New Invoice
              </button>
            </router-link>
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
              {{
                includeDeleted
                  ? 'No deleted invoices found'
                  : includePaid
                    ? 'No invoices found'
                    : 'No unpaid invoices found'
              }}
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                includeDeleted
                  ? 'No deleted invoices found.'
                  : includePaid
                    ? 'No invoices found.'
                    : 'No unpaid invoices found. Toggle "Show paid" to see all invoices.'
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
                  class="badge"
                >
                  {{ getStatusText(invoice.status) }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <DropdownMenu>
                  <template #default="{ close }">
                    <button
                      @click="
                        () => {
                          viewInvoice(invoice.id);
                          close();
                        }
                      "
                      class="dropdown-item"
                    >
                      View Preview
                    </button>
                    <button
                      @click="
                        () => {
                          editInvoice(invoice.id);
                          close();
                        }
                      "
                      class="dropdown-item"
                    >
                      Edit
                    </button>
                    <button
                      v-if="!invoice.deleted_at"
                      @click="
                        () => {
                          deleteInvoice(invoice);
                          close();
                        }
                      "
                      :disabled="deletingInvoiceId === invoice.id"
                      class="dropdown-item-danger"
                    >
                      {{
                        deletingInvoiceId === invoice.id
                          ? 'Deleting...'
                          : 'Delete'
                      }}
                    </button>
                    <button
                      v-else
                      @click="
                        () => {
                          showRestoreConfirmation(invoice);
                          close();
                        }
                      "
                      :disabled="restoringInvoiceId === invoice.id"
                      class="dropdown-item"
                    >
                      {{
                        restoringInvoiceId === invoice.id
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

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showDeleteModal"
      title="Delete Invoice"
      :message="`Are you sure you want to delete ${invoiceToDelete?.invoice_number || 'this invoice'}? This action can be undone by restoring the invoice.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletingInvoiceId === invoiceToDelete?.id"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Restore Confirmation Modal -->
    <ConfirmDialog
      :is-open="showRestoreModal"
      title="Restore Invoice"
      :message="`Are you sure you want to restore ${invoiceToRestore?.invoice_number || 'this invoice'}? This will make the invoice active again.`"
      confirm-text="Restore"
      cancel-text="Cancel"
      type="info"
      :is-loading="restoringInvoiceId === invoiceToRestore?.id"
      @confirm="confirmRestore"
      @cancel="cancelRestore"
    />

    <!-- Mobile Fixed Bottom Button -->
    <div
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/invoices/create" class="block">
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
          New Invoice
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import {
  useInvoices,
  useDeleteInvoice,
  useRestoreInvoice,
} from '../composables/useInvoices';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import DropdownMenu from '../components/DropdownMenu.vue';

const router = useRouter();

// Local state
const includeDeleted = ref(false);
const includePaid = ref(false);
const deletingInvoiceId = ref<number | null>(null);
const restoringInvoiceId = ref<number | null>(null);
const showDeleteModal = ref(false);
const showRestoreModal = ref(false);
const invoiceToDelete = ref<any>(null);
const invoiceToRestore = ref<any>(null);

// Queries and mutations
const {
  data: invoices,
  isLoading,
  error,
  refetch: refetchInvoices,
} = useInvoices({ include_deleted: true }); // Always fetch all invoices including deleted ones

const deleteInvoiceMutation = useDeleteInvoice();
const restoreInvoiceMutation = useRestoreInvoice();

// Computed properties - filter invoices based on includePaid and includeDeleted
const filteredInvoices = computed(() => {
  if (!invoices.value) return [];

  return invoices.value.filter((invoice: any) => {
    // Filter out paid invoices unless includePaid is true
    if (invoice.status === 'paid' && !includePaid.value) {
      return false;
    }

    // Filter out deleted invoices unless includeDeleted is true
    if (invoice.deleted_at && !includeDeleted.value) {
      return false;
    }

    return true;
  });
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
    partial: 'Partial',
    overdue: 'Overdue',
    cancelled: 'Cancelled',
  };
  return statusMap[status] || 'Unknown';
};

const getStatusBadgeClass = (status: string) => {
  const statusClasses: Record<string, string> = {
    draft: 'badge-draft',
    sent: 'badge-sent',
    paid: 'badge-paid',
    partial: 'badge-partial',
    overdue: 'badge-overdue',
    cancelled: 'badge-partial',
  };
  return statusClasses[status] || 'badge-draft';
};

const viewInvoice = (invoiceId: number) => {
  router.push(`/invoices/${invoiceId}`);
};

const editInvoice = (invoiceId: number) => {
  router.push(`/invoices/${invoiceId}/edit`);
};

const deleteInvoice = (invoice: any) => {
  // Store the invoice to delete and show the modal
  invoiceToDelete.value = invoice;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!invoiceToDelete.value) return;

  const invoiceId = invoiceToDelete.value.id;
  deletingInvoiceId.value = invoiceId;

  try {
    console.log('Starting delete for invoice:', invoiceId);
    const result = await deleteInvoiceMutation.mutateAsync(invoiceId);
    console.log('Delete result:', result);
    // Show success message
    console.log('Invoice deleted successfully');
    // Close modal
    showDeleteModal.value = false;
    invoiceToDelete.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error deleting invoice:', error);

    // Check if it's an authentication error
    if (error.message && error.message.includes('Invoice not found')) {
      // This might be an authentication issue
      alert('Authentication error. Please refresh the page and try again.');
    } else {
      // Show error message to user
      alert(`Failed to delete invoice: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    deletingInvoiceId.value = null;
    deleteInvoiceMutation.reset();
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  invoiceToDelete.value = null;
};

const showRestoreConfirmation = (invoice: any) => {
  invoiceToRestore.value = invoice;
  showRestoreModal.value = true;
};

const confirmRestore = async () => {
  if (!invoiceToRestore.value) return;

  const invoiceId = invoiceToRestore.value.id;
  restoringInvoiceId.value = invoiceId;

  try {
    console.log('Starting restore for invoice:', invoiceId);
    const result = await restoreInvoiceMutation.mutateAsync(invoiceId);
    console.log('Restore result:', result);
    // Show success message
    console.log('Invoice restored successfully');
    // Close modal
    showRestoreModal.value = false;
    invoiceToRestore.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error restoring invoice:', error);

    // Check if it's an authentication error or invoice not found
    if (error.message && error.message.includes('Invoice not found')) {
      // This might be an authentication issue or the invoice was already restored
      alert(
        'Invoice not found. It may have already been restored or deleted permanently.'
      );
    } else {
      // Show error message to user
      alert(`Failed to restore invoice: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    restoringInvoiceId.value = null;
    restoreInvoiceMutation.reset();
  }
};

const cancelRestore = () => {
  showRestoreModal.value = false;
  invoiceToRestore.value = null;
};

// Note: No need to watch includeDeleted as the query automatically refetches when reactive filters change

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
