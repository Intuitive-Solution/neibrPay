<template>
  <div>
    <!-- Amount Input (Optional for partial payments) -->
    <div v-if="showAmountInput" class="mb-4">
      <label
        for="paypal-amount"
        class="block text-sm font-medium text-gray-700 mb-2"
      >
        Payment Amount
      </label>
      <div class="relative rounded-md shadow-sm">
        <div
          class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
        >
          <span class="text-gray-500 sm:text-sm">$</span>
        </div>
        <input
          id="paypal-amount"
          v-model.number="paymentAmount"
          type="number"
          step="0.01"
          min="0.50"
          :max="invoice?.balance_due || 0"
          class="block w-full pl-7 pr-12 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
          placeholder="0.00"
        />
      </div>
      <p class="mt-1 text-sm text-gray-500">
        Leave empty to pay full balance:
        {{ formatCurrency(invoice?.balance_due || 0) }}
      </p>
      <p
        v-if="paymentAmount > (invoice?.balance_due || 0)"
        class="mt-1 text-sm text-yellow-600"
      >
        ⚠️ Amount exceeds balance due.
      </p>
    </div>

    <!-- PayPal Checkout Button -->
    <button
      @click="handleCheckout"
      :disabled="
        isLoading ||
        (showAmountInput && paymentAmount !== null && paymentAmount < 0.5) ||
        (showAmountInput &&
          paymentAmount !== null &&
          paymentAmount > (invoice?.balance_due || 0))
      "
      class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
        ></circle>
        <path
          class="opacity-75"
          fill="currentColor"
          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        ></path>
      </svg>
      <svg
        v-else
        class="-ml-1 mr-2 h-5 w-5"
        fill="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.174 1.351.96 3.3.869 4.717l-.003.015v.002c-.09 1.303-.228 3.266 1.088 4.855 1.31 1.577 3.956 2.144 6.158 2.144h2.141a.64.64 0 0 1 .633.74l-1.533 8.94a.642.642 0 0 1-.63.525l-2.724-.027a.376.376 0 0 0-.374.44l.36 2.844a.64.64 0 0 1-.63.74H16.33a.48.48 0 0 0-.48.6l.664 3.96a.64.64 0 0 1-.63.74l-2.9.027a.48.48 0 0 0-.48.6l.66 3.94a.64.64 0 0 1-.63.74l-4.73.044zm.46-3.027l1.93-.018a.48.48 0 0 0 .48-.6l-.66-3.94a.64.64 0 0 1 .63-.74l2.9-.027a.48.48 0 0 0 .48-.6l-.664-3.96a.64.64 0 0 1 .63-.74h5.35a.376.376 0 0 0 .374-.44l-.36-2.844a.64.64 0 0 1 .63-.74h-2.141c-1.918 0-3.968-.445-4.863-1.528-.893-1.075-1.01-2.614-1.032-3.15l.003-.016v-.002c.09-1.303.228-3.266-1.088-4.855-1.31-1.577-3.956-2.144-6.158-2.144H6.998l-2.706 15.758h4.606l-.664-3.96a.64.64 0 0 1 .63-.74l2.9-.027a.48.48 0 0 0 .48-.6l-.66-3.94a.64.64 0 0 1 .63-.74l4.73-.044z"
        />
      </svg>
      {{ isLoading ? 'Processing...' : buttonText }}
    </button>

    <!-- Error Message -->
    <p v-if="errorMessage" class="mt-2 text-sm text-red-600">
      {{ errorMessage }}
    </p>

    <!-- Info Message -->
    <p class="mt-2 text-xs text-gray-500">
      Secure payment via PayPal. Supports PayPal balance, credit cards, and bank
      accounts.
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { paymentsApi } from '@neibrpay/api-client';
import type { InvoiceUnit } from '@neibrpay/models';

interface Props {
  invoice: InvoiceUnit;
  showAmountInput?: boolean;
  buttonText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  showAmountInput: false,
  buttonText: 'Pay with PayPal',
});

const emit = defineEmits<{
  success: [orderId: string];
  error: [error: string];
}>();

const isLoading = ref(false);
const errorMessage = ref('');
const paymentAmount = ref<number | null>(null);

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
  }).format(amount);
};

const handleCheckout = async () => {
  if (!props.invoice) {
    errorMessage.value = 'Invoice information is missing.';
    return;
  }

  if (props.invoice.balance_due <= 0) {
    errorMessage.value = 'This invoice has no balance due.';
    return;
  }

  // Validate amount if amount input is shown
  if (props.showAmountInput && paymentAmount.value !== null) {
    if (paymentAmount.value < 0.5) {
      errorMessage.value = 'Minimum payment amount is $0.50.';
      return;
    }
    if (paymentAmount.value > props.invoice.balance_due) {
      errorMessage.value = 'Payment amount cannot exceed balance due.';
      return;
    }
  }

  isLoading.value = true;
  errorMessage.value = '';

  try {
    // Determine amount to charge
    const amount =
      props.showAmountInput && paymentAmount.value !== null
        ? paymentAmount.value
        : undefined; // undefined means use balance_due

    // Create PayPal Checkout session
    const response = await paymentsApi.createPayPalCheckout(
      props.invoice.id,
      amount
    );

    // Redirect to PayPal Checkout
    if (response.checkout_url) {
      window.location.href = response.checkout_url;
      emit('success', response.order_id);
    } else {
      throw new Error('No checkout URL received from server');
    }
  } catch (error: any) {
    console.error('PayPal checkout error:', error);
    errorMessage.value =
      error.response?.data?.message ||
      error.message ||
      'Failed to create payment session. Please try again.';
    emit('error', errorMessage.value);
  } finally {
    isLoading.value = false;
  }
};
</script>
