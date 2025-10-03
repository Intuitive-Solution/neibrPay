<template>
  <div class="space-y-6">
    <!-- Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Add Unit Card -->
      <div
        @click="goToAddUnit"
        class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 cursor-pointer group"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div
              class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200"
            >
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
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Add Unit</h3>
            <p class="text-sm text-gray-500">Register new unit</p>
          </div>
        </div>
      </div>

      <!-- View All Units Card -->
      <div
        @click="refreshUnits"
        class="bg-white p-6 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 cursor-pointer group"
      >
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div
              class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200"
            >
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
                  d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">View All Units</h3>
            <p class="text-sm text-gray-500">Browse unit directory</p>
          </div>
        </div>
      </div>

      <!-- Manage Units Card -->
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div
              class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center"
            >
              <svg
                class="w-6 h-6 text-purple-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
                />
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Manage Units</h3>
            <p class="text-sm text-gray-500">Organize units</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Unit List -->
    <UnitList @add-unit="goToAddUnit" @edit-unit="goToEditUnit" />
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import UnitList from '../components/UnitList.vue';
import type { Unit } from '@neibrpay/models';

// Router
const router = useRouter();

// Methods
const goToAddUnit = () => {
  router.push('/units/add');
};

const goToEditUnit = (unit: Unit) => {
  router.push(`/units/edit/${unit.id}`);
};

const refreshUnits = () => {
  // This will be handled by the UnitList component's refresh functionality
  window.location.reload();
};
</script>
