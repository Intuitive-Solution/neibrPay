<template>
  <div class="space-y-6">
    <!-- Header: Add Resident Button -->
    <div class="flex justify-end">
      <router-link to="/people/add" class="hidden md:inline-flex">
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
          Add Resident
        </button>
      </router-link>
    </div>

    <!-- Resident Directory -->
    <div ref="residentsSection">
      <ResidentList
        @add-resident="navigateToAddResident"
        @edit-resident="navigateToEditResident"
      />
    </div>

    <!-- Mobile Fixed Bottom Button -->
    <div
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/people/add" class="block">
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
          Add Resident
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import ResidentList from '../components/ResidentList.vue';
import type { Resident } from '@neibrpay/models';

const router = useRouter();
const residentsSection = ref<HTMLElement>();

// Navigation methods
const navigateToAddResident = () => {
  router.push('/people/add');
};

const navigateToEditResident = (resident: Resident) => {
  router.push(`/people/edit/${resident.id}`);
};

const scrollToResidents = () => {
  residentsSection.value?.scrollIntoView({ behavior: 'smooth' });
};
</script>
