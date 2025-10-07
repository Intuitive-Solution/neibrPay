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
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Units <span class="text-red-500">*</span>
            </label>

            <!-- Loading State -->
            <div
              v-if="isLoadingUnits"
              class="flex items-center justify-center py-8"
            >
              <div
                class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
              ></div>
              <span class="ml-2 text-gray-600">Loading units...</span>
            </div>

            <!-- Error State -->
            <div
              v-else-if="unitsError"
              class="p-4 text-center text-red-600 bg-red-50 rounded-lg"
            >
              <p>Failed to load units. Please try again.</p>
            </div>

            <!-- Searchable Multiselect Dropdown -->
            <div v-else ref="dropdownRef" class="relative">
              <!-- Main Input Field -->
              <div
                class="relative min-h-[42px] w-full px-3 py-2 border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-primary focus-within:border-primary transition-colors duration-200 cursor-pointer"
                :class="{
                  'border-red-300 focus-within:ring-red-500 focus-within:border-red-500':
                    errors.unit_ids,
                }"
                @click="toggleDropdown"
              >
                <!-- Selected Units as Chips -->
                <div class="flex flex-wrap gap-2 items-center min-h-[26px]">
                  <div
                    v-for="unitId in form.unit_ids"
                    :key="unitId"
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    {{ getUnitTitle(unitId) }}
                    <button
                      type="button"
                      @click.stop="removeUnit(unitId)"
                      class="ml-1.5 inline-flex items-center justify-center w-4 h-4 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary"
                    >
                      <svg
                        class="w-2.5 h-2.5"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                      >
                        <path
                          fill-rule="evenodd"
                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                          clip-rule="evenodd"
                        />
                      </svg>
                    </button>
                  </div>

                  <!-- Search Input -->
                  <input
                    v-model="searchQuery"
                    type="text"
                    placeholder="Search units..."
                    class="flex-1 min-w-[120px] border-0 outline-none bg-transparent text-sm placeholder-gray-500"
                    @click.stop
                    @keydown.escape="closeDropdown"
                    @keydown.down.prevent="navigateDown"
                    @keydown.up.prevent="navigateUp"
                    @keydown.enter.prevent="selectHighlighted"
                  />
                </div>

                <!-- Action Icons -->
                <div
                  class="absolute inset-y-0 right-0 flex items-center pr-3 space-x-1"
                >
                  <!-- Clear All Button -->
                  <button
                    v-if="form.unit_ids.length > 0"
                    type="button"
                    @click.stop="clearAllUnits"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary"
                  >
                    <svg
                      class="w-3 h-3 text-gray-400"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </button>

                  <!-- Dropdown Toggle -->
                  <button
                    type="button"
                    @click.stop="toggleDropdown"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-primary"
                  >
                    <svg
                      class="w-3 h-3 text-gray-400 transition-transform duration-200"
                      :class="{ 'rotate-180': isDropdownOpen }"
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
                  </button>
                </div>
              </div>

              <!-- Dropdown Menu -->
              <div
                v-if="isDropdownOpen"
                class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"
              >
                <!-- Select All Option -->
                <div
                  class="px-3 py-2 text-sm font-medium text-gray-700 border-b border-gray-100 cursor-pointer hover:bg-gray-50"
                  @click="toggleSelectAll"
                >
                  <div class="flex items-center">
                    <input
                      :checked="isAllSelected"
                      type="checkbox"
                      class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                      @click.stop
                    />
                    <span class="ml-2">Select all options</span>
                  </div>
                </div>

                <!-- Filtered Units List -->
                <div
                  v-if="filteredUnits.length === 0"
                  class="px-3 py-2 text-sm text-gray-500 text-center"
                >
                  No units found matching "{{ searchQuery }}"
                </div>

                <div v-else class="divide-y divide-gray-100">
                  <div
                    v-for="(unit, index) in filteredUnits"
                    :key="unit.id"
                    class="px-3 py-2 cursor-pointer hover:bg-gray-50"
                    :class="{ 'bg-gray-50': highlightedIndex === index }"
                    @click="toggleUnitSelection(unit.id)"
                    @mouseenter="highlightedIndex = index"
                  >
                    <div class="flex items-center">
                      <input
                        :checked="form.unit_ids.includes(unit.id)"
                        type="checkbox"
                        class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                        @click.stop="toggleUnitSelection(unit.id)"
                      />
                      <div class="ml-2 flex-1">
                        <div class="text-sm font-medium text-gray-900">
                          {{ unit.title }}
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ unit.resident_name }} • {{ unit.address }},
                          {{ unit.city }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Error Message -->
            <p v-if="errors.unit_ids" class="mt-2 text-sm text-red-600">
              {{ errors.unit_ids }}
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
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import { useUnitsForInvoices } from '../composables/useUnits';

// Router
const router = useRouter();

// Form data
const form = ref({
  unit_ids: [], // Changed to support multiple units
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
  unit_ids: '', // Changed to match form data
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

// Fetch units with resident information
const {
  data: units,
  isLoading: isLoadingUnits,
  error: unitsError,
} = useUnitsForInvoices();

// Dropdown state
const isDropdownOpen = ref(false);
const searchQuery = ref('');
const highlightedIndex = ref(-1);
const dropdownRef = ref<HTMLElement | null>(null);

// Computed properties
const filteredUnits = computed(() => {
  if (!units.value) return [];
  if (!searchQuery.value) return units.value;

  const query = searchQuery.value.toLowerCase();
  return units.value.filter(
    (unit: any) =>
      unit.title.toLowerCase().includes(query) ||
      unit.resident_name.toLowerCase().includes(query) ||
      unit.address.toLowerCase().includes(query) ||
      unit.city.toLowerCase().includes(query)
  );
});

const isAllSelected = computed(() => {
  if (!units.value || units.value.length === 0) return false;
  return units.value.every((unit: any) =>
    form.value.unit_ids.includes(unit.id)
  );
});

// Methods
const getUnitTitle = (unitId: number) => {
  const unit = units.value?.find((u: any) => u.id === unitId);
  return unit ? unit.title : `Unit ${unitId}`;
};

const toggleUnitSelection = (unitId: number) => {
  const index = form.value.unit_ids.indexOf(unitId);
  if (index > -1) {
    form.value.unit_ids.splice(index, 1);
  } else {
    form.value.unit_ids.push(unitId);
  }
};

const removeUnit = (unitId: number) => {
  const index = form.value.unit_ids.indexOf(unitId);
  if (index > -1) {
    form.value.unit_ids.splice(index, 1);
  }
};

const clearAllUnits = () => {
  form.value.unit_ids = [];
};

const toggleDropdown = () => {
  isDropdownOpen.value = !isDropdownOpen.value;
  if (isDropdownOpen.value) {
    highlightedIndex.value = -1;
  }
};

const closeDropdown = () => {
  isDropdownOpen.value = false;
  highlightedIndex.value = -1;
  searchQuery.value = '';
};

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    form.value.unit_ids = [];
  } else {
    form.value.unit_ids = units.value?.map((unit: any) => unit.id) || [];
  }
};

const navigateDown = () => {
  if (highlightedIndex.value < filteredUnits.value.length - 1) {
    highlightedIndex.value++;
  }
};

const navigateUp = () => {
  if (highlightedIndex.value > 0) {
    highlightedIndex.value--;
  }
};

const selectHighlighted = () => {
  if (
    highlightedIndex.value >= 0 &&
    highlightedIndex.value < filteredUnits.value.length
  ) {
    const unit = filteredUnits.value[highlightedIndex.value];
    toggleUnitSelection(unit.id);
  }
};

// Click outside handler
const handleClickOutside = (event: Event) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    closeDropdown();
  }
};

const handleSubmit = async () => {
  // Clear previous errors
  errors.value = {
    general: '',
    unit_ids: '',
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
  if (form.value.unit_ids.length === 0) {
    errors.value.unit_ids = 'Please select at least one unit';
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

  // Add click outside listener
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  // Remove click outside listener
  document.removeEventListener('click', handleClickOutside);
});
</script>
