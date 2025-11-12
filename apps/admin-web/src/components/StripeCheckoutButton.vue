<template>
  <div>
    <!-- Amount Input (Optional for partial payments) -->
    <div v-if="showAmountInput" class="mb-4">
      <label
        for="stripe-amount"
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
          id="stripe-amount"
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
        Leave empty to pay full balance: ${{
          formatCurrency(invoice?.balance_due || 0)
        }}
      </p>
      <p
        v-if="paymentAmount > (invoice?.balance_due || 0)"
        class="mt-1 text-sm text-yellow-600"
      >
        ⚠️ Amount exceeds balance due.
      </p>
    </div>

    <!-- Stripe Checkout Button -->
    <button
      @click="handleCheckout"
      :disabled="
        isLoading ||
        (showAmountInput && paymentAmount < 0.5) ||
        (showAmountInput && paymentAmount > (invoice?.balance_due || 0))
      "
      class="w-full inline-flex justify-center items-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
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
      {{ isLoading ? 'Processing...' : buttonText }}
    </button>

    <!-- Error Message -->
    <p v-if="errorMessage" class="mt-2 text-sm text-red-600">
      {{ errorMessage }}
    </p>

    <!-- Info Message -->
    <p class="mt-2 text-xs text-gray-500">
      Secure payment via Stripe. Supports credit cards and ACH bank transfers.
    </p>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { paymentsApi } from '@neibrpay/api-client';
import type { InvoiceUnit } from '@neibrpay/models';

interface Props {
  invoice: InvoiceUnit;
  showAmountInput?: boolean;
  buttonText?: string;
}

const props = withDefaults(defineProps<Props>(), {
  showAmountInput: false,
  buttonText: 'Pay with Stripe',
});

const emit = defineEmits<{
  success: [sessionId: string];
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

    // Create Stripe Checkout session
    const response = await paymentsApi.createStripeCheckout(
      props.invoice.id,
      amount
    );

    // Redirect to Stripe Checkout
    if (response.checkout_url) {
      window.location.href = response.checkout_url;
      emit('success', response.session_id);
    } else {
      throw new Error('No checkout URL received from server');
    }
  } catch (error: any) {
    console.error('Stripe checkout error:', error);
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
