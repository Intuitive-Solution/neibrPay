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
        @click="handleClose"
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
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-indigo-100 sm:mx-0 sm:h-10 sm:w-10"
            >
              <svg
                class="h-6 w-6 text-indigo-600"
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
                {{ modalTitle }}
              </h3>
              <div v-if="modalSubtitle" class="mt-2">
                <p class="text-sm text-gray-500">
                  {{ modalSubtitle }}
                </p>
              </div>
            </div>
          </div>

          <!-- Payment Method Selection (hidden in single-method mode) -->
          <div v-if="showMethodSelector" class="mt-6 space-y-4">
            <!-- Card Option -->
            <label
              class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
              :class="
                selectedMethod === 'card'
                  ? 'border-indigo-500 bg-indigo-50'
                  : 'border-gray-200 hover:border-gray-300'
              "
            >
              <input
                v-model="selectedMethod"
                type="radio"
                value="card"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
              />
              <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-900">
                    Credit/Debit Card
                  </span>
                  <span v-if="fees" class="text-sm font-semibold text-red-600">
                    +{{ formatCurrency(fees.card.processing_fee) }} fee
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Visa, Mastercard, American Express
                </p>
              </div>
            </label>

            <!-- ACH Option -->
            <label
              class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
              :class="
                selectedMethod === 'ach'
                  ? 'border-indigo-500 bg-indigo-50'
                  : 'border-gray-200 hover:border-gray-300'
              "
            >
              <input
                v-model="selectedMethod"
                type="radio"
                value="ach"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
              />
              <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-900">
                    Bank Account (ACH)
                  </span>
                  <span
                    v-if="fees"
                    class="text-sm font-semibold text-green-600"
                  >
                    +{{ formatCurrency(fees.ach.processing_fee) }} fee
                  </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Direct transfer from your bank account
                </p>
              </div>
            </label>

            <!-- Zelle Option (only when all methods shown; hidden in Stripe-only modal) -->
            <label
              v-if="showZelleOption && !singleMethod"
              class="flex items-center p-4 border-2 rounded-lg cursor-pointer transition-colors"
              :class="
                selectedMethod === 'zelle'
                  ? 'border-indigo-500 bg-indigo-50'
                  : 'border-gray-200 hover:border-gray-300'
              "
            >
              <input
                v-model="selectedMethod"
                type="radio"
                value="zelle"
                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
              />
              <div class="ml-4 flex-1">
                <div class="flex items-center justify-between">
                  <span class="text-sm font-medium text-gray-900">Zelle</span>
                  <span class="text-sm font-semibold text-green-600"
                    >No fee</span
                  >
                </div>
                <p class="text-xs text-gray-500 mt-1">
                  Send payment via Zelle; we'll confirm when received
                </p>
              </div>
            </label>
          </div>

          <!-- Zelle payment info (when Zelle selected or singleMethod zelle) -->
          <div
            v-if="showZelleContent && invoice"
            class="mt-6 pt-6 border-t border-gray-200 flex flex-col"
          >
            <!-- QR code: large for easy phone scanning -->
            <div
              v-if="zelleQrUrl"
              class="flex flex-col items-center justify-center py-2"
            >
              <img
                :src="zelleQrUrl"
                alt="Zelle QR code"
                class="w-[360px] h-[360px] min-w-[360px] min-h-[360px] object-contain border border-gray-200 rounded-lg bg-white"
              />
              <p
                v-if="zelleInstructions"
                class="text-xs text-gray-600 mt-4 text-center whitespace-pre-wrap max-w-sm"
              >
                {{ zelleInstructions }}
              </p>
            </div>
            <!-- Minimal details: amount and recipient -->
            <div class="mt-4 space-y-2 border-t border-gray-100 pt-4">
              <p class="text-sm text-gray-900">
                Send
                <span class="font-semibold">{{
                  formatCurrency(zellePaymentAmount)
                }}</span>
                to {{ hoaName }}
              </p>
              <div class="text-xs text-gray-600 space-y-0.5">
                <p v-if="zelleEmail">Email: {{ zelleEmail }}</p>
                <p v-if="zellePhone">Phone: {{ zellePhone }}</p>
              </div>
              <p
                v-if="!zelleQrUrl && zelleInstructions"
                class="text-xs text-gray-600 mt-2 whitespace-pre-wrap"
              >
                {{ zelleInstructions }}
              </p>
            </div>
          </div>

          <!-- Fee Breakdown (Card/ACH only; hidden when singleMethod zelle) -->
          <div
            v-if="showStripeContent && fees && selectedMethod !== 'zelle'"
            class="mt-6 pt-6 border-t border-gray-200"
          >
            <h4 class="text-sm font-medium text-gray-900 mb-4">
              Payment Breakdown
            </h4>
            <div class="bg-gray-50 rounded-lg p-4 space-y-3">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">HOA Dues Amount</span>
                <span class="font-medium text-gray-900">
                  {{ formatCurrency(currentBreakdown.invoice_amount) }}
                </span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Processing Fee</span>
                <span class="font-medium text-gray-900">
                  {{ formatCurrency(currentBreakdown.processing_fee) }}
                </span>
              </div>
              <div class="border-t border-gray-200 pt-3 flex justify-between">
                <span class="font-semibold text-gray-900">Total to Pay</span>
                <span class="font-bold text-lg text-indigo-600">
                  {{ formatCurrency(currentBreakdown.total) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Check Payment Information Note (hidden in single-method mode) -->
          <div
            v-if="showCheckSection"
            class="mt-6 pt-6 border-t border-gray-200"
          >
            <h4 class="text-sm font-medium text-gray-900 mb-3">
              Alternative: Pay by Check
            </h4>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
              <p class="text-sm text-gray-900 font-medium">
                Make checks payable to:
                <span class="block font-semibold mt-1">{{ hoaName }}</span>
              </p>
              <p class="text-sm text-gray-900 font-medium mt-3">
                Mail or drop off to:
              </p>
              <div class="text-sm text-gray-700 mt-2 whitespace-pre-wrap">
                {{ hoaAddress }}
              </div>
              <p class="text-xs text-gray-600 mt-3 italic">
                Payments by check are processed offline and marked paid once
                received.
              </p>
            </div>
          </div>

          <!-- Error Message -->
          <p v-if="errorMessage" class="mt-4 text-sm text-red-600">
            {{ errorMessage }}
          </p>
        </div>

        <!-- Modal Actions -->
        <div
          class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3"
        >
          <button
            :disabled="isPayButtonDisabled"
            @click="handlePayment"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors sm:ml-3 sm:w-auto sm:text-sm"
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
            <span v-if="isLoading">Processing...</span>
            <span v-else-if="selectedMethod === 'zelle'"
              >I've sent the payment</span
            >
            <span v-else>Pay {{ formatCurrency(currentBreakdown.total) }}</span>
          </button>
          <button
            type="button"
            :disabled="isLoading"
            @click="handleClose"
            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors sm:ml-3 sm:w-auto sm:text-sm"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, withDefaults } from 'vue';
import {
  paymentsApi,
  useSettings,
  useTenantZelleQrUrl,
  type FeeCalculation,
} from '@neibrpay/api-client';
import type { InvoiceUnit } from '@neibrpay/models';

interface Props {
  isOpen: boolean;
  invoice: InvoiceUnit | null;
  hoaName: string;
  hoaAddress: string;
  /** When set, modal shows only this method's content (no others, no check section). */
  singleMethod?: 'card' | 'ach' | 'zelle';
}

interface Emits {
  (e: 'close'): void;
  (e: 'success', sessionId: string): void;
  (e: 'error', error: string): void;
}

const props = withDefaults(defineProps<Props>(), {
  hoaName: '',
  hoaAddress: '',
  singleMethod: undefined,
});
const emit = defineEmits<Emits>();

const { data: settingsData } = useSettings();
const { zelleQrUrl: tenantZelleQrUrl } = useTenantZelleQrUrl(settingsData);
const selectedMethod = ref<'card' | 'ach' | 'zelle'>('card');

/** When singleMethod is set, only that method's content is shown. */
const showMethodSelector = computed(
  () =>
    !props.singleMethod ||
    props.singleMethod === 'card' ||
    props.singleMethod === 'ach'
);
const showCheckSection = computed(() => !props.singleMethod);
const showStripeContent = computed(
  () =>
    (props.singleMethod === 'card' ||
      props.singleMethod === 'ach' ||
      selectedMethod.value === 'card' ||
      selectedMethod.value === 'ach') &&
    props.singleMethod !== 'zelle'
);
const showZelleContent = computed(
  () =>
    (props.singleMethod === 'zelle' || selectedMethod.value === 'zelle') &&
    props.singleMethod !== 'card' &&
    props.singleMethod !== 'ach'
);

const modalTitle = computed(() => {
  if (props.singleMethod === 'zelle') return 'Pay with Zelle';
  if (props.singleMethod === 'card' || props.singleMethod === 'ach')
    return 'Pay with Stripe (Card or ACH)';
  return 'Select Payment Method';
});
const modalSubtitle = computed(() => {
  if (props.singleMethod === 'zelle') return null;
  if (props.singleMethod === 'card' || props.singleMethod === 'ach')
    return 'Choose card or bank account and complete payment.';
  return "Choose how you want to pay. You'll see the processing fees for each method.";
});
const fees = ref<FeeCalculation | null>(null);
const isLoading = ref(false);
const errorMessage = ref('');

const zelleEnabled = computed(
  () => settingsData.value?.tenant?.settings?.zelle_enabled === true
);
const zelleEmail = computed(
  () => settingsData.value?.tenant?.settings?.zelle_email ?? null
);
const zellePhone = computed(
  () => settingsData.value?.tenant?.settings?.zelle_phone ?? null
);
const zelleQrUrl = computed(() => tenantZelleQrUrl.value ?? null);
const zelleInstructions = computed(
  () => settingsData.value?.tenant?.settings?.zelle_instructions ?? null
);
const showZelleOption = computed(
  () => zelleEnabled.value && (!!zelleEmail.value || !!zellePhone.value)
);

/** For Zelle, payment amount = invoice balance only (no fees). */
const zellePaymentAmount = computed(() =>
  props.invoice ? props.invoice.balance_due : 0
);

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
  }).format(amount);
};

const currentBreakdown = computed(() => {
  if (selectedMethod.value === 'zelle') {
    const amount = zellePaymentAmount.value;
    return {
      invoice_amount: amount,
      processing_fee: 0,
      total: amount,
    };
  }
  if (!fees.value) {
    return {
      invoice_amount: 0,
      processing_fee: 0,
      total: 0,
    };
  }

  const breakdown = fees.value[selectedMethod.value as 'card' | 'ach'];
  return {
    invoice_amount: fees.value.invoice_amount,
    processing_fee: breakdown.processing_fee,
    total: breakdown.total,
  };
});

const isPayButtonDisabled = computed(() => {
  if (isLoading.value) return true;
  if (selectedMethod.value === 'zelle') {
    return !props.invoice || props.invoice.balance_due <= 0;
  }
  return !fees.value;
});

const fetchFees = async () => {
  if (!props.invoice) {
    return;
  }

  try {
    const feeData = await paymentsApi.calculateFees(props.invoice.id);
    fees.value = feeData;
    errorMessage.value = '';
  } catch (error: any) {
    console.error('Error calculating fees:', error);
    errorMessage.value =
      error.response?.data?.message ||
      error.message ||
      'Failed to calculate fees. Please try again.';
  }
};

const getTodayISO = (): string => {
  const d = new Date();
  return d.toISOString().slice(0, 10);
};

const handlePayment = async () => {
  if (!props.invoice) {
    errorMessage.value = 'Invoice information is missing.';
    return;
  }

  if (props.invoice.balance_due <= 0) {
    errorMessage.value = 'This invoice has no balance due.';
    return;
  }

  if (selectedMethod.value === 'zelle') {
    isLoading.value = true;
    errorMessage.value = '';
    try {
      await paymentsApi.create(props.invoice.id, {
        amount: zellePaymentAmount.value,
        payment_method: 'zelle',
        payment_date: getTodayISO(),
      });
      emit('success', 'zelle');
      handleClose();
    } catch (error: any) {
      console.error('Zelle payment error:', error);
      errorMessage.value =
        error.response?.data?.message ||
        error.message ||
        'Failed to submit payment. Please try again.';
      emit('error', errorMessage.value);
    } finally {
      isLoading.value = false;
    }
    return;
  }

  if (!fees.value) {
    errorMessage.value = 'Fees not loaded. Please try again.';
    return;
  }

  isLoading.value = true;
  errorMessage.value = '';

  try {
    // Create Stripe Checkout session with selected payment method
    const response = await paymentsApi.createStripeCheckout(
      props.invoice.id,
      selectedMethod.value as 'card' | 'ach'
    );

    // Redirect to Stripe Checkout
    if (response.checkout_url) {
      window.location.href = response.checkout_url;
      emit('success', response.session_id);
    } else {
      throw new Error('No checkout URL received from server');
    }
  } catch (error: any) {
    console.error('Payment error:', error);
    errorMessage.value =
      error.response?.data?.message ||
      error.message ||
      'Failed to create payment session. Please try again.';
    emit('error', errorMessage.value);
  } finally {
    isLoading.value = false;
  }
};

const handleClose = () => {
  if (!isLoading.value) {
    errorMessage.value = '';
    selectedMethod.value = 'card';
    emit('close');
  }
};

// Fetch fees when modal is open and invoice is available (not on every mount)
onMounted(() => {
  if (props.isOpen && props.invoice && props.singleMethod !== 'zelle') {
    fetchFees();
  }
});

watch(
  () => props.invoice?.id,
  () => {
    if (props.isOpen) {
      fetchFees();
    }
  }
);

watch(
  () => props.isOpen,
  newVal => {
    if (newVal) {
      errorMessage.value = '';
      if (props.singleMethod) {
        selectedMethod.value = props.singleMethod;
      }
      if (props.singleMethod !== 'zelle') {
        fetchFees();
      }
    }
  }
);
</script>
