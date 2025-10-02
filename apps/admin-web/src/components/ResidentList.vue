<template>
  <div class="bg-white shadow rounded-lg">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Resident Directory</h2>
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
            @click="refreshResidents"
            :disabled="isLoading"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50"
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
    </div>

    <div v-if="isLoading" class="p-6">
      <div class="flex items-center justify-center py-12">
        <svg
          class="animate-spin h-8 w-8 text-primary"
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
        <span class="ml-2 text-gray-600">Loading residents...</span>
      </div>
    </div>

    <div v-else-if="error" class="p-6">
      <div class="rounded-md bg-red-50 p-4">
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
            <h3 class="text-sm font-medium text-red-800">
              Error loading residents
            </h3>
            <p class="mt-1 text-sm text-red-700">{{ error.message }}</p>
            <div class="mt-3">
              <button
                @click="refreshResidents"
                class="text-sm bg-red-100 text-red-800 rounded-md px-3 py-1 hover:bg-red-200"
              >
                Try again
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="!residents || residents.length === 0" class="p-6">
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
            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
          />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">
          {{ includeDeleted ? 'No deleted residents' : 'No residents yet' }}
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          {{
            includeDeleted
              ? 'No residents have been deleted.'
              : 'Get started by adding your first resident.'
          }}
        </p>
        <div v-if="!includeDeleted" class="mt-6">
          <button
            @click="$emit('add-resident')"
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
            Add Resident
          </button>
        </div>
      </div>
    </div>

    <div v-else class="overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Name
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Email
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Phone
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Status
              </th>
              <th
                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
              >
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="resident in residents"
              :key="resident.id"
              class="hover:bg-gray-50"
              :class="{ 'opacity-50': resident.deleted_at }"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10">
                    <div
                      class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center"
                    >
                      <span class="text-sm font-medium text-gray-700">
                        {{ resident.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">
                      {{ resident.name }}
                    </div>
                    <div class="text-sm text-gray-500">
                      ID: {{ resident.id }}
                    </div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ resident.email }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ resident.phone }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  v-if="resident.deleted_at"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800"
                >
                  Deleted
                </span>
                <span
                  v-else-if="resident.is_active"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800"
                >
                  Active
                </span>
                <span
                  v-else
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800"
                >
                  Inactive
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                <div class="flex items-center space-x-2">
                  <button
                    v-if="!resident.deleted_at"
                    @click="$emit('edit-resident', resident)"
                    class="text-primary hover:text-primary-600"
                  >
                    Edit
                  </button>
                  <button
                    v-if="!resident.deleted_at"
                    @click="confirmDelete(resident)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                  <button
                    v-if="resident.deleted_at"
                    @click="confirmRestore(resident)"
                    class="text-green-600 hover:text-green-900"
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
          <h3 class="text-lg font-medium text-gray-900 mt-4">
            Delete Resident
          </h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to delete
              <strong>{{ residentToDelete?.name }}</strong
              >? This action can be undone by restoring the resident.
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="cancelDelete"
              class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Cancel
            </button>
            <button
              @click="deleteResident"
              :disabled="isDeleting"
              class="px-4 py-2 bg-red-500 text-white text-base font-medium rounded-md w-24 hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 disabled:opacity-50"
            >
              {{ isDeleting ? 'Deleting...' : 'Delete' }}
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
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <h3 class="text-lg font-medium text-gray-900 mt-4">
            Restore Resident
          </h3>
          <div class="mt-2 px-7 py-3">
            <p class="text-sm text-gray-500">
              Are you sure you want to restore
              <strong>{{ residentToRestore?.name }}</strong
              >?
            </p>
          </div>
          <div class="items-center px-4 py-3">
            <button
              @click="cancelRestore"
              class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-24 mr-2 hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-300"
            >
              Cancel
            </button>
            <button
              @click="restoreResident"
              :disabled="isRestoring"
              class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-24 hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300 disabled:opacity-50"
            >
              {{ isRestoring ? 'Restoring...' : 'Restore' }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
  useResidents,
  useDeleteResident,
  useRestoreResident,
} from '../composables/useResidents';
import type { Resident } from '@neibrpay/models';

// Props
defineProps<{
  includeDeleted?: boolean;
}>();

// Emits
const emit = defineEmits<{
  'add-resident': [];
  'edit-resident': [resident: Resident];
}>();

// State
const includeDeleted = ref(false);
const showDeleteModal = ref(false);
const showRestoreModal = ref(false);
const residentToDelete = ref<Resident | null>(null);
const residentToRestore = ref<Resident | null>(null);

// Queries and mutations
const {
  data: residents,
  isLoading,
  error,
  refetch,
} = useResidents(includeDeleted.value);
const deleteResidentMutation = useDeleteResident();
const restoreResidentMutation = useRestoreResident();

// Computed
const isDeleting = computed(() => deleteResidentMutation.isPending.value);
const isRestoring = computed(() => restoreResidentMutation.isPending.value);

// Methods
const refreshResidents = () => {
  refetch();
};

const confirmDelete = (resident: Resident) => {
  residentToDelete.value = resident;
  showDeleteModal.value = true;
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  residentToDelete.value = null;
};

const deleteResident = async () => {
  if (!residentToDelete.value) return;

  try {
    await deleteResidentMutation.mutateAsync(residentToDelete.value.id);
    showDeleteModal.value = false;
    residentToDelete.value = null;
  } catch (error) {
    console.error('Failed to delete resident:', error);
  }
};

const confirmRestore = (resident: Resident) => {
  residentToRestore.value = resident;
  showRestoreModal.value = true;
};

const cancelRestore = () => {
  showRestoreModal.value = false;
  residentToRestore.value = null;
};

const restoreResident = async () => {
  if (!residentToRestore.value) return;

  try {
    await restoreResidentMutation.mutateAsync(residentToRestore.value.id);
    showRestoreModal.value = false;
    residentToRestore.value = null;
  } catch (error) {
    console.error('Failed to restore resident:', error);
  }
};

// Watch for includeDeleted changes to refetch data
watch(includeDeleted, () => {
  refetch();
});
</script>
