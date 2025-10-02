<template>
  <div class="min-h-screen flex">
    <!-- Left Section - Forgot Password Form -->
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
              class="h-10 w-10 object-contain rounded-lg"
            />
            <h1 class="text-2xl font-bold">
              <span class="text-primary">Neibr</span>
              <span style="color: #2ee9b6">Pay</span>
            </h1>
          </div>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <h2 class="text-heading-2 text-text-primary mb-2">
            Reset your password
          </h2>
          <p class="text-body text-text-secondary">
            Enter your email address and we'll send you a link to reset your
            password.
          </p>
        </div>

        <!-- Success Message -->
        <div
          v-if="successMessage"
          class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-green-400"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-800">{{ successMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Error Message -->
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

        <!-- Forgot Password Form -->
        <form
          v-if="!emailSent"
          @submit.prevent="handleForgotPassword"
          class="space-y-6"
        >
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
              placeholder="Enter your email address"
            />
          </div>

          <!-- Send Reset Email Button -->
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
              Sending...
            </span>
            <span v-else>Send Reset Link</span>
          </button>
        </form>

        <!-- Email Sent Confirmation -->
        <div v-if="emailSent" class="space-y-6">
          <div class="text-center">
            <div
              class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4"
            >
              <svg
                class="h-6 w-6 text-green-600"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-text-primary mb-2">
              Check your email
            </h3>
            <p class="text-body text-text-secondary">
              We've sent a password reset link to
              <strong>{{ form.email }}</strong>
            </p>
          </div>

          <div class="space-y-4">
            <button
              @click="resendEmail"
              :disabled="isLoading"
              class="w-full flex justify-center py-3 px-4 border border-neutral-300 rounded-lg shadow-sm text-sm font-medium text-text-primary bg-white hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <span v-if="isLoading" class="flex items-center">
                <svg
                  class="animate-spin -ml-1 mr-3 h-5 w-5 text-text-primary"
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
                Resending...
              </span>
              <span v-else>Resend Email</span>
            </button>

            <button
              @click="goBackToLogin"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg text-sm font-medium text-primary hover:text-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
            >
              Back to Login
            </button>
          </div>
        </div>

        <!-- Back to Login Link -->
        <div v-if="!emailSent" class="mt-6 text-center">
          <p class="text-small text-text-secondary">
            Remember your password?
            <router-link
              to="/login"
              class="font-medium text-primary hover:text-primary-600 transition-colors duration-200"
            >
              Sign In
            </router-link>
          </p>
        </div>

        <!-- Legal Text -->
        <div class="mt-8 text-center">
          <p class="text-xs text-text-secondary">
            By using this service, you agree to the
            <a
              href="/terms-of-service"
              class="underline hover:text-text-primary transition-colors duration-200"
              >Terms of Service</a
            >
            and acknowledge the
            <a
              href="/privacy-notice"
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
        <!-- Security Illustration -->
        <div class="relative mb-8">
          <!-- Security Shield -->
          <div class="w-64 h-48 mx-auto relative">
            <!-- Shield Background -->
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
                <!-- Security Icon -->
                <div class="flex justify-center mb-4">
                  <div
                    class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center"
                  >
                    <svg
                      class="w-8 h-8 text-primary"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                      />
                    </svg>
                  </div>
                </div>

                <!-- Security Features -->
                <div class="space-y-2">
                  <div
                    class="flex items-center justify-between p-2 bg-neutral-50 rounded"
                  >
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 bg-success rounded-full"></div>
                      <span class="text-xs text-text-secondary"
                        >Secure Reset</span
                      >
                    </div>
                    <svg
                      class="w-4 h-4 text-success"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                      />
                    </svg>
                  </div>
                  <div
                    class="flex items-center justify-between p-2 bg-neutral-50 rounded"
                  >
                    <div class="flex items-center space-x-2">
                      <div class="w-2 h-2 bg-primary rounded-full"></div>
                      <span class="text-xs text-text-secondary"
                        >Email Verification</span
                      >
                    </div>
                    <svg
                      class="w-4 h-4 text-primary"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        fill-rule="evenodd"
                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                        clip-rule="evenodd"
                      />
                    </svg>
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
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
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
                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
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
            Secure Password Recovery
          </h3>
          <p class="text-body text-text-secondary">
            Your account security is our priority. We'll send you a secure link
            to reset your password.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { authService } from '../services/auth';

const router = useRouter();

// Form state
const form = reactive({
  email: '',
});

// UI state
const isLoading = ref(false);
const emailSent = ref(false);
const errorMessage = ref('');
const successMessage = ref('');

// Form handlers
const handleForgotPassword = async () => {
  try {
    errorMessage.value = '';
    successMessage.value = '';

    // Validate form
    if (!form.email.trim()) {
      errorMessage.value = 'Email is required';
      return;
    }

    // Basic email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(form.email.trim())) {
      errorMessage.value = 'Please enter a valid email address';
      return;
    }

    isLoading.value = true;

    await authService.sendPasswordResetEmail(form.email.trim());

    emailSent.value = true;
    successMessage.value = 'Password reset email sent successfully!';
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Failed to send reset email';
  } finally {
    isLoading.value = false;
  }
};

const resendEmail = async () => {
  try {
    errorMessage.value = '';
    isLoading.value = true;

    await authService.sendPasswordResetEmail(form.email.trim());

    successMessage.value = 'Password reset email sent again!';
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Failed to resend email';
  } finally {
    isLoading.value = false;
  }
};

const goBackToLogin = () => {
  router.push('/login');
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
