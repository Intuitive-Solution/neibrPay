<template>
  <div class="space-y-6">
    <!-- Header: Add Unit Button -->
    <div class="flex justify-end">
      <router-link to="/units/add" class="hidden md:inline-flex">
        <button class="btn-primary">
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

    <!-- Unit List -->
    <UnitList @add-unit="goToAddUnit" @edit-unit="goToEditUnit" />

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
