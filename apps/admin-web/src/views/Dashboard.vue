<template>
  <div class="dashboard">
    <!-- Header with user info and logout -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h2 class="text-2xl font-semibold text-gray-900">Dashboard</h2>
        <p class="text-gray-600 mt-1">
          Welcome back, {{ authStore.userDisplayName }}!
        </p>
        <p class="text-sm text-gray-500" v-if="authStore.tenantName">
          {{ authStore.tenantName }}
        </p>
      </div>

      <!-- User menu with logout -->
      <div class="flex items-center space-x-4">
        <!-- User avatar and info -->
        <div class="flex items-center space-x-3">
          <div
            class="w-10 h-10 bg-primary rounded-full flex items-center justify-center"
          >
            <span class="text-white font-medium text-sm">
              {{ authStore.userDisplayName.charAt(0).toUpperCase() }}
            </span>
          </div>
          <div class="hidden sm:block">
            <p class="text-sm font-medium text-gray-900">
              {{ authStore.userDisplayName }}
            </p>
            <p class="text-xs text-gray-500">{{ authStore.user?.email }}</p>
          </div>
        </div>

        <!-- Logout button -->
        <button
          @click="handleLogout"
          :disabled="authStore.isLoading"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
        >
          <svg
            v-if="authStore.isLoading"
            class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
          <svg
            v-else
            class="w-4 h-4 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
            />
          </svg>
          {{ authStore.isLoading ? 'Signing out...' : 'Sign Out' }}
        </button>
      </div>
    </div>

    <!-- Error message -->
    <div
      v-if="errorMessage"
      class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ errorMessage }}</p>
        </div>
      </div>
    </div>

    <!-- Dashboard content -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Financial Overview</h3>
        <p class="text-gray-600">Quick financial summary will go here</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Quick Actions</h3>
        <p class="text-gray-600">Common actions will go here</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-medium mb-2">Recent Activity</h3>
        <p class="text-gray-600">Recent activity feed will go here</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const router = useRouter();
const authStore = useAuthStore();

// Error message for logout failures
const errorMessage = ref('');

// Handle logout
const handleLogout = async () => {
  try {
    errorMessage.value = '';
    await authStore.signout();
    // Redirect to login page after successful logout
    router.push('/login');
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Logout failed';
  }
};
</script>
