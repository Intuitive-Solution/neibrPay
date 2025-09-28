<template>
  <div class="min-h-screen flex">
    <!-- Left Section - Login Form -->
    <div
      class="flex-1 flex flex-col justify-center px-8 py-12 sm:px-12 lg:px-16 xl:px-20 bg-white"
    >
      <div class="mx-auto w-full max-w-sm lg:w-96">
        <!-- Logo -->
        <div class="mb-8">
          <div class="flex items-center space-x-3">
            <img
              src="/owner-logo.png"
              alt="NeibrPay Logo"
              class="h-10 w-10 object-contain"
            />
            <h1 class="text-2xl font-bold">
              <span class="text-primary">Neibr</span>
              <span style="color: #2ee9b6">Pay</span>
            </h1>
          </div>
        </div>

        <!-- Welcome Message -->
        <div class="mb-8">
          <h2 class="text-heading-2 text-text-primary mb-2">Welcome back!</h2>
          <p class="text-body text-text-secondary">
            Sign in to your HOA management dashboard
          </p>
        </div>

        <!-- Login Form -->
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Email Input -->
          <div>
            <label
              for="email"
              class="block text-sm font-medium text-text-primary mb-2"
            >
              Email
            </label>
            <input
              id="email"
              v-model="form.email"
              type="email"
              autocomplete="email"
              required
              class="w-full px-4 py-3 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              placeholder="Enter your email"
            />
          </div>

          <!-- Password Input -->
          <div>
            <label
              for="password"
              class="block text-sm font-medium text-text-primary mb-2"
            >
              Password
            </label>
            <div class="relative">
              <input
                id="password"
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                required
                class="w-full px-4 py-3 pr-12 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                placeholder="Enter your password"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                :aria-label="showPassword ? 'Hide password' : 'Show password'"
              >
                <svg
                  v-if="showPassword"
                  class="h-5 w-5 text-text-secondary"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"
                  />
                </svg>
                <svg
                  v-else
                  class="h-5 w-5 text-text-secondary"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                  />
                </svg>
              </button>
            </div>
          </div>

          <!-- Sign In Button -->
          <button
            type="submit"
            :disabled="isLoading"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
          >
            <span v-if="isLoading" class="flex items-center">
              <svg
                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
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
              Signing in...
            </span>
            <span v-else>Sign In</span>
          </button>
        </form>

        <!-- Divider -->
        <div class="mt-6">
          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-neutral-300" />
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-text-secondary">or</span>
            </div>
          </div>
        </div>

        <!-- Social Login Buttons -->
        <div class="mt-6">
          <!-- Google Login -->
          <button
            type="button"
            @click="handleGoogleLogin"
            class="w-full flex justify-center items-center px-4 py-3 border border-neutral-300 rounded-lg shadow-sm bg-white text-sm font-medium text-text-primary hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
          >
            <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
              <path
                fill="#4285F4"
                d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"
              />
              <path
                fill="#34A853"
                d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"
              />
              <path
                fill="#FBBC05"
                d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"
              />
              <path
                fill="#EA4335"
                d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"
              />
            </svg>
            Login with Google
          </button>
        </div>

        <!-- Account Links -->
        <div class="mt-6 text-center space-y-2">
          <p class="text-small text-text-secondary">
            Don't have an account?
            <router-link
              to="/signup"
              class="font-medium text-primary hover:text-primary-600 transition-colors duration-200"
            >
              Sign Up
            </router-link>
          </p>
          <p class="text-small">
            <a
              href="#"
              class="font-medium text-primary hover:text-primary-600 transition-colors duration-200"
            >
              Forgot password?
            </a>
          </p>
        </div>

        <!-- Legal Text -->
        <div class="mt-8 text-center">
          <p class="text-xs text-text-secondary">
            By clicking "Sign In" you agree to the
            <a
              href="#"
              class="underline hover:text-text-primary transition-colors duration-200"
              >Terms of Service</a
            >
            and acknowledge the
            <a
              href="#"
              class="underline hover:text-text-primary transition-colors duration-200"
              >Privacy Notice</a
            >.
          </p>
        </div>
      </div>
    </div>

    <!-- Right Section - Professional Illustration -->
    <div
      class="hidden lg:flex lg:flex-1 lg:items-center lg:justify-center bg-gradient-to-br from-primary-50 to-neutral-50"
    >
      <div class="max-w-md text-center">
        <!-- Professional Dashboard Illustration -->
        <div class="relative mb-8">
          <!-- Main Dashboard Card -->
          <div class="w-64 h-48 mx-auto relative">
            <!-- Dashboard Background -->
            <div
              class="absolute inset-0 bg-white rounded-xl shadow-xl border border-neutral-200"
            >
              <!-- Header Bar -->
              <div
                class="absolute top-0 left-0 right-0 h-8 bg-primary rounded-t-xl flex items-center px-4"
              >
                <div class="flex space-x-2">
                  <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                  <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                  <div class="w-2 h-2 bg-white rounded-full opacity-60"></div>
                </div>
              </div>

              <!-- Content Area -->
              <div class="absolute top-8 left-0 right-0 bottom-0 p-4">
                <!-- Chart Bars -->
                <div class="flex items-end justify-center space-x-2 mb-4">
                  <div
                    class="w-4 bg-primary-300 rounded-t"
                    style="height: 20px"
                  ></div>
                  <div
                    class="w-4 bg-primary-400 rounded-t"
                    style="height: 30px"
                  ></div>
                  <div
                    class="w-4 bg-primary-500 rounded-t"
                    style="height: 25px"
                  ></div>
                  <div
                    class="w-4 bg-primary-600 rounded-t"
                    style="height: 35px"
                  ></div>
                  <div
                    class="w-4 bg-accent-400 rounded-t"
                    style="height: 28px"
                  ></div>
                </div>

                <!-- Invoice Cards -->
                <div class="space-y-2">
                  <div
                    class="flex items-center justify-between p-2 bg-neutral-50 rounded"
                  >
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 bg-success rounded-full"></div>
                      <span class="text-xs text-text-secondary"
                        >Invoice #001</span
                      >
                    </div>
                    <span class="text-xs font-medium text-success">$1,250</span>
                  </div>
                  <div
                    class="flex items-center justify-between p-2 bg-neutral-50 rounded"
                  >
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 bg-warning rounded-full"></div>
                      <span class="text-xs text-text-secondary"
                        >Invoice #002</span
                      >
                    </div>
                    <span class="text-xs font-medium text-warning">$850</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Floating Elements -->
            <div
              class="absolute -top-4 -right-4 w-8 h-8 bg-accent-400 rounded-full flex items-center justify-center shadow-lg"
            >
              <svg
                class="w-4 h-4 text-white"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                />
              </svg>
            </div>

            <div
              class="absolute -bottom-2 -left-2 w-6 h-6 bg-primary-400 rounded-full flex items-center justify-center shadow-lg"
            >
              <svg
                class="w-3 h-3 text-white"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
            </div>
          </div>

          <!-- Background Elements -->
          <div
            class="absolute top-8 left-8 w-3 h-3 bg-primary-200 rounded-full animate-pulse"
          ></div>
          <div
            class="absolute top-16 right-12 w-2 h-2 bg-accent-300 rounded-full animate-pulse"
            style="animation-delay: 0.5s"
          ></div>
          <div
            class="absolute bottom-16 left-12 w-2 h-2 bg-primary-300 rounded-full animate-pulse"
            style="animation-delay: 1s"
          ></div>
          <div
            class="absolute bottom-8 right-8 w-3 h-3 bg-accent-200 rounded-full animate-pulse"
            style="animation-delay: 1.5s"
          ></div>
        </div>

        <!-- Professional Text -->
        <div class="space-y-4">
          <h3 class="text-heading-3 text-text-primary">
            Streamline Your HOA Management
          </h3>
          <p class="text-body text-text-secondary">
            Professional tools for invoice management, financial tracking, and
            community communication.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

// Form state
const form = reactive({
  email: '',
  password: '',
});

// UI state
const showPassword = ref(false);
const isLoading = ref(false);

// Form handlers
const handleLogin = async () => {
  isLoading.value = true;

  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500));

    // For demo purposes, redirect to dashboard
    router.push('/');
  } catch (error) {
    console.error('Login failed:', error);
  } finally {
    isLoading.value = false;
  }
};

const handleGoogleLogin = () => {
  // Implement Google OAuth
  console.log('Google login clicked');
};
</script>

<style scoped>
/* Custom animations */
@keyframes float {
  0%,
  100% {
    transform: translateY(0px);
  }
  50% {
    transform: translateY(-10px);
  }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

/* Focus styles for accessibility */
input:focus,
button:focus {
  outline: 2px solid transparent;
  outline-offset: 2px;
}

/* Custom scrollbar for better UX */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
  background: #c1c1c1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a8a8a8;
}
</style>
