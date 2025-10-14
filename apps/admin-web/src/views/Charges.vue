<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Charges</h1>
        <p class="text-sm text-gray-600 mt-1">
          Manage standard charges and fees for your community
        </p>
      </div>
      <router-link
        to="/charges/create"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
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
          />
        </svg>
        Add Charge
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <label for="search" class="block text-sm font-medium text-gray-700">
            Search
          </label>
          <input
            id="search"
            v-model="filters.search"
            type="text"
            placeholder="Search by title..."
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
            @input="debouncedSearch"
          />
        </div>

        <!-- Category Filter -->
        <div>
          <label for="category" class="block text-sm font-medium text-gray-700">
            Category
          </label>
          <select
            id="category"
            v-model="filters.category"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
            @change="applyFilters"
          >
            <option value="">All Categories</option>
            <option
              v-for="option in categoryOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label for="status" class="block text-sm font-medium text-gray-700">
            Status
          </label>
          <select
            id="status"
            v-model="filters.is_active"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
            @change="applyFilters"
          >
            <option value="">All Status</option>
            <option :value="true">Active</option>
            <option :value="false">Inactive</option>
          </select>
        </div>

        <!-- Include Deleted -->
        <div class="flex items-end">
          <label class="flex items-center">
            <input
              v-model="filters.include_deleted"
              type="checkbox"
              class="rounded border-gray-300 text-primary shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50"
              @change="applyFilters"
            />
            <span class="ml-2 text-sm text-gray-700">Include deleted</span>
          </label>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-8">
      <div
        class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
      ></div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
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
          <h3 class="text-sm font-medium text-red-800">
            Error loading charges
          </h3>
          <div class="mt-2 text-sm text-red-700">
            {{ error }}
          </div>
        </div>
      </div>
    </div>

    <!-- Charges Table -->
    <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="charges.length === 0" class="text-center py-12">
        <svg
          class="mx-auto h-12 w-12 text-gray-400"
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
        <h3 class="mt-2 text-sm font-medium text-gray-900">No charges found</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by creating a new charge.
        </p>
        <div class="mt-6">
          <router-link
            to="/charges/create"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
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
              />
            </svg>
            Add Charge
          </router-link>
        </div>
      </div>

      <ul v-else class="divide-y divide-gray-200">
        <li v-for="charge in charges" :key="charge.id" class="px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <div class="flex items-center space-x-3">
                <h3 class="text-sm font-medium text-gray-900 truncate">
                  {{ charge.title }}
                </h3>
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    charge.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-gray-100 text-gray-800',
                  ]"
                >
                  {{ charge.is_active ? 'Active' : 'Inactive' }}
                </span>
                <span
                  v-if="charge.deleted_at"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                >
                  Deleted
                </span>
              </div>
              <div
                class="mt-1 flex items-center space-x-4 text-sm text-gray-500"
              >
                <span class="font-medium text-gray-900">
                  ${{ formatCurrency(charge.amount) }}
                </span>
                <span>{{ getChargeCategoryDisplayName(charge.category) }}</span>
                <span v-if="charge.description" class="truncate">
                  {{ charge.description }}
                </span>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <router-link
                :to="`/charges/${charge.id}/edit`"
                class="text-primary hover:text-primary-dark text-sm font-medium"
              >
                Edit
              </router-link>
              <button
                v-if="!charge.deleted_at"
                @click="deleteCharge(charge.id)"
                class="text-red-600 hover:text-red-900 text-sm font-medium"
                :disabled="isDeleting"
              >
                Delete
              </button>
              <button
                v-else
                @click="restoreCharge(charge.id)"
                class="text-green-600 hover:text-green-900 text-sm font-medium"
                :disabled="isRestoring"
              >
                Restore
              </button>
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { chargesApi, queryKeys } from '@neibrpay/api-client';
import {
  type ChargeFilters,
  getChargeCategoryDisplayName,
  getChargeCategoryOptions,
} from '@neibrpay/models';

// Reactive data
const filters = ref<ChargeFilters>({
  search: '',
  category: '',
  is_active: '',
  include_deleted: false,
});

const queryClient = useQueryClient();

// Debounced search
let searchTimeout: NodeJS.Timeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

// Category options for dropdown
const categoryOptions = getChargeCategoryOptions();

// Query for charges
const {
  data: chargesData,
  isLoading,
  error,
} = useQuery({
  queryKey: queryKeys.charges.list(filters.value),
  queryFn: () => chargesApi.list(filters.value),
  select: data => data.data,
});

const charges = computed(() => chargesData.value || []);

// Delete mutation
const { mutate: deleteCharge, isPending: isDeleting } = useMutation({
  mutationFn: (id: number) => chargesApi.delete(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.charges.all });
  },
});

// Restore mutation
const { mutate: restoreCharge, isPending: isRestoring } = useMutation({
  mutationFn: (id: number) => chargesApi.restore(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.charges.all });
  },
});

// Apply filters
const applyFilters = () => {
  // The query will automatically refetch when filters change
};

// Format currency
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};
</script>
