<template>
  <div
    class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8"
  >
    <div class="max-w-md w-full space-y-8">
      <div v-if="isLoading" class="text-center">
        <div
          class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"
        ></div>
        <p class="mt-4 text-sm text-gray-600">Authenticating...</p>
      </div>

      <div v-else-if="error" class="text-center">
        <div class="text-red-600 mb-4">
          <svg
            class="mx-auto h-12 w-12"
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
        <h3 class="text-lg font-medium text-gray-900 mb-2">
          Authentication Failed
        </h3>
        <p class="text-sm text-gray-600 mb-4">
          {{ error }}
        </p>
        <button @click="router.push('/login')" class="btn-primary">
          Go to Login
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const isLoading = ref(true);
const error = ref<string | null>(null);

onMounted(async () => {
  try {
    // Extract token from query parameters
    const token = route.query.token as string;

    if (!token) {
      error.value = 'No authentication token provided in the link.';
      isLoading.value = false;
      return;
    }

    // Use the auth store's signinWithMagicLink method which handles everything
    await authStore.signinWithMagicLink(token);

    // Redirect to dashboard on success
    router.push('/');
    isLoading.value = false;
  } catch (err: any) {
    console.error('Magic link authentication error:', err);
    error.value =
      err.message ||
      'Failed to authenticate. The link may have expired or is invalid.';
    isLoading.value = false;
  }
});
</script>
