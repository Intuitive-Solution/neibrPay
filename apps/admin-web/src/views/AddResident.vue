<template>
  <div class="max-w-4xl">
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

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-6">
        Resident Information
      </h3>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Name Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="name"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Full Name <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.name,
              }"
              placeholder="Enter resident's full name"
            />
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>
        </div>

        <!-- Email Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="email"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Email Address <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.email,
              }"
              placeholder="Enter email address"
            />
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">
              {{ errors.email }}
            </p>
          </div>
        </div>

        <!-- Phone Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="phone"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Phone Number <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              required
              @input="formatPhone"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.phone,
              }"
              placeholder="555-555-5555"
              maxlength="14"
            />
            <p v-if="errors.phone" class="mt-2 text-sm text-red-600">
              {{ errors.phone }}
            </p>
            <p class="mt-2 text-sm text-gray-600">
              Please enter a valid US phone number (10 digits). Format:
              XXX-XXX-XXXX
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
          <!-- Spacer for large screens to align with form fields -->
          <div class="hidden lg:block"></div>

          <!-- Buttons Container -->
          <div class="lg:col-span-2 flex flex-col sm:flex-row gap-4">
            <!-- Primary Button -->
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                {{
                  isSubmitting
                    ? 'Saving...'
                    : isEditMode
                      ? 'Update Resident'
                      : 'Add Resident'
                }}
              </span>
              <span v-else>{{
                isEditMode ? 'Update Resident' : 'Add Resident'
              }}</span>
            </button>

            <!-- Cancel Button -->
            <button
              type="button"
              @click="goBack"
              class="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
            >
              Cancel
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Tabs Section (only in edit mode) -->
    <div v-if="isEditMode" class="mt-8">
      <!-- Single White Container with Tabs and Content -->
      <div class="bg-white rounded-lg shadow">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6 pt-4">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-primary text-primary'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Units Tab -->
          <div v-if="activeTab === 'units'" class="space-y-4">
            <!-- Search Bar and Add Button -->
            <div class="flex items-center justify-between">
              <!-- Search Box -->
              <div class="relative flex-1 max-w-md">
                <div
                  class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                  <svg
                    class="h-5 w-5 text-gray-400"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm"
                  placeholder="Search..."
                />
              </div>

              <!-- Add Button -->
              <div class="ml-4">
                <button
                  type="button"
                  class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                  <svg
                    class="-ml-1 mr-2 h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                    ></path>
                  </svg>
                  Add
                </button>
              </div>
            </div>

            <!-- Units Table -->
            <div class="overflow-hidden sm:rounded-md">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-300">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Title
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Balance Amount
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Balance As Of Date
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="unit in filteredUnits" :key="unit.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                    >
                      {{ unit.title }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      ${{ unit.balance_amount }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ unit.balance_as_of_date }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      <button
                        @click="removeUnit(unit)"
                        class="text-red-600 hover:text-red-800"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                  <!-- Empty State -->
                  <tr v-if="filteredUnits.length === 0">
                    <td
                      colspan="4"
                      class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                      <div class="flex flex-col items-center">
                        <svg
                          class="h-12 w-12 text-gray-400 mb-2"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                          />
                        </svg>
                        <p class="text-gray-500">
                          No units assigned to this resident
                        </p>
                        <p class="text-gray-400 text-xs mt-1">
                          Click "Add" to assign units
                        </p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Invoices Tab -->
          <div v-if="activeTab === 'invoices'" class="space-y-4">
            <!-- Invoices Table -->
            <div class="overflow-hidden sm:rounded-md">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Date
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Description
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Amount
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="invoice in invoices" :key="invoice.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                    >
                      {{ invoice.date }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ invoice.description }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      ${{ invoice.amount }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          invoice.status === 'paid'
                            ? 'bg-green-100 text-green-800'
                            : invoice.status === 'pending'
                              ? 'bg-yellow-100 text-yellow-800'
                              : 'bg-red-100 text-red-800',
                        ]"
                      >
                        {{ invoice.status }}
                      </span>
                    </td>
                  </tr>
                  <!-- Empty State -->
                  <tr v-if="invoices.length === 0">
                    <td
                      colspan="4"
                      class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                      <div class="flex flex-col items-center">
                        <svg
                          class="h-12 w-12 text-gray-400 mb-2"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                          />
                        </svg>
                        <p class="text-gray-500">No invoices found</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- History Tab -->
          <div v-if="activeTab === 'history'" class="space-y-4">
            <!-- History Table -->
            <div class="overflow-hidden sm:rounded-md">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Date
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Description
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Remark
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="history in residentHistory" :key="history.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                    >
                      {{ history.date }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ history.description }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ history.remark }}
                    </td>
                  </tr>
                  <!-- Empty State -->
                  <tr v-if="residentHistory.length === 0">
                    <td
                      colspan="3"
                      class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                      <div class="flex flex-col items-center">
                        <svg
                          class="h-12 w-12 text-gray-400 mb-2"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                          />
                        </svg>
                        <p class="text-gray-500">No history found</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  useCreateResident,
  useUpdateResident,
  useResident,
} from '../composables/useResidents';
import {
  validateResidentForm,
  validateUpdateResidentForm,
} from '@neibrpay/models';
import type { ResidentFormData, ResidentFormErrors } from '@neibrpay/models';

const route = useRoute();
const router = useRouter();

// Determine if we're in edit mode
const residentId = computed(() => {
  const id = route.params.id;
  return id ? parseInt(id as string) : null;
});

const isEditMode = computed(() => !!residentId.value);

// Form data
const form = ref<ResidentFormData>({
  name: '',
  email: '',
  phone: '',
});

// Form errors
const errors = ref<ResidentFormErrors>({});

// Loading states
const isSubmitting = ref(false);

// Tab state
const activeTab = ref('units');
const searchQuery = ref('');

// Tabs configuration
const tabs = [
  { id: 'units', name: 'Units' },
  { id: 'invoices', name: 'Invoices' },
  { id: 'history', name: 'History' },
];

// Sample data for tabs (replace with actual API calls)
const units = ref([
  {
    id: 1,
    title: 'Unit 101',
    balance_amount: '3500.00',
    balance_as_of_date: '04/10/2025',
  },
  {
    id: 2,
    title: 'Unit 102',
    balance_amount: '2800.50',
    balance_as_of_date: '04/10/2025',
  },
]);

const invoices = ref([
  {
    id: 1,
    date: '2024-01-15',
    description: 'Monthly HOA Fee',
    amount: '150.00',
    status: 'paid',
  },
  {
    id: 2,
    date: '2024-01-10',
    description: 'Late Fee',
    amount: '25.00',
    status: 'pending',
  },
]);

const residentHistory = ref([
  {
    id: 1,
    date: '2024-01-15',
    description: 'Account created',
    remark: 'Initial setup',
  },
  {
    id: 2,
    date: '2024-01-10',
    description: 'Profile updated',
    remark: 'Phone number changed',
  },
]);

// Computed properties
const filteredUnits = computed(() => {
  if (!searchQuery.value) return units.value;
  return units.value.filter(
    (unit: any) =>
      unit.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      unit.balance_amount.includes(searchQuery.value) ||
      unit.balance_as_of_date.includes(searchQuery.value)
  );
});

// Methods
const removeUnit = (unit: any) => {
  const index = units.value.findIndex((u: any) => u.id === unit.id);
  if (index > -1) {
    units.value.splice(index, 1);
  }
};

// Queries and mutations
const { data: resident, isLoading: isLoadingResident } = useResident(
  residentId.value!
);
const createResidentMutation = useCreateResident();
const updateResidentMutation = useUpdateResident();

// Load resident data for editing
onMounted(() => {
  if (isEditMode.value && resident.value) {
    form.value = {
      name: resident.value.name,
      email: resident.value.email,
      phone: resident.value.phone,
    };
  }
});

// Watch for resident data changes
watch(resident, (newResident: any) => {
  if (newResident && isEditMode.value) {
    form.value = {
      name: newResident.name,
      email: newResident.email,
      phone: newResident.phone,
    };
  }
});

// Phone formatting - match signup form format (XXX-XXX-XXXX)
const formatPhone = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = target.value;

  // Remove all non-numeric characters
  const phoneNumber = value.replace(/\D/g, '');

  // Limit to 10 digits
  const limitedPhoneNumber = phoneNumber.substring(0, 10);

  // Format as XXX-XXX-XXXX
  let formatted = '';
  if (limitedPhoneNumber.length >= 6) {
    formatted = `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3, 6)}-${limitedPhoneNumber.substring(6)}`;
  } else if (limitedPhoneNumber.length >= 3) {
    formatted = `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3)}`;
  } else {
    formatted = limitedPhoneNumber;
  }

  form.value.phone = formatted;
  target.value = formatted;
};

// Form submission
const handleSubmit = async () => {
  // Clear previous errors
  errors.value = {};

  // Validate form
  const validation = isEditMode.value
    ? validateUpdateResidentForm(form.value)
    : validateResidentForm(form.value);

  if (!validation.success) {
    errors.value = validation.errors || {};
    return;
  }

  isSubmitting.value = true;

  try {
    if (isEditMode.value && residentId.value) {
      await updateResidentMutation.mutateAsync({
        id: residentId.value,
        data: validation.data as any,
      });
    } else {
      await createResidentMutation.mutateAsync(validation.data as any);
    }

    // Success - redirect back to people page
    router.push('/people');
  } catch (error: any) {
    // Handle API errors
    if (error.errors) {
      errors.value = error.errors;
    } else {
      errors.value.general = error.message || 'An unexpected error occurred';
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Navigation
const goBack = () => {
  router.push('/people');
};

// Show loading state while fetching resident data
if (isEditMode.value && isLoadingResident.value) {
  // You could show a loading spinner here
}
</script>
