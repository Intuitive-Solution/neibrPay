<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
  >
    <div
      class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
    >
      <!-- Background overlay -->
      <div
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        aria-hidden="true"
        @click="closeModal"
      ></div>

      <!-- This element is to trick the browser into centering the modal contents. -->
      <span
        class="hidden sm:inline-block sm:align-middle sm:h-screen"
        aria-hidden="true"
        >&#8203;</span
      >

      <!-- Modal panel -->
      <div
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full"
      >
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10"
            >
              <svg
                class="h-6 w-6 text-blue-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3
                class="text-lg leading-6 font-medium text-gray-900"
                id="modal-title"
              >
                Payment Details
              </h3>
              <div class="mt-2">
                <p class="text-sm text-gray-500">
                  View payment information and review comments
                </p>
              </div>
            </div>
          </div>

          <!-- Payment Details Section -->
          <div class="mt-6 space-y-4">
            <!-- Payment Summary -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-sm font-semibold text-gray-700 mb-3">
                Payment Information
              </h4>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="font-medium text-gray-600">Invoice Number:</span>
                  <p class="text-gray-900 mt-1">
                    {{
                      payment?.invoiceUnit?.invoice_number ||
                      invoice?.invoice_number ||
                      'N/A'
                    }}
                  </p>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Amount:</span>
                  <p class="text-gray-900 mt-1 font-semibold">
                    ${{ formatCurrency(payment?.amount || 0) }}
                  </p>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Payment Method:</span>
                  <p class="text-gray-900 mt-1">
                    {{ formatPaymentMethod(payment?.payment_method || '') }}
                  </p>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Payment Date:</span>
                  <p class="text-gray-900 mt-1">
                    {{ formatDate(payment?.payment_date || '') }}
                  </p>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Status:</span>
                  <p class="mt-1">
                    <span
                      :class="getPaymentStatusBadgeClass(payment?.status)"
                      class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                    >
                      {{ getPaymentStatusText(payment?.status) }}
                    </span>
                  </p>
                </div>
                <div>
                  <span class="font-medium text-gray-600">Reference:</span>
                  <p class="text-gray-900 mt-1">
                    {{ payment?.payment_reference || '-' }}
                  </p>
                </div>
                <div v-if="payment?.recorder" class="col-span-2">
                  <span class="font-medium text-gray-600">Recorded By:</span>
                  <p class="text-gray-900 mt-1">
                    {{ payment.recorder.name }}
                    <span class="text-gray-500 text-xs ml-2"
                      >({{ payment.recorder.email }})</span
                    >
                  </p>
                </div>
                <div
                  v-if="payment?.reviewer && payment?.reviewed_at"
                  class="col-span-2"
                >
                  <span class="font-medium text-gray-600">Reviewed By:</span>
                  <p class="text-gray-900 mt-1">
                    {{ payment.reviewer.name }}
                    <span class="text-gray-500 text-xs ml-2"
                      >on {{ formatDate(payment.reviewed_at) }}</span
                    >
                  </p>
                </div>
              </div>
            </div>

            <!-- Notes -->
            <div v-if="payment?.notes">
              <h4 class="text-sm font-semibold text-gray-700 mb-2">Notes:</h4>
              <div class="bg-gray-50 p-3 rounded-md">
                <p class="text-sm text-gray-900 whitespace-pre-wrap">
                  {{ payment.notes }}
                </p>
              </div>
            </div>

            <!-- Public Comment (Visible to all) -->
            <div v-if="payment?.admin_comment_public">
              <h4 class="text-sm font-semibold text-gray-700 mb-2">
                Admin Comment (Public):
              </h4>
              <div class="bg-blue-50 p-3 rounded-md border-l-4 border-blue-400">
                <p class="text-sm text-gray-900 whitespace-pre-wrap">
                  {{ payment.admin_comment_public }}
                </p>
              </div>
            </div>

            <!-- Private Comment (Admin only) -->
            <div v-if="isAdmin && payment?.admin_comment_private">
              <h4 class="text-sm font-semibold text-gray-700 mb-2">
                Admin Comment (Private):
                <span class="text-xs text-gray-500 font-normal ml-2"
                  >(admin only)</span
                >
              </h4>
              <div
                class="bg-amber-50 p-3 rounded-md border-l-4 border-amber-400"
              >
                <p class="text-sm text-gray-900 whitespace-pre-wrap">
                  {{ payment.admin_comment_private }}
                </p>
              </div>
            </div>

            <!-- No Comments Message -->
            <div
              v-if="
                !payment?.admin_comment_public &&
                (!isAdmin || !payment?.admin_comment_private)
              "
              class="bg-gray-50 p-3 rounded-md text-center"
            >
              <p class="text-sm text-gray-500">No comments available</p>
            </div>
          </div>
        </div>

        <!-- Modal Actions -->
        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button
            type="button"
            @click="closeModal"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm"
          >
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useAuthStore } from '../stores/auth';

const props = defineProps<{
  isOpen: boolean;
  payment: any;
  invoice?: any;
}>();

const emit = defineEmits<{
  close: [];
}>();

const authStore = useAuthStore();
const isAdmin = computed(() => authStore.isAdmin);

const formatCurrency = (amount: number | string) => {
  if (amount === null || amount === undefined) return '0.00';
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  return numAmount.toFixed(2);
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatPaymentMethod = (method: string) => {
  const methodMap: Record<string, string> = {
    cash: 'Cash',
    check: 'Check',
    credit_card: 'Credit Card',
    bank_transfer: 'Bank Transfer',
    stripe_card: 'Stripe (Card)',
    stripe_ach: 'Stripe (ACH)',
    other: 'Other',
  };
  return methodMap[method] || method;
};

const getPaymentStatusText = (status: string | undefined | null) => {
  if (!status) return 'Approved';

  const statusMap: Record<string, string> = {
    pending: 'Pending',
    in_review: 'In Review',
    approved: 'Approved',
    rejected: 'Rejected',
  };

  const normalizedStatus = String(status).toLowerCase();
  return statusMap[normalizedStatus] || 'Approved';
};

const getPaymentStatusBadgeClass = (status: string | undefined | null) => {
  if (!status) return 'bg-green-100 text-green-800';

  const statusClasses: Record<string, string> = {
    pending: 'bg-gray-100 text-gray-800',
    in_review: 'bg-blue-100 text-blue-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };

  const normalizedStatus = String(status).toLowerCase();
  return statusClasses[normalizedStatus] || 'bg-green-100 text-green-800';
};

const closeModal = () => {
  emit('close');
};
</script>
