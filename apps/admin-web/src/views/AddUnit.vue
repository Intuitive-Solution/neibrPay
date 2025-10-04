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
    <form @submit.prevent="handleSubmit" class="space-y-6">
      <!-- Title Field -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
        <label
          for="title"
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          Title <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2">
          <input
            id="title"
            v-model="form.title"
            type="text"
            required
            class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
            :class="{
              'border-red-300 focus:ring-red-500 focus:border-red-500':
                errors.title,
            }"
            placeholder="Enter unit title (e.g., Unit 101, Apartment A)"
          />
          <p v-if="errors.title" class="mt-2 text-sm text-red-600">
            {{ errors.title }}
          </p>
        </div>
      </div>

      <!-- Address Field -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
        <label
          for="address"
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          Address <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2">
          <input
            id="address"
            v-model="form.address"
            type="text"
            required
            class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
            :class="{
              'border-red-300 focus:ring-red-500 focus:border-red-500':
                errors.address,
            }"
            placeholder="Enter full address"
          />
          <p v-if="errors.address" class="mt-2 text-sm text-red-600">
            {{ errors.address }}
          </p>
        </div>
      </div>

      <!-- City Field -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
        <label
          for="city"
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          City <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2">
          <input
            id="city"
            v-model="form.city"
            type="text"
            required
            class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
            :class="{
              'border-red-300 focus:ring-red-500 focus:border-red-500':
                errors.city,
            }"
            placeholder="Enter city"
          />
          <p v-if="errors.city" class="mt-2 text-sm text-red-600">
            {{ errors.city }}
          </p>
        </div>
      </div>

      <!-- State and ZIP Code Fields -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
        <label
          for="state"
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          State <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <select
              id="state"
              v-model="form.state"
              required
              class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.state,
              }"
            >
              <option value="">Select State</option>
              <option
                v-for="state in US_STATES"
                :key="state.value"
                :value="state.value"
              >
                {{ state.label }}
              </option>
            </select>
            <p v-if="errors.state" class="mt-2 text-sm text-red-600">
              {{ errors.state }}
            </p>
          </div>
          <div>
            <input
              id="zip_code"
              v-model="form.zip_code"
              type="text"
              required
              @input="formatZipCode"
              class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.zip_code,
              }"
              placeholder="12345"
              maxlength="10"
            />
            <p v-if="errors.zip_code" class="mt-2 text-sm text-red-600">
              {{ errors.zip_code }}
            </p>
          </div>
        </div>
      </div>

      <!-- Starting Balance Field -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
        <label
          for="starting_balance"
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          Starting Balance <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2">
          <div class="relative">
            <div
              class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
            >
              <span class="text-gray-500 sm:text-sm">$</span>
            </div>
            <input
              id="starting_balance"
              v-model="form.starting_balance"
              type="number"
              step="0.01"
              min="-999999.99"
              max="999999.99"
              required
              class="w-full pl-6 pr-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.starting_balance,
              }"
              placeholder="0.00"
            />
          </div>
          <p v-if="errors.starting_balance" class="mt-2 text-sm text-red-600">
            {{ errors.starting_balance }}
          </p>
          <p class="mt-2 text-sm text-gray-600">
            Enter the starting balance for this unit (can be negative)
          </p>
        </div>
      </div>

      <!-- Balance As Of Date Field -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
        <label
          for="balance_as_of_date"
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          Balance As Of Date <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2">
          <input
            id="balance_as_of_date"
            v-model="form.balance_as_of_date"
            type="date"
            required
            class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
            :class="{
              'border-red-300 focus:ring-red-500 focus:border-red-500':
                errors.balance_as_of_date,
            }"
          />
          <p v-if="errors.balance_as_of_date" class="mt-2 text-sm text-red-600">
            {{ errors.balance_as_of_date }}
          </p>
          <p class="mt-2 text-sm text-gray-600">
            The date when the starting balance was recorded
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
            class="flex-1 flex justify-center py-2 px-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                    ? 'Update Unit'
                    : 'Add Unit'
              }}
            </span>
            <span v-else>{{ isEditMode ? 'Update Unit' : 'Add Unit' }}</span>
          </button>

          <!-- Cancel Button -->
          <button
            type="button"
            @click="goBack"
            class="flex-1 flex justify-center py-2 px-3 border border-neutral-300 rounded-lg shadow-sm bg-white text-sm font-medium text-text-primary hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
          >
            Cancel
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCreateUnit, useUpdateUnit, useUnit } from '../composables/useUnits';
import { US_STATES } from '@neibrpay/models';
import type { UnitFormData, UnitFormErrors } from '@neibrpay/models';

// Router
const route = useRoute();
const router = useRouter();

// Computed
const isEditMode = computed(() => route.name === 'EditUnit');
const unitId = computed(() => {
  const id = route.params.id;
  return id ? parseInt(id as string) : null;
});

// State
const isSubmitting = ref(false);
const errors = ref<UnitFormErrors>({});

const form = ref<UnitFormData>({
  title: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  starting_balance: 0,
  balance_as_of_date: new Date().toISOString().split('T')[0], // Today's date
});

// Queries and mutations
const createUnitMutation = useCreateUnit();
const updateUnitMutation = useUpdateUnit();
const { data: existingUnit } = useUnit(unitId.value || 0);

// Methods
const goBack = () => {
  router.push('/units');
};

const formatZipCode = (event: Event) => {
  const target = event.target as HTMLInputElement;
  let value = target.value.replace(/\D/g, '');

  if (value.length > 5) {
    value = value.slice(0, 5) + '-' + value.slice(5, 9);
  }

  form.value.zip_code = value;
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  errors.value = {};

  try {
    if (isEditMode.value && unitId.value) {
      await updateUnitMutation.mutateAsync({
        id: unitId.value,
        data: form.value,
      });
    } else {
      await createUnitMutation.mutateAsync(form.value);
    }

    router.push('/units');
  } catch (error: any) {
    console.error('Form submission error:', error);

    if (error.errors) {
      errors.value = error.errors;
    } else {
      errors.value.general = error.message || 'An unexpected error occurred';
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Load existing unit data for editing
onMounted(() => {
  if (isEditMode.value && existingUnit.value) {
    form.value = {
      title: existingUnit.value.title,
      address: existingUnit.value.address,
      city: existingUnit.value.city,
      state: existingUnit.value.state,
      zip_code: existingUnit.value.zip_code,
      starting_balance: existingUnit.value.starting_balance,
      balance_as_of_date: existingUnit.value.balance_as_of_date.split('T')[0],
    };
  }
});
</script>
