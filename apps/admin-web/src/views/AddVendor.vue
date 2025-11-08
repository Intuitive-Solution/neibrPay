<template>
  <div class="max-w-4xl">
    <!-- Header Section -->
    <div class="card mb-6">
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
        <div class="mb-4 lg:mb-0">
          <div class="flex items-center gap-4 mb-2">
            <h1 class="text-2xl font-bold text-gray-900">
              {{ isEdit ? 'Edit Vendor' : 'Add New Vendor' }}
            </h1>
          </div>
          <p class="text-gray-600">
            {{
              isEdit
                ? 'Update vendor information and details'
                : 'Create a new vendor for your HOA management'
            }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="handleCancel" class="btn-outline">
            <svg
              class="w-4 h-4 mr-2"
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
            Back to Vendors
          </button>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="submitError"
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
          <p class="text-sm text-red-800">{{ submitError }}</p>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="card">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Name Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="name"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Name <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.name,
              }"
              placeholder="Enter vendor name"
            />
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>
        </div>

        <!-- Description Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="description"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Description
          </label>
          <div class="lg:col-span-2">
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.description,
              }"
              placeholder="Enter vendor description (optional)"
            />
            <p v-if="errors.description" class="mt-2 text-sm text-red-600">
              {{ errors.description }}
            </p>
          </div>
        </div>

        <!-- Category Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="category"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Category <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <select
              id="category"
              v-model="form.category"
              required
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.category,
              }"
            >
              <option value="">Select a category</option>
              <option
                v-for="option in categoryOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
            <p v-if="errors.category" class="mt-2 text-sm text-red-600">
              {{ errors.category }}
            </p>
          </div>
        </div>

        <!-- EIN Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="ein"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            EIN
          </label>
          <div class="lg:col-span-2">
            <input
              id="ein"
              v-model="form.ein"
              type="text"
              maxlength="20"
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.ein,
              }"
              placeholder="12-3456789 (optional)"
            />
            <p v-if="errors.ein" class="mt-2 text-sm text-red-600">
              {{ errors.ein }}
            </p>
            <p class="mt-1 text-xs text-gray-500">
              Employer Identification Number (optional)
            </p>
          </div>
        </div>

        <!-- Street Address Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="street_address"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Street Address <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="street_address"
              v-model="form.street_address"
              type="text"
              required
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.street_address,
              }"
              placeholder="123 Main Street"
            />
            <p v-if="errors.street_address" class="mt-2 text-sm text-red-600">
              {{ errors.street_address }}
            </p>
          </div>
        </div>

        <!-- City Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="city"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            City <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="city"
              v-model="form.city"
              type="text"
              required
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.city,
              }"
              placeholder="New York"
            />
            <p v-if="errors.city" class="mt-2 text-sm text-red-600">
              {{ errors.city }}
            </p>
          </div>
        </div>

        <!-- State Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="state"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            State <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <select
              id="state"
              v-model="form.state"
              required
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.state,
              }"
            >
              <option value="">Select a state</option>
              <option
                v-for="option in stateOptions"
                :key="option.value"
                :value="option.value"
              >
                {{ option.label }}
              </option>
            </select>
            <p v-if="errors.state" class="mt-2 text-sm text-red-600">
              {{ errors.state }}
            </p>
          </div>
        </div>

        <!-- ZIP Code Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="zip_code"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            ZIP Code <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="zip_code"
              v-model="form.zip_code"
              type="text"
              required
              pattern="\d{5}(-\d{4})?"
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.zip_code,
              }"
              placeholder="12345 or 12345-6789"
            />
            <p v-if="errors.zip_code" class="mt-2 text-sm text-red-600">
              {{ errors.zip_code }}
            </p>
          </div>
        </div>

        <!-- Website Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="website"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Website
          </label>
          <div class="lg:col-span-2">
            <input
              id="website"
              v-model="form.website"
              type="url"
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.website,
              }"
              placeholder="https://example.com"
            />
            <p v-if="errors.website" class="mt-2 text-sm text-red-600">
              {{ errors.website }}
            </p>
          </div>
        </div>

        <!-- Notes Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="notes"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Notes
          </label>
          <div class="lg:col-span-2">
            <textarea
              id="notes"
              v-model="form.notes"
              rows="4"
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.notes,
              }"
              placeholder="Additional notes about the vendor (optional)"
            />
            <p v-if="errors.notes" class="mt-2 text-sm text-red-600">
              {{ errors.notes }}
            </p>
          </div>
        </div>

        <!-- Contact Name Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="contact_name"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Contact Name <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="contact_name"
              v-model="form.contact_name"
              type="text"
              required
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.contact_name,
              }"
              placeholder="John Doe"
            />
            <p v-if="errors.contact_name" class="mt-2 text-sm text-red-600">
              {{ errors.contact_name }}
            </p>
          </div>
        </div>

        <!-- Contact Email Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="contact_email"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Contact Email <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="contact_email"
              v-model="form.contact_email"
              type="email"
              required
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.contact_email,
              }"
              placeholder="john@example.com"
            />
            <p v-if="errors.contact_email" class="mt-2 text-sm text-red-600">
              {{ errors.contact_email }}
            </p>
          </div>
        </div>

        <!-- Contact Phone Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="contact_phone"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Contact Phone <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="contact_phone"
              v-model="form.contact_phone"
              type="tel"
              required
              pattern="\(\d{3}\)\s\d{3}-\d{4}"
              class="input-field"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.contact_phone,
              }"
              placeholder="(123) 456-7890"
            />
            <p v-if="errors.contact_phone" class="mt-2 text-sm text-red-600">
              {{ errors.contact_phone }}
            </p>
            <p class="mt-1 text-xs text-gray-500">Format: (123) 456-7890</p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
          <!-- Spacer for large screens to align with form fields -->
          <div class="hidden lg:block"></div>

          <!-- Buttons Container -->
          <div class="lg:col-span-2 flex flex-col sm:flex-row gap-4">
            <!-- Cancel Button -->
            <button
              type="button"
              @click="handleCancel"
              class="btn-secondary flex-1"
            >
              Cancel
            </button>

            <!-- Primary Button -->
            <button
              type="submit"
              :disabled="isSubmitting"
              class="btn-primary flex-1"
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
                    : isEdit
                      ? 'Update Vendor'
                      : 'Create Vendor'
                }}
              </span>
              <span v-else>
                {{ isEdit ? 'Update Vendor' : 'Create Vendor' }}
              </span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { vendorsApi, queryKeys } from '@neibrpay/api-client';
import {
  type CreateVendorDto,
  type UpdateVendorDto,
  VendorCategory,
  getVendorCategoryOptions,
  getUSStates,
} from '@neibrpay/models';

const route = useRoute();
const router = useRouter();
const queryClient = useQueryClient();

// Check if this is edit mode
const isEdit = computed(() => !!route.params.id);
const vendorId = computed(() => Number(route.params.id));

// Form data
const form = reactive<CreateVendorDto & UpdateVendorDto>({
  name: '',
  description: '',
  category: '' as VendorCategory,
  ein: '',
  street_address: '',
  city: '',
  state: '',
  zip_code: '',
  website: '',
  notes: '',
  contact_name: '',
  contact_email: '',
  contact_phone: '',
});

// Form validation
const errors = ref<Record<string, string>>({});
const submitError = ref('');

// Category and state options
const categoryOptions = getVendorCategoryOptions();
const stateOptions = getUSStates();

// Fetch vendor data for edit mode
const { data: vendorData } = useQuery({
  queryKey: queryKeys.vendors.detail(vendorId.value),
  queryFn: () => vendorsApi.get(vendorId.value),
  select: data => data.data,
  enabled: isEdit.value,
});

// Populate form when vendor data is loaded
const populateForm = (vendor: any) => {
  form.name = vendor.name;
  form.description = vendor.description || '';
  form.category = vendor.category;
  form.ein = vendor.ein || '';
  form.street_address = vendor.street_address;
  form.city = vendor.city;
  form.state = vendor.state;
  form.zip_code = vendor.zip_code;
  form.website = vendor.website || '';
  form.notes = vendor.notes || '';
  form.contact_name = vendor.contact_name;
  form.contact_email = vendor.contact_email;
  form.contact_phone = vendor.contact_phone;
};

// Watch for vendor data changes and populate form
watch(
  vendorData,
  newVendorData => {
    if (isEdit.value && newVendorData) {
      populateForm(newVendorData);
    }
  },
  { immediate: true }
);

// Create mutation
const { mutate: createVendor, isPending: isCreating } = useMutation({
  mutationFn: (data: CreateVendorDto) => vendorsApi.create(data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.vendors.all });
    router.push('/vendors');
  },
  onError: (error: any) => {
    submitError.value =
      error.response?.data?.message || 'Failed to create vendor';
  },
});

// Update mutation
const { mutate: updateVendor, isPending: isUpdating } = useMutation({
  mutationFn: ({ id, data }: { id: number; data: UpdateVendorDto }) =>
    vendorsApi.update(id, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.vendors.all });
    router.push('/vendors');
  },
  onError: (error: any) => {
    submitError.value =
      error.response?.data?.message || 'Failed to update vendor';
  },
});

const isSubmitting = computed(() => isCreating.value || isUpdating.value);

// Form validation
const validateForm = (): boolean => {
  errors.value = {};

  if (!form.name.trim()) {
    errors.value.name = 'Name is required';
  } else if (form.name.length > 255) {
    errors.value.name = 'Name must be less than 255 characters';
  }

  if (!form.category) {
    errors.value.category = 'Category is required';
  }

  if (form.ein && form.ein.length > 20) {
    errors.value.ein = 'EIN must be less than 20 characters';
  }

  if (!form.street_address.trim()) {
    errors.value.street_address = 'Street address is required';
  } else if (form.street_address.length > 255) {
    errors.value.street_address =
      'Street address must be less than 255 characters';
  }

  if (!form.city.trim()) {
    errors.value.city = 'City is required';
  } else if (form.city.length > 100) {
    errors.value.city = 'City must be less than 100 characters';
  }

  if (!form.state) {
    errors.value.state = 'State is required';
  }

  if (!form.zip_code.trim()) {
    errors.value.zip_code = 'ZIP code is required';
  } else if (!/^\d{5}(-\d{4})?$/.test(form.zip_code)) {
    errors.value.zip_code =
      'ZIP code format is invalid. Use 12345 or 12345-6789';
  }

  if (form.website && !/^https?:\/\/.+/.test(form.website)) {
    errors.value.website = 'Website must be a valid URL';
  }

  if (!form.contact_name.trim()) {
    errors.value.contact_name = 'Contact name is required';
  } else if (form.contact_name.length > 255) {
    errors.value.contact_name = 'Contact name must be less than 255 characters';
  }

  if (!form.contact_email.trim()) {
    errors.value.contact_email = 'Contact email is required';
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.contact_email)) {
    errors.value.contact_email = 'Contact email must be a valid email address';
  }

  if (!form.contact_phone.trim()) {
    errors.value.contact_phone = 'Contact phone is required';
  } else if (!/^\(\d{3}\)\s\d{3}-\d{4}$/.test(form.contact_phone)) {
    errors.value.contact_phone =
      'Contact phone format is invalid. Use (123) 456-7890';
  }

  return Object.keys(errors.value).length === 0;
};

// Handle form submission
const handleSubmit = () => {
  if (!validateForm()) {
    return;
  }

  submitError.value = '';

  if (isEdit.value) {
    updateVendor({
      id: vendorId.value,
      data: {
        name: form.name,
        description: form.description,
        category: form.category,
        ein: form.ein,
        street_address: form.street_address,
        city: form.city,
        state: form.state,
        zip_code: form.zip_code,
        website: form.website,
        notes: form.notes,
        contact_name: form.contact_name,
        contact_email: form.contact_email,
        contact_phone: form.contact_phone,
      },
    });
  } else {
    createVendor({
      name: form.name,
      description: form.description,
      category: form.category,
      ein: form.ein,
      street_address: form.street_address,
      city: form.city,
      state: form.state,
      zip_code: form.zip_code,
      website: form.website,
      notes: form.notes,
      contact_name: form.contact_name,
      contact_email: form.contact_email,
      contact_phone: form.contact_phone,
    });
  }
};

// Handle cancel
const handleCancel = () => {
  router.push('/vendors');
};
</script>
