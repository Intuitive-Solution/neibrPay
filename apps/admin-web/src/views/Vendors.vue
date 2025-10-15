<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-right justify-end">
      <router-link
        to="/vendors/create"
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
        Add Vendor
      </router-link>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Search -->
        <div>
          <label for="search" class="block text-sm font-medium text-gray-700">
            Search
          </label>
          <input
            id="search"
            v-model="filters.search"
            type="text"
            placeholder="Search by name..."
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
            Error loading vendors
          </h3>
          <div class="mt-2 text-sm text-red-700">
            {{ error }}
          </div>
        </div>
      </div>
    </div>

    <!-- Vendors Table -->
    <div v-else class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="vendors.length === 0" class="text-center py-12">
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
            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">No vendors found</h3>
        <p class="mt-1 text-sm text-gray-500">
          Get started by creating a new vendor.
        </p>
        <div class="mt-6">
          <router-link
            to="/vendors/create"
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
            Add Vendor
          </router-link>
        </div>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Name
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Category
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Contact Person
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                scope="col"
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="vendor in vendors"
              :key="vendor.id"
              class="hover:bg-gray-50"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <div class="text-sm font-medium text-gray-900">
                    {{ vendor.name }}
                  </div>
                  <div
                    v-if="vendor.description"
                    class="text-sm text-gray-500 truncate max-w-xs"
                  >
                    {{ vendor.description }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-900">
                  {{ getVendorCategoryDisplayName(vendor.category) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <div class="text-sm font-medium text-gray-900">
                    {{ vendor.contact_name }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ vendor.contact_email }}
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                    vendor.deleted_at
                      ? 'bg-red-100 text-red-800'
                      : 'bg-green-100 text-green-800',
                  ]"
                >
                  {{ vendor.deleted_at ? 'Deleted' : 'Active' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-3">
                  <router-link
                    :to="`/vendors/${vendor.id}/edit`"
                    class="text-primary hover:text-primary-dark"
                  >
                    Edit
                  </router-link>
                  <button
                    v-if="!vendor.deleted_at"
                    @click="deleteVendor(vendor.id)"
                    class="text-red-600 hover:text-red-900"
                    :disabled="isDeleting"
                  >
                    Delete
                  </button>
                  <button
                    v-else
                    @click="restoreVendor(vendor.id)"
                    class="text-green-600 hover:text-green-900"
                    :disabled="isRestoring"
                  >
                    Restore
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { vendorsApi, queryKeys } from '@neibrpay/api-client';
import {
  type VendorFilters,
  getVendorCategoryDisplayName,
  getVendorCategoryOptions,
} from '@neibrpay/models';

// Reactive data
const filters = ref<VendorFilters>({
  search: '',
  category: '',
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
const categoryOptions = getVendorCategoryOptions();

// Query for vendors
const {
  data: vendorsData,
  isLoading,
  error,
} = useQuery({
  queryKey: queryKeys.vendors.list(filters.value),
  queryFn: () => vendorsApi.list(filters.value),
  select: data => data.data,
});

const vendors = computed(() => vendorsData.value || []);

// Delete mutation
const { mutate: deleteVendor, isPending: isDeleting } = useMutation({
  mutationFn: (id: number) => vendorsApi.delete(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.vendors.all });
  },
});

// Restore mutation
const { mutate: restoreVendor, isPending: isRestoring } = useMutation({
  mutationFn: (id: number) => vendorsApi.restore(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.vendors.all });
  },
});

// Apply filters
const applyFilters = () => {
  // The query will automatically refetch when filters change
};
</script>
