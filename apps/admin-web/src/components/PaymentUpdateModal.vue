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
        <form @submit.prevent="submitPayment">
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
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                  />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3
                  class="text-lg leading-6 font-medium text-gray-900"
                  id="modal-title"
                >
                  Update Payment
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Update the payment details for this invoice.
                  </p>
                </div>
              </div>
            </div>

            <!-- Error Message -->
            <div
              v-if="errors.general"
              class="mt-4 p-3 bg-red-50 border border-red-200 rounded-md"
            >
              <p class="text-sm text-red-600">{{ errors.general }}</p>
            </div>

            <!-- Form Fields -->
            <div class="mt-6 space-y-4">
              <!-- Invoice Information (Read-only) -->
              <div class="bg-gray-50 p-3 rounded-md">
                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium text-gray-700">Invoice:</span>
                    <p class="text-gray-900">
                      {{ payment?.invoiceUnit?.invoice_number || 'N/A' }}
                    </p>
                  </div>
                  <div>
                    <span class="font-medium text-gray-700"
                      >Current Amount:</span
                    >
                    <p class="text-gray-900">
                      ${{ formatCurrency(payment?.amount || 0) }}
                    </p>
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
                  Amount <span class="text-red-500">*</span>
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
                    required
                    class="block w-full pl-7 pr-12 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                    :class="{ 'border-red-300': errors.amount }"
                    placeholder="0.00"
                  />
                </div>
                <p v-if="errors.amount" class="mt-1 text-sm text-red-600">
                  {{ errors.amount }}
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
                  <option value="cash">Cash</option>
                  <option value="check">Check</option>
                  <option value="credit_card">Credit Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="other">Other</option>
                </select>
                <p
                  v-if="errors.payment_method"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ errors.payment_method }}
                </p>
              </div>

              <!-- Payment Reference -->
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
                  placeholder="Check number, transaction ID, etc."
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
                  placeholder="Additional notes about this payment..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Modal Actions -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="submit"
              :disabled="isUpdatingPayment"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg
                v-if="isUpdatingPayment"
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
              {{ isUpdatingPayment ? 'Updating...' : 'Update Payment' }}
            </button>
            <button
              type="button"
              @click="closeModal"
              :disabled="isUpdatingPayment"
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
import { useUpdatePayment } from '../composables/usePayments';
import type { Payment, UpdatePaymentRequest } from '@neibrpay/models';

interface Props {
  isOpen: boolean;
  payment: Payment | null;
}

const props = defineProps<Props>();
const emit = defineEmits<{
  close: [];
  success: [];
}>();

const form = ref<UpdatePaymentRequest>({
  amount: 0,
  payment_method: 'cash',
  payment_reference: '',
  notes: '',
  payment_date: new Date().toISOString().split('T')[0], // Default to today
});

const errors = ref<Record<string, string>>({});

const updatePaymentMutation = useUpdatePayment();
const isUpdatingPayment = computed(() => updatePaymentMutation.isPending.value);

const formatCurrency = (amount: number) => {
  return amount.toFixed(2);
};

const submitPayment = async () => {
  errors.value = {}; // Clear previous errors

  if (!props.payment) {
    console.error('No payment selected for update.');
    return;
  }

  try {
    await updatePaymentMutation.mutateAsync({
      id: props.payment.id,
      data: form.value,
    });
    emit('success');
    closeModal();
  } catch (error: any) {
    console.error('Payment update error:', error);
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors;
    } else {
      errors.value = { general: error.message || 'Failed to update payment.' };
    }
  }
};

const closeModal = () => {
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

// Watch for modal open/close to populate form
watch(
  () => props.isOpen,
  (isOpen: boolean) => {
    if (isOpen && props.payment) {
      nextTick(() => {
        form.value = {
          amount: props.payment!.amount,
          payment_method: props.payment!.payment_method,
          payment_reference: props.payment!.payment_reference || '',
          notes: props.payment!.notes || '',
          payment_date: props.payment!.payment_date,
        };
        errors.value = {};
      });
    }
  }
);
</script>
