<template>
  <div class="min-h-screen flex">
    <!-- Left Section - Auth Form -->
    <div
      class="flex-1 flex flex-col justify-center px-8 py-12 sm:px-12 lg:px-16 xl:px-20 bg-white"
    >
      <div class="mx-auto w-full max-w-sm lg:w-96">
        <!-- Logo -->
        <div class="mb-8">
          <NeibrPayLogo size="lg" />
        </div>

        <!-- Step 1: Email Entry -->
        <div v-if="step === 'email'" class="space-y-6">
          <div>
            <h2 class="text-heading-2 text-text-primary mb-2">
              Welcome to NeibrPay
            </h2>
            <p class="text-body text-text-secondary">
              Enter your email to get started
            </p>
          </div>

          <div
            v-if="errorMessage"
            class="p-4 bg-red-50 border border-red-200 rounded-lg"
          >
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>

          <form @submit.prevent="handleEmailSubmit" class="space-y-6">
            <div>
              <label
                for="email"
                class="block text-sm font-medium text-text-primary mb-2"
              >
                Email
              </label>
              <input
                id="email"
                v-model="email"
                type="email"
                autocomplete="email"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                placeholder="Enter your email"
              />
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                Sending code...
              </span>
              <span v-else>Continue</span>
            </button>
          </form>

          <div class="relative">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-neutral-300" />
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-2 bg-white text-text-secondary">or</span>
            </div>
          </div>

          <button
            type="button"
            @click="handleGoogleAuth"
            :disabled="isLoading"
            class="w-full flex justify-center items-center px-4 py-2 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200 disabled:opacity-50"
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
            Continue with Google
          </button>
        </div>

        <!-- Step 2: Verification Code -->
        <div v-if="step === 'code'" class="space-y-6">
          <div>
            <h2 class="text-heading-2 text-text-primary mb-2">
              Enter verification code
            </h2>
            <p class="text-body text-text-secondary">
              We sent a 6-digit code to <strong>{{ email }}</strong>
            </p>
          </div>

          <div
            v-if="errorMessage"
            class="p-4 bg-red-50 border border-red-200 rounded-lg"
          >
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>

          <VerificationCodeInput
            ref="codeInputRef"
            @submit="handleCodeSubmit"
            @error="handleCodeError"
          />

          <div class="flex items-center justify-between text-sm">
            <button
              type="button"
              @click="resendCode"
              :disabled="!canResend || isLoading"
              class="text-primary hover:text-primary-600 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Resend code{{ countdown > 0 ? ` (${countdown}s)` : '' }}
            </button>
            <button
              type="button"
              @click="goBack"
              class="text-text-secondary hover:text-text-primary"
            >
              Change email
            </button>
          </div>
        </div>

        <!-- Step 3: New User Signup Form -->
        <div v-if="step === 'signup'" class="space-y-6">
          <div>
            <h2 class="text-heading-2 text-text-primary mb-2">
              Complete your signup
            </h2>
            <p class="text-body text-text-secondary">
              Please provide the following information to create your account
            </p>
          </div>

          <div
            v-if="errorMessage"
            class="p-4 bg-red-50 border border-red-200 rounded-lg"
          >
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>

          <form @submit.prevent="handleSignup" class="space-y-6">
            <div>
              <label
                for="fullName"
                class="block text-sm font-medium text-text-primary mb-2"
              >
                Full Name <span class="text-red-500">*</span>
              </label>
              <input
                id="fullName"
                v-model="fullName"
                type="text"
                autocomplete="name"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                placeholder="Enter your full name"
              />
            </div>

            <div>
              <label
                for="phoneNumber"
                class="block text-sm font-medium text-text-primary mb-2"
              >
                Phone Number <span class="text-red-500">*</span>
              </label>
              <input
                id="phoneNumber"
                v-model="formattedPhoneNumber"
                @input="handlePhoneInput"
                type="tel"
                autocomplete="tel"
                required
                maxlength="14"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                placeholder="555-555-5555"
              />
            </div>

            <div>
              <label
                for="communityName"
                class="block text-sm font-medium text-text-primary mb-2"
              >
                Community Name <span class="text-red-500">*</span>
              </label>
              <input
                id="communityName"
                v-model="communityName"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                placeholder="Enter your community name"
              />
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                Creating account...
              </span>
              <span v-else>Complete Signup</span>
            </button>
          </form>
        </div>

        <!-- Step 4: Google Signup Form -->
        <div v-if="step === 'google-signup'" class="space-y-6">
          <div>
            <h2 class="text-heading-2 text-text-primary mb-2">
              Complete your signup
            </h2>
            <p class="text-body text-text-secondary">
              Please provide the following information to create your account
            </p>
          </div>

          <div
            v-if="errorMessage"
            class="p-4 bg-red-50 border border-red-200 rounded-lg"
          >
            <p class="text-sm text-red-800">{{ errorMessage }}</p>
          </div>

          <div class="p-4 bg-neutral-50 rounded-lg">
            <p class="text-sm text-text-secondary mb-1">
              <strong>Email:</strong> {{ googleData.email }}
            </p>
            <p class="text-sm text-text-secondary">
              <strong>Name:</strong> {{ googleData.name }}
            </p>
          </div>

          <form @submit.prevent="handleGoogleSignup" class="space-y-6">
            <div>
              <label
                for="googlePhoneNumber"
                class="block text-sm font-medium text-text-primary mb-2"
              >
                Phone Number <span class="text-red-500">*</span>
              </label>
              <input
                id="googlePhoneNumber"
                v-model="formattedPhoneNumber"
                @input="handlePhoneInput"
                type="tel"
                autocomplete="tel"
                required
                maxlength="14"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                placeholder="555-555-5555"
              />
            </div>

            <div>
              <label
                for="googleCommunityName"
                class="block text-sm font-medium text-text-primary mb-2"
              >
                Community Name <span class="text-red-500">*</span>
              </label>
              <input
                id="googleCommunityName"
                v-model="communityName"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
                placeholder="Enter your community name"
              />
            </div>

            <button
              type="submit"
              :disabled="isLoading"
              class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                Creating account...
              </span>
              <span v-else>Complete Signup</span>
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- Right Section - Illustration -->
    <div
      class="hidden lg:flex lg:flex-1 lg:items-center lg:justify-center bg-gradient-to-br from-primary-50 to-neutral-50 relative overflow-hidden"
    >
      <div class="max-w-md text-center relative z-10">
        <!-- Donut Chart Graphic -->
        <div class="mb-8 flex flex-col items-center">
          <!-- Chart Container -->
          <div class="relative mb-6">
            <!-- Donut Chart SVG -->
            <svg
              width="200"
              height="200"
              viewBox="0 0 200 200"
              class="transform rotate-[-90deg]"
            >
              <!-- Background Circle -->
              <circle
                cx="100"
                cy="100"
                r="80"
                fill="none"
                stroke="#e5e7eb"
                stroke-width="20"
              />
              <!-- Maintenance Segment (35%) -->
              <circle
                cx="100"
                cy="100"
                r="80"
                fill="none"
                stroke="#3b82f6"
                stroke-width="20"
                stroke-dasharray="175.93 502.65"
                stroke-dashoffset="0"
                stroke-linecap="round"
              />
              <!-- Utilities Segment (25%) -->
              <circle
                cx="100"
                cy="100"
                r="80"
                fill="none"
                stroke="#fbbf24"
                stroke-width="20"
                stroke-dasharray="125.66 502.65"
                stroke-dashoffset="-175.93"
                stroke-linecap="round"
              />
              <!-- Insurance Segment (25%) -->
              <circle
                cx="100"
                cy="100"
                r="80"
                fill="none"
                stroke="#10b981"
                stroke-width="20"
                stroke-dasharray="125.66 502.65"
                stroke-dashoffset="-301.59"
                stroke-linecap="round"
              />
              <!-- Other Segment (15%) -->
              <circle
                cx="100"
                cy="100"
                r="80"
                fill="none"
                stroke="#f97316"
                stroke-width="20"
                stroke-dasharray="75.4 502.65"
                stroke-dashoffset="-427.25"
                stroke-linecap="round"
              />
            </svg>

            <!-- Center Circle with Dollar Sign -->
            <div
              class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
            >
              <div
                class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center shadow-sm"
              >
                <svg
                  class="w-10 h-10 text-gray-700"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
            </div>
          </div>

          <!-- Legend -->
          <div class="space-y-3 text-left">
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded-full bg-blue-500"></div>
              <span class="text-sm text-gray-700">Maintenance</span>
              <span class="text-sm font-medium text-gray-900 ml-auto">35%</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded-full bg-yellow-400"></div>
              <span class="text-sm text-gray-700">Utilities</span>
              <span class="text-sm font-medium text-gray-900 ml-auto">25%</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded-full bg-green-500"></div>
              <span class="text-sm text-gray-700">Insurance</span>
              <span class="text-sm font-medium text-gray-900 ml-auto">25%</span>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-4 h-4 rounded-full bg-orange-500"></div>
              <span class="text-sm text-gray-700">Other</span>
              <span class="text-sm font-medium text-gray-900 ml-auto">15%</span>
            </div>
          </div>
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

      <!-- Decorative Icons -->
      <!-- Top Right Bar Chart Icon -->
      <div
        class="absolute top-8 right-8 w-12 h-12 bg-yellow-400 rounded-full flex items-center justify-center shadow-lg z-0"
      >
        <svg
          class="w-6 h-6 text-white"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"
          />
        </svg>
      </div>

      <!-- Bottom Left Dollar Icon -->
      <div
        class="absolute bottom-8 left-8 w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center shadow-lg z-0"
      >
        <svg
          class="w-6 h-6 text-white"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
          />
        </svg>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import NeibrPayLogo from '../components/NeibrPayLogo.vue';
import VerificationCodeInput from '../components/VerificationCodeInput.vue';
import { authService } from '../services/auth';
import { usePostHog } from '../composables/usePostHog';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const { trackOnboardingEvent } = usePostHog();

// Step management
type Step = 'email' | 'code' | 'signup' | 'google-signup';
const step = ref<Step>('email');

// Form state
const email = ref('');
const verificationToken = ref<string | null>(null);
const fullName = ref('');
const phoneNumber = ref('');
const formattedPhoneNumber = ref('');
const communityName = ref('');
const googleData = reactive<{ email: string; name: string; avatar?: string }>({
  email: '',
  name: '',
});
const googleToken = ref<string | null>(null);

// UI state
const isLoading = ref(false);
const errorMessage = ref('');
const codeInputRef = ref<InstanceType<typeof VerificationCodeInput> | null>(
  null
);

// Resend code state
const canResend = ref(true);
const countdown = ref(0);
let countdownInterval: number | null = null;

// Phone number formatting
const formatPhoneNumber = (value: string): string => {
  const phoneNumber = value.replace(/\D/g, '');
  const limitedPhoneNumber = phoneNumber.substring(0, 10);

  if (limitedPhoneNumber.length >= 6) {
    return `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3, 6)}-${limitedPhoneNumber.substring(6)}`;
  } else if (limitedPhoneNumber.length >= 3) {
    return `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3)}`;
  } else {
    return limitedPhoneNumber;
  }
};

const handlePhoneInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const formatted = formatPhoneNumber(target.value);
  formattedPhoneNumber.value = formatted;
  phoneNumber.value = formatted.replace(/\D/g, '');
};

// Email submission
const handleEmailSubmit = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    const emailValue = email.value.trim().toLowerCase();
    if (!emailValue) {
      errorMessage.value = 'Email is required';
      return;
    }

    // Check if email exists
    const checkResult = await authService.checkEmail(emailValue);

    // Track: User entered email (signup started)
    trackOnboardingEvent('signup_email_entered', {
      email: emailValue,
      step: 'email_entry',
    });

    // Send verification code
    await authService.sendVerificationCode(emailValue);

    // Track: Verification code sent
    trackOnboardingEvent('verification_code_sent', {
      email: emailValue,
      step: 'code_sent',
    });

    // Start countdown
    startCountdown(60);

    // Move to code step
    step.value = 'code';
    nextTick(() => {
      codeInputRef.value?.focus();
    });
  } catch (error) {
    errorMessage.value =
      error instanceof Error
        ? error.message
        : 'Failed to send verification code';
  } finally {
    isLoading.value = false;
  }
};

// Code submission
const handleCodeSubmit = async (code: string) => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    const emailValue = email.value.trim().toLowerCase();
    const token = await authService.verifyCode(emailValue, code);
    verificationToken.value = token;

    // Track: Email verified
    trackOnboardingEvent('email_verified', {
      email: emailValue,
      step: 'code_verified',
    });

    // Check if user exists
    const checkResult = await authService.checkEmail(emailValue);

    if (checkResult.exists) {
      // Existing user - login
      await handleLogin();
    } else {
      // New user - show signup form
      step.value = 'signup';
      // Track: Signup form shown
      trackOnboardingEvent('signup_form_shown', {
        email: emailValue,
        step: 'signup_form',
      });
    }
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Invalid verification code';
    codeInputRef.value?.setError(errorMessage.value);
  } finally {
    isLoading.value = false;
  }
};

const handleCodeError = (message: string) => {
  errorMessage.value = message;
};

// Login
const handleLogin = async () => {
  try {
    if (!verificationToken.value) {
      throw new Error('Verification token is missing');
    }

    const result = await authService.login(
      email.value.trim().toLowerCase(),
      verificationToken.value
    );
    authStore.setAuth(result);

    // Track: Login completed
    trackOnboardingEvent('login_completed', {
      email: email.value.trim().toLowerCase(),
      step: 'login_complete',
    });

    // Redirect to dashboard
    router.push('/');
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Login failed';
  }
};

// Signup
const handleSignup = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    if (!verificationToken.value) {
      throw new Error('Verification token is missing');
    }

    if (!fullName.value.trim()) {
      errorMessage.value = 'Full name is required';
      return;
    }

    if (!phoneNumber.value || phoneNumber.value.length !== 10) {
      errorMessage.value = 'Please enter a valid 10-digit phone number';
      return;
    }

    if (!communityName.value.trim()) {
      errorMessage.value = 'Community name is required';
      return;
    }

    const result = await authService.signup(
      {
        email: email.value.trim().toLowerCase(),
        fullName: fullName.value.trim(),
        phoneNumber: phoneNumber.value,
        communityName: communityName.value.trim(),
      },
      verificationToken.value
    );

    authStore.setAuth(result);

    // Track: Signup completed
    trackOnboardingEvent('signup_completed', {
      email: email.value.trim().toLowerCase(),
      community_name: communityName.value.trim(),
      step: 'signup_complete',
    });

    // Redirect to dashboard
    router.push('/');
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Signup failed';
  } finally {
    isLoading.value = false;
  }
};

// Google OAuth
const handleGoogleAuth = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    await authService.initiateGoogleAuth();
  } catch (error) {
    errorMessage.value =
      error instanceof Error
        ? error.message
        : 'Failed to initiate Google authentication';
    isLoading.value = false;
  }
};

// Google signup
const handleGoogleSignup = async () => {
  try {
    isLoading.value = true;
    errorMessage.value = '';

    if (!googleToken.value) {
      throw new Error('Google token is missing');
    }

    if (!phoneNumber.value || phoneNumber.value.length !== 10) {
      errorMessage.value = 'Please enter a valid 10-digit phone number';
      return;
    }

    if (!communityName.value.trim()) {
      errorMessage.value = 'Community name is required';
      return;
    }

    const result = await authService.googleSignup(googleToken.value, {
      phoneNumber: phoneNumber.value,
      communityName: communityName.value.trim(),
    });

    authStore.setAuth(result);

    // Redirect to dashboard
    router.push('/');
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Google signup failed';
  } finally {
    isLoading.value = false;
  }
};

// Resend code
const resendCode = async () => {
  if (!canResend.value || isLoading.value) return;

  try {
    isLoading.value = true;
    errorMessage.value = '';

    await authService.sendVerificationCode(email.value.trim().toLowerCase());
    startCountdown(60);
    codeInputRef.value?.clear();
  } catch (error) {
    errorMessage.value =
      error instanceof Error ? error.message : 'Failed to resend code';
  } finally {
    isLoading.value = false;
  }
};

// Countdown timer
const startCountdown = (seconds: number) => {
  canResend.value = false;
  countdown.value = seconds;

  if (countdownInterval) {
    clearInterval(countdownInterval);
  }

  countdownInterval = window.setInterval(() => {
    countdown.value--;
    if (countdown.value <= 0) {
      canResend.value = true;
      if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
      }
    }
  }, 1000);
};

// Go back
const goBack = () => {
  step.value = 'email';
  errorMessage.value = '';
  codeInputRef.value?.clear();
  if (countdownInterval) {
    clearInterval(countdownInterval);
    countdownInterval = null;
  }
  countdown.value = 0;
  canResend.value = true;
};

// Handle Google OAuth callback and email pre-fill
onMounted(async () => {
  // Check for email parameter to pre-fill (from expired magic link)
  const emailParam = route.query.email as string | undefined;
  if (emailParam && step.value === 'email') {
    email.value = decodeURIComponent(emailParam);
  }

  // Check for Google OAuth callback from backend redirect
  const token = route.query.token as string | undefined;
  const googleTokenParam = route.query.google_token as string | undefined;
  const exists = route.query.exists as string | undefined;
  const error = route.query.error as string | undefined;

  // Check if we have raw Google OAuth parameters (means Google redirected directly to frontend)
  const hasGoogleCode = route.query.code as string | undefined;
  const hasGoogleState = route.query.state as string | undefined;

  // If we have raw Google OAuth parameters, redirect to backend callback
  if (hasGoogleCode && hasGoogleState && !token && !googleTokenParam) {
    // Google redirected directly to frontend - redirect to backend callback
    const apiUrl =
      (import.meta as any).env?.VITE_API_URL || 'http://localhost:8000/api';
    // Remove /api suffix if present, then add /api/auth/google/callback
    const backendBase = apiUrl.replace(/\/api$/, '');
    const callbackUrl = `${backendBase}/api/auth/google/callback?${new URLSearchParams(route.query as Record<string, string>).toString()}`;
    window.location.href = callbackUrl;
    return;
  }

  if (error) {
    errorMessage.value = decodeURIComponent(error);
    step.value = 'email';
    return;
  }

  if (token && exists === 'true') {
    // Existing user - login successful
    try {
      isLoading.value = true;
      // Set token first
      authService.setToken(token);
      // Get user data from backend
      const result = await authService.getCurrentUser();
      authStore.setAuth({
        user: result.user,
        tenant: result.tenant,
        token: token,
      });
      router.push('/');
    } catch (error) {
      errorMessage.value =
        error instanceof Error ? error.message : 'Failed to get user data';
      step.value = 'email';
    } finally {
      isLoading.value = false;
    }
  } else if (googleTokenParam && exists === 'false') {
    // New user - show signup form
    googleData.email = (route.query.email as string) || '';
    googleData.name = (route.query.name as string) || '';
    googleData.avatar = route.query.avatar as string;
    googleToken.value = googleTokenParam;
    step.value = 'google-signup';
  }
});
</script>

<style scoped>
/* Styles are handled by Tailwind classes */
</style>
