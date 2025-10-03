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
      <!-- Name Field -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
        <label
          for="name"
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          Full Name <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2">
          <input
            id="name"
            v-model="form.name"
            type="text"
            required
            class="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
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
          class="block text-sm font-medium text-text-primary lg:pt-3"
        >
          Email Address <span class="text-red-500">*</span>
        </label>
        <div class="lg:col-span-2">
          <input
            id="email"
            v-model="form.email"
            type="email"
            required
            class="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
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
          class="block text-sm font-medium text-text-primary lg:pt-3"
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
            class="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
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
            class="flex-1 flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
            class="flex-1 flex justify-center py-3 px-4 border border-neutral-300 rounded-lg shadow-sm bg-white text-sm font-medium text-text-primary hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
          >
            Cancel
          </button>
        </div>
      </div>
    </form>
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
watch(resident, newResident => {
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
