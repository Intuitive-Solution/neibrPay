<template>
  <div id="app">
    <!-- Header only shown when not on login or signup page -->
    <header
      v-if="$route.name !== 'Login' && $route.name !== 'Signup'"
      class="text-white p-4 bg-primary"
    >
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">NeibrPay - HOA Manager Dashboard</h1>

        <!-- Quick logout button in header -->
        <button
          @click="handleQuickLogout"
          :disabled="authStore.isLoading"
          class="inline-flex items-center px-3 py-2 border border-white/20 text-sm font-medium rounded-md text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-white/50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          title="Sign Out"
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
          <span class="hidden sm:inline">{{
            authStore.isLoading ? 'Signing out...' : 'Sign Out'
          }}</span>
        </button>
      </div>
    </header>

    <!-- Main content with conditional styling -->
    <main
      :class="
        $route.name === 'Login' ||
        $route.name === 'Signup' ||
        $route.name === 'TermsOfService' ||
        $route.name === 'PrivacyNotice'
          ? ''
          : 'container mx-auto p-4'
      "
    >
      <router-view />
    </main>

    <!-- Footer with legal links (only on main pages) -->
    <footer
      v-if="
        $route.name !== 'Login' &&
        $route.name !== 'Signup' &&
        $route.name !== 'TermsOfService' &&
        $route.name !== 'PrivacyNotice'
      "
      class="bg-gray-800 text-white py-8 mt-12"
    >
      <div class="container mx-auto px-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <!-- Company Info -->
          <div>
            <h3 class="text-lg font-semibold mb-4">NeibrPay</h3>
            <p class="text-gray-300 text-sm">
              Comprehensive HOA management platform for modern communities.
            </p>
          </div>

          <!-- Quick Links -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
            <ul class="space-y-2">
              <li>
                <router-link
                  to="/"
                  class="text-gray-300 hover:text-white text-sm transition-colors"
                >
                  Dashboard
                </router-link>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-300 hover:text-white text-sm transition-colors"
                >
                  Support
                </a>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-300 hover:text-white text-sm transition-colors"
                >
                  Documentation
                </a>
              </li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h3 class="text-lg font-semibold mb-4">Legal</h3>
            <ul class="space-y-2">
              <li>
                <router-link
                  to="/terms-of-service"
                  class="text-gray-300 hover:text-white text-sm transition-colors"
                >
                  Terms of Service
                </router-link>
              </li>
              <li>
                <router-link
                  to="/privacy-notice"
                  class="text-gray-300 hover:text-white text-sm transition-colors"
                >
                  Privacy Notice
                </router-link>
              </li>
              <li>
                <a
                  href="#"
                  class="text-gray-300 hover:text-white text-sm transition-colors"
                >
                  Cookie Policy
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Copyright -->
        <div class="border-t border-gray-700 mt-8 pt-8 text-center">
          <p class="text-gray-400 text-sm">
            © {{ currentYear }} NeibrPay. All rights reserved.
          </p>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from './stores/auth';

const $route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Current year for copyright
const currentYear = computed(() => new Date().getFullYear());

// Handle quick logout from header
const handleQuickLogout = async () => {
  try {
    await authStore.signout();
    // Redirect to login page after successful logout
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
    // Still redirect to login even if logout fails
    router.push('/login');
  }
};
</script>

<style>
#app {
  font-family: 'Inter', 'Roboto', 'Helvetica Neue', Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
