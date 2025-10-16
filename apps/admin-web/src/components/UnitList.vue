<template>
  <div class="card">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-base font-semibold text-gray-900">Unit Directory</h2>
      <div class="flex items-center space-x-3">
        <label class="flex items-center">
          <input
            v-model="includeDeleted"
            type="checkbox"
            class="rounded border-gray-300 text-primary focus:ring-primary"
          />
          <span class="ml-2 text-sm text-gray-600">Show deleted</span>
        </label>
        <button
          @click="refreshUnits"
          :disabled="isLoading"
          class="btn-outline btn-sm"
        >
          <svg
            class="h-4 w-4"
            :class="{ 'animate-spin': isLoading }"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
        </button>
      </div>
    </div>

    <div v-if="isLoading" class="p-6">
      <div class="flex items-center justify-center">
        <div class="flex items-center space-x-2">
          <svg
            class="animate-spin h-5 w-5 text-primary"
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
          <span class="text-sm text-gray-600">Loading units...</span>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="p-6">
      <div class="text-center py-12">
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
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          Error loading units
        </h3>
        <p class="mt-1 text-sm text-gray-500">{{ error.message }}</p>
        <div class="mt-6">
          <button
            @click="refreshUnits"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            Try Again
          </button>
        </div>
      </div>
    </div>

    <div v-else-if="!units || units.length === 0" class="p-6">
      <div class="text-center py-12">
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
          {{ includeDeleted ? 'No deleted units' : 'No units yet' }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{
            includeDeleted
              ? 'No units have been deleted.'
              : 'Get started by adding your first unit.'
          }}
        </p>
        <div v-if="!includeDeleted" class="mt-6">
          <button
            @click="$emit('add-unit')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
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
            Add Unit
          </button>
        </div>
      </div>
    </div>

    <div v-else class="overflow-x-auto -mx-6">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              TITLE
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              ADDRESS
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              LOCATION
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              STARTING BALANCE
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              BALANCE DATE
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              STATUS
            </th>
            <th
              scope="col"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
            >
              ACTIONS
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="unit in units"
            :key="unit.id"
            class="table-row-hover"
            :class="{ 'opacity-50': unit.deleted_at }"
          >
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div class="flex-shrink-0 h-10 w-10">
                  <div
                    class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center"
                  >
                    <span class="text-sm font-medium text-gray-700">
                      {{ unit.title.charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium text-gray-900">
                    {{ unit.title }}
                  </div>
                </div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">{{ unit.address }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ unit.city }}, {{ unit.state }} {{ unit.zip_code }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ formatCurrency(unit.starting_balance) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-900">
                {{ formatDate(unit.balance_as_of_date) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span v-if="unit.deleted_at" class="badge badge-overdue">
                Deleted
              </span>
              <span v-else class="badge badge-paid"> Active </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right">
              <DropdownMenu>
                <template #default="{ close }">
                  <button
                    v-if="!unit.deleted_at"
                    @click="
                      () => {
                        $emit('edit-unit', unit);
                        close();
                      }
                    "
                    class="dropdown-item"
                  >
                    Edit
                  </button>
                  <button
                    v-if="!unit.deleted_at"
                    @click="
                      () => {
                        confirmDelete(unit);
                        close();
                      }
                    "
                    class="dropdown-item-danger"
                  >
                    Delete
                  </button>
                  <button
                    v-if="unit.deleted_at"
                    @click="
                      () => {
                        confirmRestore(unit);
                        close();
                      }
                    "
                    class="dropdown-item"
                  >
                    Restore
                  </button>
                </template>
              </DropdownMenu>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Delete Confirmation Modal -->
    <div
      v-if="showDeleteModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div
        class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
      >
        <div class="mt-3 text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100"
          >
            <svg
              class="h-6 w-6 text-red-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
              />
            </svg>
          </div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">
            Delete Unit
          </h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to delete "{{ unitToDelete?.title }}"? This
              action cannot be undone.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="deleteUnit"
              :disabled="isDeleting"
              class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:opacity-50"
            >
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
            </button>
            <button
              @click="cancelDelete"
              class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div
      v-if="showRestoreModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
    >
      <div
        class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
      >
        <div class="mt-3 text-center">
          <div
            class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100"
          >
            <svg
              class="h-6 w-6 text-green-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M5 13l4 4L19 7"
              />
            </svg>
          </div>
          <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">
            Restore Unit
          </h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to restore "{{ unitToRestore?.title }}"?
              This will make the unit active again.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="restoreUnit"
              :disabled="isRestoring"
              class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300 disabled:opacity-50"
            >
              {{ isRestoring ? 'Restoring...' : 'Restore' }}
            </button>
            <button
              @click="cancelRestore"
              class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import {
  useUnits,
  useDeleteUnit,
  useRestoreUnit,
} from '../composables/useUnits';
import type { Unit } from '@neibrpay/models';
import DropdownMenu from './DropdownMenu.vue';

// Props
defineProps<{
  includeDeleted?: boolean;
}>();

// Emits
const emit = defineEmits<{
  'add-unit': [];
  'edit-unit': [unit: Unit];
}>();

// State
const includeDeleted = ref(false);
const showDeleteModal = ref(false);
const showRestoreModal = ref(false);
const unitToDelete = ref<Unit | null>(null);
const unitToRestore = ref<Unit | null>(null);

// Queries and mutations
const { data: units, isLoading, error, refetch } = useUnits(includeDeleted);
const deleteUnitMutation = useDeleteUnit();
const restoreUnitMutation = useRestoreUnit();

// Computed
const isDeleting = computed(() => deleteUnitMutation.isPending.value);
const isRestoring = computed(() => restoreUnitMutation.isPending.value);

// Methods
const refreshUnits = () => {
  refetch();
};

const confirmDelete = (unit: Unit) => {
  unitToDelete.value = unit;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  unitToDelete.value = null;
};

const deleteUnit = async () => {
  if (!unitToDelete.value) return;

  try {
    await deleteUnitMutation.mutateAsync(unitToDelete.value.id);
    showDeleteModal.value = false;
    unitToDelete.value = null;
  } catch (error) {
    console.error('Failed to delete unit:', error);
  }
};

const confirmRestore = (unit: Unit) => {
  unitToRestore.value = unit;
  showRestoreModal.value = true;
};

const cancelRestore = () => {
  showRestoreModal.value = false;
  unitToRestore.value = null;
};

const restoreUnit = async () => {
  if (!unitToRestore.value) return;

  try {
    await restoreUnitMutation.mutateAsync(unitToRestore.value.id);
    showRestoreModal.value = false;
    unitToRestore.value = null;
  } catch (error) {
    console.error('Failed to restore unit:', error);
  }
};

// Utility functions
const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
};

const formatDate = (dateString: string): string => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
</script>
