<template>
  <div class="max-w-4xl">
    <!-- Header -->
    <div class="card-modern bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
        <div class="mb-4 lg:mb-0">
          <h1 class="text-2xl font-bold text-gray-900">Unit Details</h1>
          <p class="text-gray-600 mt-1">View unit information</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="goBack"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
          >
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
            Back to Units
          </button>
        </div>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div
        class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
      ></div>
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="card-modern bg-red-50 border border-red-200 rounded-lg p-6"
    >
      <p class="text-sm text-red-800">
        {{ error.message || 'Failed to load unit' }}
      </p>
    </div>

    <!-- Content -->
    <div v-else-if="unit" class="card-modern bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-500">Title</label>
          <p class="mt-1 text-sm text-gray-900">{{ unit.title }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">Status</label>
          <span
            :class="unit.deleted_at ? 'badge-overdue' : 'badge-paid'"
            class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1 badge"
          >
            {{
              unit.deleted_at
                ? 'Deleted'
                : unit.is_active
                  ? 'Active'
                  : 'Inactive'
            }}
          </span>
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-500">Address</label>
          <p class="mt-1 text-sm text-gray-900">{{ unit.address }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">City</label>
          <p class="mt-1 text-sm text-gray-900">{{ unit.city }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500">State</label>
          <p class="mt-1 text-sm text-gray-900">{{ unit.state }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500"
            >ZIP Code</label
          >
          <p class="mt-1 text-sm text-gray-900">{{ unit.zip_code }}</p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500"
            >Starting Balance</label
          >
          <p class="mt-1 text-sm font-medium text-gray-900">
            {{ formatCurrency(unit.starting_balance) }}
          </p>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-500"
            >Balance as of Date</label
          >
          <p class="mt-1 text-sm text-gray-900">
            {{ formatDate(unit.balance_as_of_date) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useUnit } from '../composables/useUnits';

const route = useRoute();
const router = useRouter();

const unitId = computed(() => Number(route.params.id));
const { data: unitData, isLoading, error } = useUnit(unitId);
const unit = computed(() => unitData.value ?? null);

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
};

const formatDate = (dateString: string): string => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const goBack = () => {
  router.push('/units');
};
</script>
