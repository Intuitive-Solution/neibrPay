<template>
  <div>
    <AppHeader />
    <main class="bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <h1 class="text-4xl md:text-5xl font-bold text-primary-800 mb-4">
          Support
        </h1>
        <p class="text-gray-600 mb-8 text-lg">
          Need help? A member of our team will call you back to assist you.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
          <!-- Callback Request Form -->
          <div class="bg-gray-50 p-8 rounded-lg">
            <h2 class="text-2xl font-bold text-primary-800 mb-4">
              Request a Callback
            </h2>
            <p class="text-gray-600 mb-6">
              Fill out the form below and we'll get back to you as soon as
              possible.
            </p>

            <form @submit.prevent="submitForm" class="space-y-6">
              <!-- Name Field -->
              <div>
                <label
                  for="name"
                  class="block text-sm font-medium text-gray-700 mb-2"
                >
                  Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="name"
                  v-model="form.name"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors"
                  placeholder="Your full name"
                />
              </div>

              <!-- Email Field -->
              <div>
                <label
                  for="email"
                  class="block text-sm font-medium text-gray-700 mb-2"
                >
                  Email <span class="text-red-500">*</span>
                </label>
                <input
                  id="email"
                  v-model="form.email"
                  type="email"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors"
                  placeholder="your.email@example.com"
                />
              </div>

              <!-- Community Name Field -->
              <div>
                <label
                  for="community"
                  class="block text-sm font-medium text-gray-700 mb-2"
                >
                  Community Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="community"
                  v-model="form.communityName"
                  type="text"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors"
                  placeholder="Your HOA community name"
                />
              </div>

              <!-- Reason Field -->
              <div>
                <label
                  for="reason"
                  class="block text-sm font-medium text-gray-700 mb-2"
                >
                  Reason for Contact <span class="text-red-500">*</span>
                </label>
                <select
                  id="reason"
                  v-model="form.reason"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors bg-white"
                >
                  <option value="">Select a reason...</option>
                  <option value="technical-support">Technical Support</option>
                  <option value="billing-payment">
                    Billing/Payment Questions
                  </option>
                  <option value="account-setup">
                    Account Setup/Onboarding
                  </option>
                  <option value="invoice-payment">
                    Invoice/Payment Issues
                  </option>
                  <option value="vendor-management">Vendor Management</option>
                  <option value="feature-request">Feature Request</option>
                  <option value="bug-report">Bug Report</option>
                  <option value="general-inquiry">General Inquiry</option>
                  <option value="other">Other</option>
                </select>
              </div>

              <!-- Callback Number Field -->
              <div>
                <label
                  for="phone"
                  class="block text-sm font-medium text-gray-700 mb-2"
                >
                  Callback Number <span class="text-red-500">*</span>
                </label>
                <input
                  id="phone"
                  v-model="form.callbackNumber"
                  type="tel"
                  required
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors"
                  placeholder="(555) 123-4567"
                />
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="isSubmitting"
                class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!isSubmitting">Request Callback</span>
                <span v-else>Sending...</span>
              </button>

              <!-- Error Message -->
              <div
                v-if="errorMessage"
                class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg"
              >
                <p class="font-medium">Error</p>
                <p class="text-sm mt-1">{{ errorMessage }}</p>
              </div>
            </form>
          </div>

          <!-- Alternative Contact Method -->
          <div class="bg-primary-50 p-8 rounded-lg">
            <h2 class="text-2xl font-bold text-primary-800 mb-4">
              Prefer Email?
            </h2>
            <p class="text-gray-700 mb-6">
              You can also reach out to us directly via email. We typically
              respond within 24 hours.
            </p>
            <div class="space-y-4">
              <a
                href="mailto:support@neibrpay.com"
                class="inline-flex items-center text-primary-700 hover:text-primary-800 font-semibold transition-colors"
              >
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
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
                support@neibrpay.com
              </a>
              <p class="text-sm text-gray-600 mt-4">
                Include your community name and a brief description of your
                issue for faster assistance.
              </p>
            </div>
          </div>
        </div>
      </div>
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

useHead({
  title: 'Support - NeibrPay',
  meta: [
    {
      name: 'description',
      content:
        'Get support for NeibrPay. Request a callback from our team or contact us via email at support@neibrpay.com.',
    },
  ],
});

const form = ref({
  name: '',
  email: '',
  communityName: '',
  reason: '',
  callbackNumber: '',
});

const isSubmitting = ref(false);
const errorMessage = ref('');

const submitForm = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';

  try {
    const response = await $fetch('/api/support-request', {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        communityName: form.value.communityName,
        reason: form.value.reason,
        callbackNumber: form.value.callbackNumber,
      },
    });

    if (response.success) {
      // Redirect to thank you page
      await navigateTo('/support-thank-you');
    }
  } catch (error: any) {
    errorMessage.value =
      error.data?.message ||
      'Failed to submit your request. Please try again or email us directly.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>
