<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <div class="w-64 bg-white shadow-lg flex flex-col">
      <!-- Community Header -->
      <div class="p-6">
        <div class="flex items-center space-x-3">
          <img
            src="/owner-logo.png"
            alt="NeibrPay Logo"
            class="h-10 w-10 object-contain rounded-lg"
          />
          <div>
            <h1 class="text-xl font-bold">
              <span class="text-primary">Neibr</span>
              <span style="color: #2ee9b6">Pay</span>
            </h1>
            <p class="text-sm text-gray-600 mt-1">{{ communityName }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav class="flex-1 px-4 py-6">
        <ul class="space-y-2">
          <li>
            <router-link
              to="/"
              :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Dashboard'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              <svg
                class="w-5 h-5 mr-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"
                />
              </svg>
              Dashboard
            </router-link>
          </li>

          <li>
            <router-link
              to="/invoices"
              :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Invoices'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              <svg
                class="w-5 h-5 mr-3"
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
              Invoices
            </router-link>
          </li>

          <li>
            <router-link
              to="/people"
              :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'People'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              <svg
                class="w-5 h-5 mr-3"
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
              People
            </router-link>
          </li>

          <li>
            <router-link
              to="/payments"
              :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Payments'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              <svg
                class="w-5 h-5 mr-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                />
              </svg>
              Payments
            </router-link>
          </li>

          <li>
            <router-link
              to="/vendors"
              :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Vendors'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              <svg
                class="w-5 h-5 mr-3"
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
              Vendors
            </router-link>
          </li>

          <li>
            <router-link
              to="/settings"
              :class="[
                'flex items-center px-4 py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Settings'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
              ]"
            >
              <svg
                class="w-5 h-5 mr-3"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              Settings
            </router-link>
          </li>
        </ul>
      </nav>

      <!-- User Info & Logout -->
      <div class="p-4 border-t border-gray-200">
        <div class="flex items-center space-x-3 mb-4">
          <div
            class="w-8 h-8 bg-primary rounded-full flex items-center justify-center"
          >
            <span class="text-white font-medium text-sm">
              {{ userInitials }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ userDisplayName }}
            </p>
            <p class="text-xs text-gray-500 truncate">{{ userEmail }}</p>
          </div>
        </div>

        <button
          @click="handleLogout"
          :disabled="isLoading"
          class="w-full flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg
            v-if="isLoading"
            class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-500"
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
          {{ isLoading ? 'Signing out...' : 'Sign Out' }}
        </button>
      </div>
    </div>

    <!-- Main Content Area -->
    <div
      class="flex-1 flex flex-col overflow-hidden"
      style="background-color: #f3f8fd"
    >
      <!-- Top Header -->
      <header class="px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">
              {{ pageTitle }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">{{ pageDescription }}</p>
          </div>

          <!-- Search Bar -->
          <div class="flex items-center space-x-4">
            <div class="relative">
              <input
                type="text"
                placeholder="Search..."
                class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200"
              />
              <svg
                class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
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
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 overflow-auto p-6" style="background-color: #f3f8fd">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Computed properties
const communityName = computed(() => authStore.tenantName || 'Community');
const userDisplayName = computed(() => authStore.userDisplayName || 'User');
const userEmail = computed(() => authStore.user?.email || '');
const userInitials = computed(() => {
  const name = userDisplayName.value;
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});
const isLoading = computed(() => authStore.isLoading);

// Page title and description based on current route
const pageTitle = computed(() => {
  switch (route.name) {
    case 'Dashboard':
      return 'Dashboard';
    case 'Invoices':
      return 'Invoices';
    case 'People':
      return 'People';
    case 'Payments':
      return 'Payments';
    case 'Vendors':
      return 'Vendors';
    case 'Settings':
      return 'Settings';
    default:
      return 'Dashboard';
  }
});

const pageDescription = computed(() => {
  switch (route.name) {
    case 'Dashboard':
      return 'Overview of your HOA community management';
    case 'Invoices':
      return 'Manage community invoices and billing';
    case 'People':
      return 'Manage residents and community members';
    case 'Payments':
      return 'Track payments and financial transactions';
    case 'Vendors':
      return 'Manage vendors and service providers';
    case 'Settings':
      return 'Configure your community settings';
    default:
      return 'Overview of your HOA community management';
  }
});

// Handle logout
const handleLogout = async () => {
  try {
    await authStore.signout();
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
    router.push('/login');
  }
};
</script>

<style scoped>
/* Custom styles for the sidebar */
.router-link-active {
  @apply bg-primary text-white;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
