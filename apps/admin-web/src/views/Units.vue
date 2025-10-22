<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Active Units Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-green-500': activeFilter === 'active',
        }"
        @click="filterByStatus('active')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <svg
              class="w-6 h-6 text-green-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Active Units</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ activeUnitsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- All Units Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-blue-500': activeFilter === 'all',
        }"
        @click="filterByStatus('all')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg
              class="w-6 h-6 text-blue-600"
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
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">All Units</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ allUnitsCount }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Units Directory Section -->
    <div class="bg-white rounded-lg shadow-sm">
      <!-- Header Section -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <!-- Search Filter (Left) -->
          <div class="flex-1 max-w-md">
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search units..."
                class="input-field pl-10 w-full"
                @input="debouncedSearch"
              />
            </div>
          </div>

          <!-- Header Controls (Right) -->
          <div class="flex items-center space-x-3">
            <!-- Show Deleted Checkbox -->
            <div class="flex items-center">
              <input
                id="show-deleted"
                v-model="includeDeleted"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                @change="applyFilters"
              />
              <label for="show-deleted" class="ml-2 text-sm text-gray-700">
                Include Deleted
              </label>
            </div>

            <!-- Refresh Button -->
            <button
              @click="refetch"
              :disabled="isLoading"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 transition-colors duration-200"
            >
              <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                :class="{ 'animate-spin': isLoading }"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
            </button>

            <!-- New Unit Button (Desktop) -->
            <router-link to="/units/add" class="hidden md:inline-flex">
              <button class="btn-primary btn-sm">
                <svg
                  class="w-4 h-4"
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
              </button>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="overflow-hidden">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-12">
          <div class="flex items-center space-x-2">
            <svg
              class="animate-spin h-5 w-5 text-primary"
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
            <span class="text-sm text-gray-600">Loading units...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex items-center justify-center py-12">
          <div class="text-center">
            <svg
              class="mx-auto h-12 w-12 text-red-400"
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              Error loading units
            </h3>
            <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
            <div class="mt-4">
              <button
                @click="refetch"
                class="text-sm text-primary hover:text-primary-600"
              >
                Try again
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!filteredUnits || filteredUnits.length === 0"
          class="flex items-center justify-center py-12"
        >
          <div class="text-center">
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              No units found
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                searchQuery
                  ? 'Try adjusting your search query.'
                  : 'Get started by adding your first unit.'
              }}
            </p>
            <div v-if="!searchQuery" class="mt-4">
              <router-link to="/units/add">
                <button
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                  <svg
                    class="-ml-1 mr-2 h-4 w-4"
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
                  Add Unit
                </button>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Table with Data -->
        <table v-else class="w-full divide-y divide-gray-200">
          <thead class="bg-gray-100 border-b border-gray-200">
            <tr>
              <th
                @click="sortBy('title')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>TITLE</span>
                  <svg
                    v-if="sortColumn === 'title'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('address')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>ADDRESS</span>
                  <svg
                    v-if="sortColumn === 'address'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('location')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>LOCATION</span>
                  <svg
                    v-if="sortColumn === 'location'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('starting_balance')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden xl:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>STARTING BALANCE</span>
                  <svg
                    v-if="sortColumn === 'starting_balance'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('status')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>STATUS</span>
                  <svg
                    v-if="sortColumn === 'status'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"
              ></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="unit in filteredUnits"
              :key="unit.id"
              class="table-row-hover"
            >
              <!-- Title Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div
                        :class="[
                          'h-10 w-10 rounded-full flex items-center justify-center',
                          unit.deleted_at ? 'bg-red-100' : 'bg-gray-100',
                        ]"
                      >
                        <span
                          :class="[
                            'text-sm font-medium',
                            unit.deleted_at ? 'text-red-600' : 'text-gray-700',
                          ]"
                        >
                          {{ unit.title.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="flex items-center space-x-2">
                        <div
                          :class="[
                            'text-sm font-medium',
                            unit.deleted_at
                              ? 'text-red-600 line-through'
                              : 'text-gray-900',
                          ]"
                        >
                          {{ unit.title }}
                        </div>
                        <span
                          v-if="unit.deleted_at"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"
                        >
                          Deleted
                        </span>
                      </div>
                      <!-- Mobile-only additional info -->
                      <div class="sm:hidden mt-1">
                        <div
                          :class="[
                            'text-xs',
                            unit.deleted_at ? 'text-red-400' : 'text-gray-500',
                          ]"
                        >
                          {{ unit.address }} • {{ unit.city }}, {{ unit.state }}
                        </div>
                        <div class="mt-1">
                          <span
                            v-if="unit.deleted_at"
                            class="badge badge-overdue text-xs"
                          >
                            Deleted
                          </span>
                          <span
                            v-else
                            :class="getStatusBadgeClass(unit)"
                            class="badge text-xs"
                          >
                            {{ getStatusText(unit) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Address Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <div
                  :class="[
                    'text-sm',
                    unit.deleted_at
                      ? 'text-red-600 line-through'
                      : 'text-gray-900',
                  ]"
                >
                  {{ unit.address }}
                </div>
              </td>

              <!-- Location Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                <div
                  :class="[
                    'text-sm',
                    unit.deleted_at
                      ? 'text-red-600 line-through'
                      : 'text-gray-900',
                  ]"
                >
                  {{ unit.city }}, {{ unit.state }} {{ unit.zip_code }}
                </div>
              </td>

              <!-- Starting Balance Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden xl:table-cell">
                <div
                  :class="[
                    'text-sm',
                    unit.deleted_at
                      ? 'text-red-600 line-through'
                      : 'text-gray-900',
                  ]"
                >
                  {{ formatCurrency(unit.starting_balance) }}
                </div>
              </td>

              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <span v-if="unit.deleted_at" class="badge badge-overdue">
                  Deleted
                </span>
                <span v-else :class="getStatusBadgeClass(unit)" class="badge">
                  {{ getStatusText(unit) }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end relative">
                  <!-- Enhanced Kebab Menu - More Visible -->
                  <DropdownMenu
                    trigger-class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 shadow-sm hover:shadow-md transition-all duration-200"
                  >
                    <template #default="{ close }">
                      <!-- Actions for deleted units -->
                      <template v-if="unit.deleted_at">
                        <button
                          @click="
                            () => {
                              restoreUnit(unit.id);
                              close();
                            }
                          "
                          class="dropdown-item"
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
                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                            />
                          </svg>
                          Restore
                        </button>
                      </template>

                      <!-- Actions for active units -->
                      <template v-else>
                        <button
                          @click="
                            () => {
                              editUnit(unit.id);
                              close();
                            }
                          "
                          class="dropdown-item"
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
                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
                            />
                          </svg>
                          Edit
                        </button>
                        <div class="border-t border-gray-200 my-1"></div>
                        <button
                          @click="
                            () => {
                              deleteUnit(unit);
                              close();
                            }
                          "
                          :disabled="deletingUnitId === unit.id"
                          class="dropdown-item dropdown-item-danger"
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
                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                            />
                          </svg>
                          {{
                            deletingUnitId === unit.id
                              ? 'Deleting...'
                              : 'Delete'
                          }}
                        </button>
                      </template>
                    </template>
                  </DropdownMenu>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showDeleteModal"
      title="Delete Unit"
      :message="`Are you sure you want to delete ${unitToDelete?.title || 'this unit'}?`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletingUnitId === unitToDelete?.id"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Mobile Fixed Bottom Button -->
    <div
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/units/add" class="block">
        <button class="btn-primary w-full">
          <svg
            class="w-5 h-5 mr-2"
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
          Add Unit
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { unitsApi, queryKeys } from '@neibrpay/api-client';
import type { Unit } from '@neibrpay/models';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import DropdownMenu from '../components/DropdownMenu.vue';

const router = useRouter();
const queryClient = useQueryClient();

// Local state
const searchQuery = ref('');
const includeDeleted = ref(false);
const deletingUnitId = ref<number | null>(null);
const showDeleteModal = ref(false);
const unitToDelete = ref<Unit | null>(null);
const activeFilter = ref<'active' | 'all' | null>('active'); // Default to 'active' filter
const sortColumn = ref<
  'title' | 'address' | 'location' | 'starting_balance' | 'status' | null
>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

// Debounced search
let searchTimeout: NodeJS.Timeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

// Query for units
const {
  data: unitsData,
  isLoading,
  error,
  refetch: refetchUnits,
} = useQuery({
  queryKey: queryKeys.units.list({ include_deleted: includeDeleted.value }),
  queryFn: () => unitsApi.getUnits(includeDeleted.value),
  select: (data: any) => data,
});

const units = computed(() => unitsData.value || []);

// Delete mutation
const { mutateAsync: deleteUnitMutation } = useMutation({
  mutationFn: (id: number) => unitsApi.deleteUnit(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.units.all });
  },
});

// Restore mutation
const { mutateAsync: restoreUnitMutation } = useMutation({
  mutationFn: (id: number) => unitsApi.restoreUnit(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.units.all });
  },
});

// Helper function to check if unit is active
const isUnitActive = (unit: Unit): boolean => {
  return !unit.deleted_at;
};

// Computed properties for summary cards
const activeUnitsCount = computed(() => {
  if (!units.value) return 0;
  return units.value.filter((unit: Unit) => isUnitActive(unit)).length;
});

const allUnitsCount = computed(() => {
  if (!units.value) return 0;
  return units.value.length;
});

// Computed properties - filter units based on activeFilter and search
const filteredUnits = computed(() => {
  if (!units.value) return [];

  let filtered = units.value.filter((unit: Unit) => {
    // Apply active filter from summary cards
    if (activeFilter.value === 'active' && !isUnitActive(unit)) {
      return false;
    }

    // 'all' filter shows all units (no additional filtering needed)
    // If activeFilter is 'all', we don't filter by status

    // Apply search filter
    if (searchQuery.value && searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim();
      const title = (unit.title || '').toLowerCase();
      const address = (unit.address || '').toLowerCase();
      const city = (unit.city || '').toLowerCase();
      const state = (unit.state || '').toLowerCase();
      const zipCode = (unit.zip_code || '').toLowerCase();

      return (
        title.includes(query) ||
        address.includes(query) ||
        city.includes(query) ||
        state.includes(query) ||
        zipCode.includes(query)
      );
    }

    return true;
  });

  // Apply sorting
  if (sortColumn.value) {
    filtered = [...filtered].sort((a: Unit, b: Unit) => {
      let aValue: any;
      let bValue: any;

      switch (sortColumn.value) {
        case 'title':
          aValue = a.title || '';
          bValue = b.title || '';
          break;
        case 'address':
          aValue = a.address || '';
          bValue = b.address || '';
          break;
        case 'location':
          aValue = `${a.city}, ${a.state} ${a.zip_code}` || '';
          bValue = `${b.city}, ${b.state} ${b.zip_code}` || '';
          break;
        case 'starting_balance':
          aValue = a.starting_balance || 0;
          bValue = b.starting_balance || 0;
          break;
        case 'status':
          aValue = a.deleted_at ? 'deleted' : 'active';
          bValue = b.deleted_at ? 'deleted' : 'active';
          break;
        default:
          return 0;
      }

      if (sortDirection.value === 'asc') {
        return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
      } else {
        return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
      }
    });
  }

  return filtered;
});

// Methods
const refetch = () => {
  refetchUnits();
};

const filterByStatus = (status: 'active' | 'all') => {
  // Toggle filter: if already active, clear it; otherwise, set it
  if (activeFilter.value === status) {
    activeFilter.value = null;
  } else {
    activeFilter.value = status;
  }
};

const sortBy = (
  column: 'title' | 'address' | 'location' | 'starting_balance' | 'status'
) => {
  if (sortColumn.value === column) {
    // Toggle direction if clicking the same column
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column and default to ascending
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
};

const getStatusText = (unit: Unit) => {
  if (unit.deleted_at) return 'Deleted';
  return 'Active';
};

const getStatusBadgeClass = (unit: Unit) => {
  if (unit.deleted_at) return 'badge-overdue';
  return 'badge-paid';
};

const editUnit = (unitId: number) => {
  router.push(`/units/edit/${unitId}`);
};

const restoreUnit = async (unitId: number) => {
  try {
    await restoreUnitMutation(unitId);
    console.log('Unit restored successfully');
    refetch();
  } catch (error: any) {
    console.error('Error restoring unit:', error);
    alert(`Failed to restore unit: ${error.message || 'Unknown error'}`);
  }
};

const deleteUnit = (unit: Unit) => {
  // Store the unit to delete and show the modal
  unitToDelete.value = unit;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!unitToDelete.value) return;

  const unitId = unitToDelete.value.id;
  deletingUnitId.value = unitId;

  try {
    console.log('Starting delete for unit:', unitId);
    const result = await deleteUnitMutation(unitId);
    console.log('Delete result:', result);
    // Show success message
    console.log('Unit deleted successfully');
    // Close modal
    showDeleteModal.value = false;
    unitToDelete.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error deleting unit:', error);

    // Check if it's an authentication error
    if (error.message && error.message.includes('Unit not found')) {
      // This might be an authentication issue
      alert('Authentication error. Please refresh the page and try again.');
    } else {
      // Show error message to user
      alert(`Failed to delete unit: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    deletingUnitId.value = null;
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  unitToDelete.value = null;
};

// Apply filters
const applyFilters = () => {
  // The query will automatically refetch when filters change
};

// Watch for changes in include_deleted to refetch data and update filter
watch(
  () => includeDeleted.value,
  (newValue: boolean) => {
    refetchUnits();

    // When showing deleted units, automatically set filter to 'all'
    if (newValue) {
      activeFilter.value = 'all';
    }
  }
);

// Reset mutation states on component mount to clear any stuck states
onMounted(() => {
  // Reset any stuck states
});

// Global reset function for debugging (can be called from browser console)
(window as any).resetUnitMutations = () => {
  deletingUnitId.value = null;
  console.log('Unit mutations and local state reset');
};
</script>
