<template>
  <div
    v-if="isOpen"
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="check-modal-title"
    role="dialog"
    aria-modal="true"
  >
    <div
      class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
    >
      <div
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        aria-hidden="true"
        @click="handleClose"
      />
      <span
        class="hidden sm:inline-block sm:align-middle sm:h-screen"
        aria-hidden="true"
        >&#8203;</span
      >

      <div
        class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
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
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3
                id="check-modal-title"
                class="text-lg leading-6 font-medium text-gray-900"
              >
                Pay by Check
              </h3>
              <p class="mt-2 text-sm text-gray-500">
                Drop off or mail a check. We'll mark the invoice when it's
                received.
              </p>
            </div>
          </div>

          <div v-if="invoice" class="mt-6 space-y-4">
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Amount to pay</span>
                <span class="font-semibold text-gray-900">
                  {{ formatCurrency(invoice.balance_due) }}
                </span>
              </div>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-gray-900 font-medium">
                Make checks payable to:
              </p>
              <p class="mt-1 font-semibold text-gray-900">{{ hoaName }}</p>
              <p class="text-sm text-gray-900 font-medium mt-3">
                Mail or drop off to:
              </p>
              <div class="text-sm text-gray-700 mt-2 whitespace-pre-wrap">
                {{ hoaAddress }}
              </div>
              <p class="text-xs text-gray-600 mt-3 italic">
                Your payment will be marked when we receive the check.
              </p>
            </div>
          </div>

          <p v-if="errorMessage" class="mt-4 text-sm text-red-600">
            {{ errorMessage }}
          </p>
        </div>

        <div
          class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3"
        >
          <button
            :disabled="isLoading || !invoice || invoice.balance_due <= 0"
            type="button"
            @click="handleConfirm"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed sm:ml-3 sm:w-auto sm:text-sm"
          >
            <svg
              v-if="isLoading"
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
              />
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              />
            </svg>
            <span v-if="isLoading">Submitting...</span>
            <span v-else>Ok, I will drop the check</span>
          </button>
          <button
            type="button"
            :disabled="isLoading"
            @click="handleClose"
            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { paymentsApi } from '@neibrpay/api-client';
import type { InvoiceUnit } from '@neibrpay/models';

interface Props {
  isOpen: boolean;
  invoice: InvoiceUnit | null;
  hoaName: string;
  hoaAddress: string;
}

const props = withDefaults(defineProps<Props>(), {
  hoaName: '',
  hoaAddress: '',
});

const emit = defineEmits<{
  (e: 'close'): void;
  (e: 'success'): void;
}>();

const isLoading = ref(false);
const errorMessage = ref('');

const formatCurrency = (amount: number): string =>
  new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
  }).format(amount);

const getTodayISO = (): string => new Date().toISOString().slice(0, 10);

const handleConfirm = async () => {
  if (!props.invoice || props.invoice.balance_due <= 0) return;
  isLoading.value = true;
  errorMessage.value = '';
  try {
    await paymentsApi.create(props.invoice.id, {
      amount: props.invoice.balance_due,
      payment_method: 'check',
      payment_date: getTodayISO(),
    });
    emit('success');
    handleClose();
  } catch (err: unknown) {
    const message =
      err && typeof err === 'object' && 'message' in err
        ? String((err as { message: unknown }).message)
        : 'Failed to submit. Please try again.';
    errorMessage.value =
      err &&
      typeof err === 'object' &&
      'response' in err &&
      typeof (err as { response: unknown }).response === 'object' &&
      (err as { response: { data?: { message?: string } } }).response?.data
        ?.message
        ? (err as { response: { data: { message: string } } }).response.data
            .message
        : message;
  } finally {
    isLoading.value = false;
  }
};

const handleClose = () => {
  if (!isLoading.value) {
    errorMessage.value = '';
    emit('close');
  }
};
</script>
