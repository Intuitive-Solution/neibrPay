<template>
  <div class="max-w-4xl">
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
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-6">
        {{ isEdit ? 'Edit Charge' : 'Add New Charge' }}
      </h3>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Title Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="title"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Title <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.title,
              }"
              placeholder="Enter charge title"
            />
            <p v-if="errors.title" class="mt-2 text-sm text-red-600">
              {{ errors.title }}
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
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.description,
              }"
              placeholder="Enter charge description (optional)"
            />
            <p v-if="errors.description" class="mt-2 text-sm text-red-600">
              {{ errors.description }}
            </p>
          </div>
        </div>

        <!-- Amount Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="amount"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Amount <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <span class="text-gray-500 text-sm">$</span>
              </div>
              <input
                id="amount"
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0"
                max="999999.99"
                required
                class="w-full pl-7 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.amount,
                }"
                placeholder="0.00"
              />
            </div>
            <p v-if="errors.amount" class="mt-2 text-sm text-red-600">
              {{ errors.amount }}
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
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
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

        <!-- Is Active Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="is_active"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Status
          </label>
          <div class="lg:col-span-2">
            <div class="flex items-center">
              <input
                id="is_active"
                v-model="form.is_active"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
              />
              <label for="is_active" class="ml-2 block text-sm text-gray-900">
                Active
              </label>
            </div>
            <p class="mt-2 text-sm text-gray-600">
              Inactive charges will not appear in the charge selection dropdown
              when creating invoices.
            </p>
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
              class="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
            >
              Cancel
            </button>

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
                    : isEdit
                      ? 'Update Charge'
                      : 'Create Charge'
                }}
              </span>
              <span v-else>
                {{ isEdit ? 'Update Charge' : 'Create Charge' }}
              </span>
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { chargesApi, queryKeys } from '@neibrpay/api-client';
import {
  type CreateChargeDto,
  type UpdateChargeDto,
  ChargeCategory,
  getChargeCategoryOptions,
} from '@neibrpay/models';

const route = useRoute();
const router = useRouter();
const queryClient = useQueryClient();

// Check if this is edit mode
const isEdit = computed(() => !!route.params.id);
const chargeId = computed(() => Number(route.params.id));

// Form data
const form = reactive<CreateChargeDto & UpdateChargeDto>({
  title: '',
  description: '',
  amount: 0,
  category: '' as ChargeCategory,
  is_active: true,
});

// Form validation
const errors = ref<Record<string, string>>({});
const submitError = ref('');

// Category options
const categoryOptions = getChargeCategoryOptions();

// Fetch charge data for edit mode
const { data: chargeData } = useQuery({
  queryKey: queryKeys.charges.detail(chargeId.value),
  queryFn: () => chargesApi.get(chargeId.value),
  select: data => data.data,
  enabled: isEdit.value,
});

// Populate form when charge data is loaded
onMounted(() => {
  if (isEdit.value && chargeData.value) {
    form.title = chargeData.value.title;
    form.description = chargeData.value.description || '';
    form.amount = chargeData.value.amount;
    form.category = chargeData.value.category;
    form.is_active = chargeData.value.is_active;
  }
});

// Create mutation
const { mutate: createCharge, isPending: isCreating } = useMutation({
  mutationFn: (data: CreateChargeDto) => chargesApi.create(data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.charges.all });
    router.push('/charges');
  },
  onError: (error: any) => {
    submitError.value =
      error.response?.data?.message || 'Failed to create charge';
  },
});

// Update mutation
const { mutate: updateCharge, isPending: isUpdating } = useMutation({
  mutationFn: ({ id, data }: { id: number; data: UpdateChargeDto }) =>
    chargesApi.update(id, data),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.charges.all });
    router.push('/charges');
  },
  onError: (error: any) => {
    submitError.value =
      error.response?.data?.message || 'Failed to update charge';
  },
});

const isSubmitting = computed(() => isCreating.value || isUpdating.value);

// Form validation
const validateForm = (): boolean => {
  errors.value = {};

  if (!form.title.trim()) {
    errors.value.title = 'Title is required';
  } else if (form.title.length > 255) {
    errors.value.title = 'Title must be less than 255 characters';
  }

  if (!form.amount || form.amount < 0) {
    errors.value.amount = 'Amount must be greater than or equal to 0';
  } else if (form.amount > 999999.99) {
    errors.value.amount = 'Amount must be less than 999,999.99';
  }

  if (!form.category) {
    errors.value.category = 'Category is required';
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
    updateCharge({
      id: chargeId.value,
      data: {
        title: form.title,
        description: form.description,
        amount: form.amount,
        category: form.category,
        is_active: form.is_active,
      },
    });
  } else {
    createCharge({
      title: form.title,
      description: form.description,
      amount: form.amount,
      category: form.category,
      is_active: form.is_active,
    });
  }
};

// Handle cancel
const handleCancel = () => {
  router.push('/charges');
};
</script>
