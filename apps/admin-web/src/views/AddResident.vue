<template>
  <div class="max-w-2xl mx-auto">
    <div class="mb-6">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            {{ isEditMode ? 'Edit Resident' : 'Add New Resident' }}
          </h1>
          <p class="mt-1 text-sm text-gray-600">
            {{
              isEditMode
                ? 'Update resident information'
                : 'Add a new resident to your community'
            }}
          </p>
        </div>
        <button
          @click="goBack"
          class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
        >
          <svg
            class="-ml-0.5 mr-2 h-4 w-4"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 19l-7-7m0 0l7-7m-7 7h18"
            />
          </svg>
          Back
        </button>
      </div>
    </div>

    <div class="bg-white shadow rounded-lg">
      <form @submit.prevent="handleSubmit" class="p-6 space-y-6">
        <!-- Name Field -->
        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">
            Full Name <span class="text-red-500">*</span>
          </label>
          <div class="mt-1">
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.name,
              }"
              placeholder="Enter resident's full name"
            />
            <p v-if="errors.name" class="mt-1 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>
        </div>

        <!-- Email Field -->
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">
            Email Address <span class="text-red-500">*</span>
          </label>
          <div class="mt-1">
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.email,
              }"
              placeholder="Enter email address"
            />
            <p v-if="errors.email" class="mt-1 text-sm text-red-600">
              {{ errors.email }}
            </p>
          </div>
        </div>

        <!-- Phone Field -->
        <div>
          <label for="phone" class="block text-sm font-medium text-gray-700">
            Phone Number <span class="text-red-500">*</span>
          </label>
          <div class="mt-1">
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              required
              @input="formatPhone"
              class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-gray-300 rounded-md"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.phone,
              }"
              placeholder="(XXX) XXX-XXXX"
              maxlength="14"
            />
            <p v-if="errors.phone" class="mt-1 text-sm text-red-600">
              {{ errors.phone }}
            </p>
            <p class="mt-1 text-xs text-gray-500">Format: (XXX) XXX-XXXX</p>
          </div>
        </div>

        <!-- General Error -->
        <div v-if="errors.general" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-red-400"
                fill="currentColor"
                viewBox="0 0 20 20"
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

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
          <button
            type="button"
            @click="goBack"
            class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg
              v-if="isSubmitting"
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
            {{
              isSubmitting
                ? 'Saving...'
                : isEditMode
                  ? 'Update Resident'
                  : 'Add Resident'
            }}
          </button>
        </div>
      </form>
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
  formatPhoneNumber,
  validateResidentForm,
  validateUpdateResidentForm,
} from '@neibrpay/models';
import type {
  ResidentFormData,
  ResidentFormErrors,
  CreateResidentRequest,
} from '@neibrpay/models';

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

// Phone formatting
const formatPhone = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const formatted = formatPhoneNumber(target.value);
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
        data: validation.data!,
      });
    } else {
      await createResidentMutation.mutateAsync(
        validation.data as CreateResidentRequest
      );
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
