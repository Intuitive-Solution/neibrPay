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
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
      >
        <form @submit.prevent="handleSubmit">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div
                class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10"
              >
                <svg
                  class="h-6 w-6 text-green-600"
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
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3
                  class="text-lg leading-6 font-medium text-gray-900"
                  id="modal-title"
                >
                  Record Payment
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Record a payment for Invoice #{{ invoice?.invoice_number }}
                  </p>
                </div>
              </div>
            </div>

            <!-- Form Fields -->
            <div class="mt-6 space-y-4">
              <!-- Invoice Info (Read-only) -->
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700"
                    >Invoice Number</label
                  >
                  <div
                    class="mt-1 p-2 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-900"
                  >
                    {{ invoice?.invoice_number }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700"
                    >Balance Due</label
                  >
                  <div
                    class="mt-1 p-2 bg-gray-50 border border-gray-300 rounded-md text-sm text-gray-900"
                  >
                    ${{ formatCurrency(invoice?.balance_due || 0) }}
                  </div>
                </div>
              </div>

              <!-- Payment Date -->
              <div>
                <label
                  for="payment_date"
                  class="block text-sm font-medium text-gray-700"
                >
                  Payment Date <span class="text-red-500">*</span>
                </label>
                <input
                  id="payment_date"
                  v-model="form.payment_date"
                  type="date"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                  :class="{ 'border-red-300': errors.payment_date }"
                />
                <p v-if="errors.payment_date" class="mt-1 text-sm text-red-600">
                  {{ errors.payment_date }}
                </p>
              </div>

              <!-- Amount -->
              <div>
                <label
                  for="amount"
                  class="block text-sm font-medium text-gray-700"
                >
                  Amount Received <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                  <div
                    class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                  >
                    <span class="text-gray-500 sm:text-sm">$</span>
                  </div>
                  <input
                    id="amount"
                    v-model.number="form.amount"
                    type="number"
                    step="0.01"
                    min="0.01"
                    :max="invoice?.balance_due || 0"
                    required
                    class="block w-full pl-7 pr-12 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :class="{ 'border-red-300': errors.amount }"
                    placeholder="0.00"
                  />
                </div>
                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">
                  {{ errors.amount }}
                </p>
                <p
                  v-if="form.amount > (invoice?.balance_due || 0)"
                  class="mt-1 text-sm text-yellow-600"
                >
                  ⚠️ Amount exceeds balance due. This will result in an
                  overpayment.
                </p>
              </div>

              <!-- Payment Method -->
              <div>
                <label
                  for="payment_method"
                  class="block text-sm font-medium text-gray-700"
                >
                  Payment Method <span class="text-red-500">*</span>
                </label>
                <select
                  id="payment_method"
                  v-model="form.payment_method"
                  required
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                  :class="{ 'border-red-300': errors.payment_method }"
                >
                  <option value="">Select payment method</option>
                  <option value="cash">Cash</option>
                  <option value="check">Check</option>
                  <option value="credit_card">Credit Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="stripe_card">Stripe (Card)</option>
                  <option value="stripe_ach">Stripe (ACH)</option>
                  <option value="other">Other</option>
                </select>
                <p
                  v-if="errors.payment_method"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ errors.payment_method }}
                </p>
              </div>

              <!-- Transaction Reference -->
              <div>
                <label
                  for="payment_reference"
                  class="block text-sm font-medium text-gray-700"
                >
                  Transaction Reference
                </label>
                <input
                  id="payment_reference"
                  v-model="form.payment_reference"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                  placeholder="e.g., Check #1234, Transaction ID"
                />
              </div>

              <!-- Notes -->
              <div>
                <label
                  for="notes"
                  class="block text-sm font-medium text-gray-700"
                >
                  Notes
                </label>
                <textarea
                  id="notes"
                  v-model="form.notes"
                  rows="3"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                  placeholder="Additional payment notes..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="submit"
              :disabled="isSubmitting || !isFormValid"
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
              {{ isSubmitting ? 'Recording...' : 'Record Payment' }}
            </button>
            <button
              type="button"
              @click="closeModal"
              :disabled="isSubmitting"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancel
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, nextTick, defineProps, defineEmits } from 'vue';
import { useCreatePayment } from '../composables/usePayments';
import type { InvoiceUnit, CreatePaymentRequest } from '@neibrpay/models';

interface Props {
  isOpen: boolean;
  invoice: InvoiceUnit | null;
}

interface Emits {
  (e: 'close'): void;
  (e: 'success'): void;
}

const props = defineProps<Props>();
const emit = defineEmits<Emits>();

// Composables
const createPaymentMutation = useCreatePayment();

// Form state
const form = ref<CreatePaymentRequest>({
  amount: 0,
  payment_method: 'cash',
  payment_reference: '',
  notes: '',
  payment_date: new Date().toISOString().split('T')[0], // Today's date
});

const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

// Computed properties
const isFormValid = computed(() => {
  return (
    form.value.amount > 0 &&
    form.value.payment_method &&
    form.value.payment_date &&
    Object.keys(errors.value).length === 0
  );
});

// Methods
const formatCurrency = (amount: number | string) => {
  if (amount === null || amount === undefined) return '0.00';
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  return numAmount.toFixed(2);
};

const validateForm = () => {
  errors.value = {};

  if (!form.value.amount || form.value.amount <= 0) {
    errors.value.amount = 'Amount must be greater than 0';
  }

  if (!form.value.payment_method) {
    errors.value.payment_method = 'Payment method is required';
  }

  if (!form.value.payment_date) {
    errors.value.payment_date = 'Payment date is required';
  }

  return Object.keys(errors.value).length === 0;
};

const handleSubmit = async () => {
  if (!validateForm() || !props.invoice) return;

  isSubmitting.value = true;
  errors.value = {};

  try {
    await createPaymentMutation.mutateAsync({
      invoiceId: props.invoice.id,
      data: form.value,
    });

    emit('success');
    closeModal();
  } catch (error: any) {
    console.error('Error creating payment:', error);

    // Handle validation errors from the API
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value.general = error.message || 'Failed to record payment';
    }
  } finally {
    isSubmitting.value = false;
  }
};

const closeModal = () => {
  // Reset form
  form.value = {
    amount: 0,
    payment_method: 'cash',
    payment_reference: '',
    notes: '',
    payment_date: new Date().toISOString().split('T')[0],
  };
  errors.value = {};

  emit('close');
};

// Watch for modal open/close to reset form
watch(
  () => props.isOpen,
  (isOpen: boolean) => {
    if (isOpen && props.invoice) {
      nextTick(() => {
        // Set default amount to balance due if it's a reasonable amount
        if (
          props.invoice!.balance_due > 0 &&
          props.invoice!.balance_due <= 10000
        ) {
          form.value.amount = props.invoice!.balance_due;
        }
      });
    }
  }
);
</script>
