<template>
  <div class="space-y-6">
    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <button
        @click="navigateToAddResident"
        class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow cursor-pointer"
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
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
              />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">Add Resident</h3>
            <p class="text-sm text-gray-600">Register new resident</p>
          </div>
        </div>
      </button>

      <button
        @click="scrollToResidents"
        class="bg-white rounded-lg shadow p-6 hover:shadow-md transition-shadow cursor-pointer"
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
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
              />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">
              View All Residents
            </h3>
            <p class="text-sm text-gray-600">Browse resident directory</p>
          </div>
        </div>
      </button>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="p-3 bg-purple-100 rounded-lg">
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
                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-semibold text-gray-900">Manage Groups</h3>
            <p class="text-sm text-gray-600">Organize residents</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Resident Directory -->
    <div ref="residentsSection">
      <ResidentList
        @add-resident="navigateToAddResident"
        @edit-resident="navigateToEditResident"
      />
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
