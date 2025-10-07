<template>
  <div class="max-w-7xl bg-white p-6">
    <!-- Error Message -->
    <div
      v-if="errors.general"
      class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ errors.general }}</p>
        </div>
      </div>
    </div>

    <!-- Three Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Column 1: Units -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-6">Units</h3>

        <div class="space-y-4">
          <!-- Unit Selection -->
          <div>
            <label
              for="unit"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Unit <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <select
                id="unit"
                v-model="form.unit_id"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.unit_id,
                }"
              >
                <option value="">Select a unit</option>
                <option v-for="unit in units" :key="unit.id" :value="unit.id">
                  {{ unit.title }}
                </option>
              </select>
              <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
            <p v-if="errors.unit_id" class="mt-2 text-sm text-red-600">
              {{ errors.unit_id }}
            </p>
          </div>
        </div>
      </div>

      <!-- Column 2: Scheduling/Frequency -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-6">Scheduling</h3>

        <div class="space-y-4">
          <!-- Frequency -->
          <div>
            <label
              for="frequency"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Frequency <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <select
                id="frequency"
                v-model="form.frequency"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.frequency,
                }"
              >
                <option value="monthly">Monthly</option>
                <option value="weekly">Weekly</option>
                <option value="quarterly">Quarterly</option>
                <option value="yearly">Yearly</option>
                <option value="one-time">One Time</option>
              </select>
              <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
            <p v-if="errors.frequency" class="mt-2 text-sm text-red-600">
              {{ errors.frequency }}
            </p>
          </div>

          <!-- Start Date -->
          <div>
            <label
              for="start_date"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Start Date <span class="text-red-500">*</span>
            </label>
            <div class="relative">
              <input
                id="start_date"
                v-model="form.start_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.start_date,
                }"
              />
              <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
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
            </div>
            <p v-if="errors.start_date" class="mt-2 text-sm text-red-600">
              {{ errors.start_date }}
            </p>
          </div>

          <!-- Remaining Cycles -->
          <div>
            <label
              for="remaining_cycles"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Remaining Cycles
            </label>
            <div class="relative">
              <select
                id="remaining_cycles"
                v-model="form.remaining_cycles"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.remaining_cycles,
                }"
              >
                <option value="endless">Endless</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="6">6</option>
                <option value="12">12</option>
                <option value="24">24</option>
              </select>
              <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
            <p v-if="errors.remaining_cycles" class="mt-2 text-sm text-red-600">
              {{ errors.remaining_cycles }}
            </p>
          </div>

          <!-- Due Date -->
          <div>
            <label
              for="due_date"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Due Date
            </label>
            <div class="relative">
              <select
                id="due_date"
                v-model="form.due_date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.due_date,
                }"
              >
                <option value="use_payment_terms">Use Payment Terms</option>
                <option value="net_15">Net 15</option>
                <option value="net_30">Net 30</option>
                <option value="net_45">Net 45</option>
                <option value="net_60">Net 60</option>
                <option value="due_on_receipt">Due on Receipt</option>
              </select>
              <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
            <p v-if="errors.due_date" class="mt-2 text-sm text-red-600">
              {{ errors.due_date }}
            </p>
          </div>
        </div>
      </div>

      <!-- Column 3: Invoice Details -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-6">Invoice Details</h3>

        <div class="space-y-4">
          <!-- Invoice # -->
          <div>
            <label
              for="invoice_number"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Invoice #
            </label>
            <input
              id="invoice_number"
              v-model="form.invoice_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.invoice_number,
              }"
              placeholder="Auto-generated if empty"
            />
            <p v-if="errors.invoice_number" class="mt-2 text-sm text-red-600">
              {{ errors.invoice_number }}
            </p>
          </div>

          <!-- PO # -->
          <div>
            <label
              for="po_number"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              PO #
            </label>
            <input
              id="po_number"
              v-model="form.po_number"
              type="text"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.po_number,
              }"
              placeholder="Purchase order number"
            />
            <p v-if="errors.po_number" class="mt-2 text-sm text-red-600">
              {{ errors.po_number }}
            </p>
          </div>

          <!-- Discount -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Discount
            </label>
            <div class="flex gap-2">
              <div class="flex-1">
                <input
                  v-model="form.discount_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.discount_amount,
                  }"
                  placeholder="0.00"
                />
              </div>
              <div class="w-24">
                <select
                  v-model="form.discount_type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.discount_type,
                  }"
                >
                  <option value="amount">Amount</option>
                  <option value="percentage">%</option>
                </select>
              </div>
            </div>
            <p
              v-if="errors.discount_amount || errors.discount_type"
              class="mt-2 text-sm text-red-600"
            >
              {{ errors.discount_amount || errors.discount_type }}
            </p>
          </div>

          <!-- Auto Bill -->
          <div>
            <label
              for="auto_bill"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Auto Bill
            </label>
            <div class="relative">
              <select
                id="auto_bill"
                v-model="form.auto_bill"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm appearance-none bg-white"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.auto_bill,
                }"
              >
                <option value="disabled">Disabled</option>
                <option value="enabled">Enabled</option>
                <option value="on_due_date">On Due Date</option>
                <option value="on_send">On Send</option>
              </select>
              <div
                class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"
                  />
                </svg>
              </div>
            </div>
            <p v-if="errors.auto_bill" class="mt-2 text-sm text-red-600">
              {{ errors.auto_bill }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-8 flex justify-end space-x-4">
      <button
        type="button"
        @click="handleCancel"
        class="px-6 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
      >
        Cancel
      </button>
      <button
        type="button"
        @click="handleSubmit"
        :disabled="isSubmitting"
        class="px-6 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
      >
        <span v-if="isSubmitting" class="flex items-center">
          <svg
            class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
            xmlns="http://www.w3.org/2000/svg"
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
          Creating...
        </span>
        <span v-else>Create Invoice</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';

// Router
const router = useRouter();

// Form data
const form = ref({
  unit_id: '',
  frequency: 'monthly',
  start_date: '',
  remaining_cycles: 'endless',
  due_date: 'use_payment_terms',
  invoice_number: '',
  po_number: '',
  discount_amount: '',
  discount_type: 'amount',
  auto_bill: 'disabled',
});

// Form errors
const errors = ref({
  general: '',
  unit_id: '',
  frequency: '',
  start_date: '',
  remaining_cycles: '',
  due_date: '',
  invoice_number: '',
  po_number: '',
  discount_amount: '',
  discount_type: '',
  auto_bill: '',
});

// Loading state
const isSubmitting = ref(false);

// Dummy units data (replace with API call later)
const units = ref([
  { id: 1, title: 'Unit 101' },
  { id: 2, title: 'Unit 102' },
  { id: 3, title: 'Unit 201' },
  { id: 4, title: 'Unit 202' },
  { id: 5, title: 'Unit 301' },
]);

// Methods
const handleSubmit = async () => {
  // Clear previous errors
  errors.value = {
    general: '',
    unit_id: '',
    frequency: '',
    start_date: '',
    remaining_cycles: '',
    due_date: '',
    invoice_number: '',
    po_number: '',
    discount_amount: '',
    discount_type: '',
    auto_bill: '',
  };

  // Basic validation
  if (!form.value.unit_id) {
    errors.value.unit_id = 'Please select a unit';
    return;
  }

  if (!form.value.frequency) {
    errors.value.frequency = 'Please select a frequency';
    return;
  }

  if (!form.value.start_date) {
    errors.value.start_date = 'Please select a start date';
    return;
  }

  isSubmitting.value = true;

  try {
    // TODO: Implement API call to create invoice
    // await invoicesApi.createInvoice(form.value);

    // For now, simulate API call
    await new Promise(resolve => setTimeout(resolve, 1000));

    // Redirect to invoices list or show success message
    router.push('/invoices');
  } catch (error) {
    console.error('Error creating invoice:', error);
    errors.value.general = 'Failed to create invoice. Please try again.';
  } finally {
    isSubmitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/invoices');
};

// Initialize form with default values
onMounted(() => {
  // Set default start date to today
  const today = new Date();
  form.value.start_date = today.toISOString().split('T')[0];
});
</script>
