<template>
  <div class="max-w-2xl mx-auto">
    <div class="bg-white shadow sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-6">
          {{ isEdit ? 'Edit Charge' : 'Add New Charge' }}
        </h3>

        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Title -->
          <div>
            <label for="title" class="block text-sm font-medium text-gray-700">
              Title <span class="text-red-500">*</span>
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              :class="{ 'border-red-300': errors.title }"
              placeholder="Enter charge title"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">
              {{ errors.title }}
            </p>
          </div>

          <!-- Description -->
          <div>
            <label
              for="description"
              class="block text-sm font-medium text-gray-700"
            >
              Description
            </label>
            <textarea
              id="description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              :class="{ 'border-red-300': errors.description }"
              placeholder="Enter charge description (optional)"
            />
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
              {{ errors.description }}
            </p>
          </div>

          <!-- Amount -->
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700">
              Amount <span class="text-red-500">*</span>
            </label>
            <div class="mt-1 relative rounded-md shadow-sm">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <span class="text-gray-500 sm:text-sm">$</span>
              </div>
              <input
                id="amount"
                v-model="form.amount"
                type="number"
                step="0.01"
                min="0"
                max="999999.99"
                required
                class="pl-7 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.amount }"
                placeholder="0.00"
              />
            </div>
            <p v-if="errors.amount" class="mt-1 text-sm text-red-600">
              {{ errors.amount }}
            </p>
          </div>

          <!-- Category -->
          <div>
            <label
              for="category"
              class="block text-sm font-medium text-gray-700"
            >
              Category <span class="text-red-500">*</span>
            </label>
            <select
              id="category"
              v-model="form.category"
              required
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              :class="{ 'border-red-300': errors.category }"
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
            <p v-if="errors.category" class="mt-1 text-sm text-red-600">
              {{ errors.category }}
            </p>
          </div>

          <!-- Is Active -->
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

          <!-- Error Summary -->
          <div
            v-if="submitError"
            class="bg-red-50 border border-red-200 rounded-md p-4"
          >
            <div class="flex">
              <div class="flex-shrink-0">
                <svg
                  class="h-5 w-5 text-red-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Error</h3>
                <div class="mt-2 text-sm text-red-700">
                  {{ submitError }}
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex justify-end space-x-3">
            <button
              type="button"
              @click="handleCancel"
              class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="isSubmitting"
              class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
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
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
              {{
                isSubmitting
                  ? 'Saving...'
                  : isEdit
                    ? 'Update Charge'
                    : 'Create Charge'
              }}
            </button>
          </div>
        </form>
      </div>
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
