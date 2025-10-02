<template>
  <div class="min-h-screen flex">
    <!-- Left Section - Reset Password Form -->
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
            Set new password
          </h2>
          <p class="text-body text-text-secondary">
            Enter your new password below. Make sure it's secure and easy to
            remember.
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

        <!-- Invalid Link Message -->
        <div
          v-if="invalidLink"
          class="mb-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-yellow-400"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-yellow-800">{{ invalidLinkMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Reset Password Form -->
        <form
          v-if="!passwordReset && !invalidLink"
          @submit.prevent="handleResetPassword"
          class="space-y-6"
        >
          <!-- New Password Input -->
          <div>
            <label
              for="newPassword"
              class="block text-sm font-medium text-text-primary mb-2"
            >
              New Password
            </label>
            <div class="relative">
              <input
                id="newPassword"
                v-model="form.newPassword"
                :type="showNewPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="w-full px-4 py-3 pr-12 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                placeholder="Enter your new password"
              />
              <button
                type="button"
                @click="showNewPassword = !showNewPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                :aria-label="
                  showNewPassword ? 'Hide password' : 'Show password'
                "
              >
                <svg
                  v-if="showNewPassword"
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
            <!-- Password Requirements -->
            <div class="mt-2 text-xs text-text-secondary">
              <p>Password must be at least 8 characters long</p>
            </div>
          </div>

          <!-- Confirm Password Input -->
          <div>
            <label
              for="confirmPassword"
              class="block text-sm font-medium text-text-primary mb-2"
            >
              Confirm New Password
            </label>
            <div class="relative">
              <input
                id="confirmPassword"
                v-model="form.confirmPassword"
                :type="showConfirmPassword ? 'text' : 'password'"
                autocomplete="new-password"
                required
                class="w-full px-4 py-3 pr-12 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                placeholder="Confirm your new password"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                :aria-label="
                  showConfirmPassword ? 'Hide password' : 'Show password'
                "
              >
                <svg
                  v-if="showConfirmPassword"
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
            <!-- Password Match Indicator -->
            <div v-if="form.confirmPassword" class="mt-2 text-xs">
              <p v-if="passwordsMatch" class="text-green-600">
                ✓ Passwords match
              </p>
              <p v-else class="text-red-600">✗ Passwords do not match</p>
            </div>
          </div>

          <!-- Reset Password Button -->
          <button
            type="submit"
            :disabled="isLoading || !passwordsMatch || !isPasswordValid"
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
              Resetting...
            </span>
            <span v-else>Reset Password</span>
          </button>
        </form>

        <!-- Password Reset Success -->
        <div v-if="passwordReset" class="space-y-6">
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
              Password Reset Successful
            </h3>
            <p class="text-body text-text-secondary">
              Your password has been successfully updated. You can now sign in
              with your new password.
            </p>
          </div>

          <button
            @click="goToLogin"
            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
          >
            Sign In
          </button>
        </div>

        <!-- Invalid Link Actions -->
        <div v-if="invalidLink" class="space-y-6">
          <div class="space-y-4">
            <button
              @click="goToForgotPassword"
              class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
            >
              Request New Reset Link
            </button>

            <button
              @click="goToLogin"
              class="w-full flex justify-center py-3 px-4 border border-neutral-300 rounded-lg shadow-sm text-sm font-medium text-text-primary bg-white hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
            >
              Back to Login
            </button>
          </div>
        </div>

        <!-- Back to Login Link -->
        <div v-if="!passwordReset && !invalidLink" class="mt-6 text-center">
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
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"
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
                        >Strong Password</span
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
                        >Secure Reset</span
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
            Secure Password Update
          </h3>
          <p class="text-body text-text-secondary">
            Choose a strong, unique password to keep your account secure and
            protected.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { authService } from '../services/auth';

const router = useRouter();
const route = useRoute();

// Form state
const form = reactive({
  newPassword: '',
  confirmPassword: '',
});

// UI state
const isLoading = ref(false);
const passwordReset = ref(false);
const invalidLink = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const invalidLinkMessage = ref('');
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);

// Action code from URL
const actionCode = ref('');

// Computed properties
const passwordsMatch = computed(() => {
  return (
    form.newPassword &&
    form.confirmPassword &&
    form.newPassword === form.confirmPassword
  );
});

const isPasswordValid = computed(() => {
  return form.newPassword && form.newPassword.length >= 8;
});

// Extract action code from URL on mount
onMounted(() => {
  const oobCode = route.query.oobCode as string;
  const mode = route.query.mode as string;

  if (!oobCode || mode !== 'resetPassword') {
    invalidLink.value = true;
    invalidLinkMessage.value =
      'This password reset link is invalid or has expired.';
    return;
  }

  actionCode.value = oobCode;
});

// Form handlers
const handleResetPassword = async () => {
  try {
    errorMessage.value = '';
    successMessage.value = '';

    // Validate form
    if (!form.newPassword) {
      errorMessage.value = 'New password is required';
      return;
    }

    if (!form.confirmPassword) {
      errorMessage.value = 'Please confirm your new password';
      return;
    }

    if (!passwordsMatch.value) {
      errorMessage.value = 'Passwords do not match';
      return;
    }

    if (!isPasswordValid.value) {
      errorMessage.value = 'Password must be at least 8 characters long';
      return;
    }

    if (!actionCode.value) {
      errorMessage.value = 'Invalid reset link';
      return;
    }

    isLoading.value = true;

    await authService.confirmPasswordReset(actionCode.value, form.newPassword);

    passwordReset.value = true;
    successMessage.value = 'Your password has been successfully reset!';
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Failed to reset password';

    // Check if it's an invalid/expired link error
    if (
      error instanceof Error &&
      (error.message.includes('invalid') || error.message.includes('expired'))
    ) {
      invalidLink.value = true;
      invalidLinkMessage.value = error.message;
    }
  } finally {
    isLoading.value = false;
  }
};

const goToLogin = () => {
  router.push('/login');
};

const goToForgotPassword = () => {
  router.push('/forgot-password');
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
