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
        <div>
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
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3
                  class="text-lg leading-6 font-medium text-gray-900"
                  id="modal-title"
                >
                  Review Payment
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Review payment details and approve or reject
                  </p>
                </div>
              </div>
            </div>

            <!-- Payment Details Section -->
            <div class="mt-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label
                    class="block text-xs font-medium text-gray-500 uppercase"
                    >Invoice Number</label
                  >
                  <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ payment?.invoiceUnit?.invoice_number || '-' }}
                  </p>
                </div>
                <div>
                  <label
                    class="block text-xs font-medium text-gray-500 uppercase"
                    >Amount</label
                  >
                  <p class="mt-1 text-sm font-medium text-gray-900">
                    ${{ formatCurrency(payment?.amount || 0) }}
                  </p>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label
                    class="block text-xs font-medium text-gray-500 uppercase"
                    >Payment Method</label
                  >
                  <p class="mt-1 text-sm text-gray-900">
                    {{ formatPaymentMethod(payment?.payment_method || '') }}
                  </p>
                </div>
                <div>
                  <label
                    class="block text-xs font-medium text-gray-500 uppercase"
                    >Payment Date</label
                  >
                  <p class="mt-1 text-sm text-gray-900">
                    {{ formatDate(payment?.payment_date || '') }}
                  </p>
                </div>
              </div>

              <div v-if="payment?.payment_reference">
                <label class="block text-xs font-medium text-gray-500 uppercase"
                  >Reference</label
                >
                <p class="mt-1 text-sm text-gray-900">
                  {{ payment.payment_reference }}
                </p>
              </div>

              <div v-if="payment?.notes">
                <label class="block text-xs font-medium text-gray-500 uppercase"
                  >Resident Notes</label
                >
                <p class="mt-1 text-sm text-gray-900 bg-gray-50 p-3 rounded">
                  {{ payment.notes }}
                </p>
              </div>

              <!-- Submitted By -->
              <div v-if="payment?.recorder">
                <label class="block text-xs font-medium text-gray-500 uppercase"
                  >Submitted By</label
                >
                <p class="mt-1 text-sm text-gray-900">
                  {{ payment.recorder.name }}
                  <span class="text-gray-500"
                    >({{ payment.recorder.email }})</span
                  >
                </p>
              </div>

              <hr class="my-4" />

              <!-- Review Comments -->
              <div>
                <label
                  for="admin_comment_public"
                  class="block text-sm font-medium text-gray-700"
                >
                  Public Comment <span class="text-red-500">*</span>
                  <span class="text-xs text-gray-500 font-normal"
                    >(visible to resident)</span
                  >
                </label>
                <textarea
                  id="admin_comment_public"
                  v-model="form.admin_comment_public"
                  rows="3"
                  required
                  :disabled="action === 'approve'"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm disabled:bg-gray-50 disabled:text-gray-600"
                  :class="{ 'border-red-300': errors.admin_comment_public }"
                  :placeholder="
                    action === 'approve'
                      ? 'Optional comment for approval'
                      : 'Required: explain rejection reason'
                  "
                ></textarea>
                <p
                  v-if="errors.admin_comment_public"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ errors.admin_comment_public }}
                </p>
                <p class="mt-1 text-xs text-gray-500">
                  {{
                    action === 'approve'
                      ? 'Optional: Add approval notes'
                      : 'Required for rejection: Explain why the payment was rejected'
                  }}
                </p>
              </div>

              <div>
                <label
                  for="admin_comment_private"
                  class="block text-sm font-medium text-gray-700"
                >
                  Private Comment
                  <span class="text-xs text-gray-500 font-normal"
                    >(admin only)</span
                  >
                </label>
                <textarea
                  id="admin_comment_private"
                  v-model="form.admin_comment_private"
                  rows="2"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                  placeholder="Internal notes (not visible to residents)"
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div
            class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2"
          >
            <!-- Approve Button -->
            <button
              @click="handleApprove"
              :disabled="isSubmitting"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg
                v-if="isSubmitting"
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
              {{ isSubmitting ? 'Approving...' : 'Approve Payment' }}
            </button>

            <!-- Reject Button -->
            <button
              @click="handleReject"
              :disabled="isSubmitting || !form.admin_comment_public"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg
                v-if="isSubmitting"
                class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
              {{ isSubmitting ? 'Rejecting...' : 'Reject Payment' }}
            </button>

            <!-- Cancel Button -->
            <button
              type="button"
              @click="closeModal"
              :disabled="isSubmitting"
              class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, defineProps, defineEmits } from 'vue';
import {
  useApprovePayment,
  useRejectPayment,
} from '../composables/usePayments';
import type { Payment } from '@neibrpay/models';

interface Props {
  isOpen: boolean;
  payment: Payment | null;
}

interface Emits {
  (e: 'close'): void;
  (e: 'approved', payment: Payment): void;
  (e: 'rejected', payment: Payment): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Composables
const approvePaymentMutation = useApprovePayment();
const rejectPaymentMutation = useRejectPayment();

// Form state
const form = ref({
  admin_comment_public: '',
  admin_comment_private: '',
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

// Methods
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

const handleApprove = async () => {
  if (!props.payment) return;

  isSubmitting.value = true;
  errors.value = {};

  try {
    const payment = await approvePaymentMutation.mutateAsync({
      paymentId: props.payment.id,
      data: {
        admin_comment_public: form.value.admin_comment_public || undefined,
        admin_comment_private: form.value.admin_comment_private || undefined,
      },
    });
    emit('approved', payment);
    closeModal();
  } catch (error: any) {
    console.error('Error approving payment:', error);

    // Handle validation errors from the API
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = error.message || 'Failed to approve payment';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const handleReject = async () => {
  if (!props.payment) return;

  // Validate public comment is required for rejection
  if (!form.value.admin_comment_public) {
    errors.value.admin_comment_public =
      'Public comment is required for rejection';
    return;
  }

  isSubmitting.value = true;
  errors.value = {};

  try {
    const payment = await rejectPaymentMutation.mutateAsync({
      paymentId: props.payment.id,
      data: {
        admin_comment_public: form.value.admin_comment_public,
        admin_comment_private: form.value.admin_comment_private || undefined,
      },
    });
    emit('rejected', payment);
    closeModal();
  } catch (error: any) {
    console.error('Error rejecting payment:', error);

    // Handle validation errors from the API
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = error.message || 'Failed to reject payment';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const closeModal = () => {
  resetForm();
  emit('close');
};

const resetForm = () => {
  form.value = {
    admin_comment_public: '',
    admin_comment_private: '',
  };
  errors.value = {};
};

// Watch for modal open/close
watch(
  () => props.isOpen,
  (isOpen: boolean) => {
    if (!isOpen) {
      resetForm();
    }
  }
);
</script>
