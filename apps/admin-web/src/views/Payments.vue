<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-lg shadow p-6">
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
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">Total Payments</h3>
            <p class="text-2xl font-bold text-gray-900">
              {{ payments?.length || 0 }}
            </p>
          </div>
        </div>
      </div>

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
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
              />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">Total Amount</h3>
            <p class="text-2xl font-bold text-gray-900">
              ${{ formatCurrency(totalAmount) }}
            </p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-yellow-100 rounded-lg">
            <svg
              class="w-6 h-6 text-yellow-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
              />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">This Month</h3>
            <p class="text-2xl font-bold text-gray-900">
              ${{ formatCurrency(monthlyAmount) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label
            for="start_date"
            class="block text-sm font-medium text-gray-700"
            >Start Date</label
          >
          <input
            id="start_date"
            v-model="filters.start_date"
            type="date"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
          />
        </div>
        <div>
          <label for="end_date" class="block text-sm font-medium text-gray-700"
            >End Date</label
          >
          <input
            id="end_date"
            v-model="filters.end_date"
            type="date"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
          />
        </div>
        <div>
          <label
            for="payment_method"
            class="block text-sm font-medium text-gray-700"
            >Payment Method</label
          >
          <select
            id="payment_method"
            v-model="filters.payment_method"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
          >
            <option value="">All Methods</option>
            <option value="cash">Cash</option>
            <option value="check">Check</option>
            <option value="credit_card">Credit Card</option>
            <option value="bank_transfer">Bank Transfer</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="clearFilters"
            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            Clear Filters
          </button>
        </div>
      </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">All Payments</h2>
          <button
            @click="() => refetch()"
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
            <span class="text-sm text-gray-600">Loading payments...</span>
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
              Error loading payments
            </h3>
            <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
            <div class="mt-4">
              <button
                @click="() => refetch()"
                class="text-sm text-primary hover:text-primary-600"
              >
                Try again
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!payments || payments.length === 0"
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
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              No payments found
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              No payments match your current filters.
            </p>
          </div>
        </div>

        <!-- Table with Data -->
        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Invoice
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Unit
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Payment Date
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Amount
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Method
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Reference
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Recorded By
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="payment in payments"
              :key="payment.id"
              class="hover:bg-gray-50"
            >
              <!-- Invoice Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  <router-link
                    :to="`/invoices/${payment.invoice_unit_id}`"
                    class="text-primary hover:text-primary-600"
                  >
                    {{
                      payment.invoiceUnit?.invoice_number ||
                      `#${payment.invoice_unit_id}`
                    }}
                  </router-link>
                </div>
              </td>

              <!-- Unit Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">
                  {{ payment.invoiceUnit?.unit?.title || 'N/A' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ payment.invoiceUnit?.unit?.address || '' }}
                </div>
              </td>

              <!-- Payment Date Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(payment.payment_date) }}
              </td>

              <!-- Amount Column -->
              <td
                class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
              >
                ${{ formatCurrency(payment.amount) }}
              </td>

              <!-- Method Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="getPaymentMethodBadgeClass(payment.payment_method)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ formatPaymentMethod(payment.payment_method) }}
                </span>
              </td>

              <!-- Reference Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.payment_reference || '-' }}
              </td>

              <!-- Recorded By Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ payment.recorder?.name || 'Unknown' }}
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex space-x-2">
                  <button
                    @click="viewPayment(payment)"
                    class="text-primary hover:text-primary-600"
                  >
                    View
                  </button>
                  <button
                    @click="deletePayment(payment)"
                    :disabled="deletingPaymentId === payment.id"
                    class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    {{
                      deletingPaymentId === payment.id
                        ? 'Deleting...'
                        : 'Delete'
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
import { ref, computed, watch } from 'vue';
import { usePayments, useDeletePayment } from '../composables/usePayments';
import type { PaymentFilters } from '@neibrpay/models';

// Local state
const filters = ref<PaymentFilters>({
  start_date: '',
  end_date: '',
  payment_method: undefined,
});

const deletingPaymentId = ref<number | null>(null);

// Queries
const {
  data: payments,
  isLoading,
  error,
  refetch,
} = usePayments(filters.value);

// Mutations
const deletePaymentMutation = useDeletePayment();

// Computed properties
const totalAmount = computed(() => {
  if (!payments.value) return 0;
  return payments.value.reduce(
    (sum: number, payment: any) => sum + payment.amount,
    0
  );
});

const monthlyAmount = computed(() => {
  if (!payments.value) return 0;
  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1);

  return payments.value
    .filter((payment: any) => new Date(payment.payment_date) >= startOfMonth)
    .reduce((sum: number, payment: any) => sum + payment.amount, 0);
});

// Methods
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

const formatPaymentMethod = (method: string) => {
  const methodMap: Record<string, string> = {
    cash: 'Cash',
    check: 'Check',
    credit_card: 'Credit Card',
    bank_transfer: 'Bank Transfer',
    other: 'Other',
  };
  return methodMap[method] || method;
};

const getPaymentMethodBadgeClass = (method: string) => {
  const methodClasses: Record<string, string> = {
    cash: 'bg-green-100 text-green-800',
    check: 'bg-blue-100 text-blue-800',
    credit_card: 'bg-purple-100 text-purple-800',
    bank_transfer: 'bg-indigo-100 text-indigo-800',
    other: 'bg-gray-100 text-gray-800',
  };
  return methodClasses[method] || 'bg-gray-100 text-gray-800';
};

const clearFilters = () => {
  filters.value = {
    start_date: '',
    end_date: '',
    payment_method: undefined,
  };
};

const viewPayment = (payment: any) => {
  // TODO: Implement payment detail view
  console.log('View payment:', payment);
};

const deletePayment = async (payment: any) => {
  if (
    !confirm(
      `Are you sure you want to delete this payment of $${formatCurrency(payment.amount)}?`
    )
  ) {
    return;
  }

  deletingPaymentId.value = payment.id;

  try {
    await deletePaymentMutation.mutateAsync(payment.id);
    // Success message could be shown here
  } catch (error: any) {
    console.error('Error deleting payment:', error);
    alert(error.message || 'Failed to delete payment');
  } finally {
    deletingPaymentId.value = null;
  }
};

// Watch for filter changes to refetch data
watch(
  filters,
  () => {
    refetch();
  },
  { deep: true }
);
</script>
