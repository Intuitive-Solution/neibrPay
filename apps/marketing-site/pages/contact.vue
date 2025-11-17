<template>
  <div>
    <AppHeader />
    <main class="bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <h1 class="text-4xl md:text-5xl font-bold text-primary-800 mb-4">
          Contact Us
        </h1>
        <p class="text-gray-600 mb-8 text-lg">
          Have a question or want to learn more? We'd love to hear from you.
        </p>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-12">
          <!-- Contact Form -->
          <div class="bg-gray-50 p-8 rounded-lg">
            <h2 class="text-2xl font-bold text-primary-800 mb-4">
              Send us a Message
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

              <!-- Message Field -->
              <div>
                <label
                  for="message"
                  class="block text-sm font-medium text-gray-700 mb-2"
                >
                  Message <span class="text-red-500">*</span>
                </label>
                <textarea
                  id="message"
                  v-model="form.message"
                  required
                  rows="6"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-colors resize-vertical"
                  placeholder="Tell us how we can help you..."
                ></textarea>
                <p class="text-sm text-gray-500 mt-1">
                  {{ form.message.length }} / 2000 characters
                </p>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="isSubmitting"
                class="w-full btn-primary disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!isSubmitting">Send Message</span>
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

          <!-- Contact Information -->
          <div class="bg-primary-50 p-8 rounded-lg">
            <h2 class="text-2xl font-bold text-primary-800 mb-4">
              Get in Touch
            </h2>
            <p class="text-gray-700 mb-6">
              Prefer to reach out directly? Here are other ways to contact us.
            </p>
            <div class="space-y-6">
              <!-- Email -->
              <div>
                <h3 class="text-lg font-semibold text-primary-800 mb-2">
                  Email
                </h3>
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
              </div>

              <!-- Response Time -->
              <div>
                <h3 class="text-lg font-semibold text-primary-800 mb-2">
                  Response Time
                </h3>
                <p class="text-gray-700">
                  We typically respond within 24 hours during business days.
                </p>
              </div>

              <!-- Support Page Link -->
              <div>
                <h3 class="text-lg font-semibold text-primary-800 mb-2">
                  Need Immediate Help?
                </h3>
                <p class="text-gray-700 mb-3">
                  Request a callback from our support team.
                </p>
                <NuxtLink
                  to="/support"
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
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"
                    />
                  </svg>
                  Request a Callback
                </NuxtLink>
              </div>
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
  title: 'Contact Us - NeibrPay',
  meta: [
    {
      name: 'description',
      content:
        "Contact NeibrPay. Have a question or want to learn more? We'd love to hear from you.",
    },
  ],
});

const form = ref({
  name: '',
  email: '',
  communityName: '',
  message: '',
});

const isSubmitting = ref(false);
const errorMessage = ref('');

const submitForm = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';

  // Validate message length
  if (form.value.message.length > 2000) {
    errorMessage.value = 'Message must be 2000 characters or less.';
    isSubmitting.value = false;
    return;
  }

  try {
    const response = await $fetch('/api/contact', {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        communityName: form.value.communityName,
        message: form.value.message,
      },
    });

    if (response.success) {
      // Redirect to thank you page
      await navigateTo('/contact-thank-you');
    }
  } catch (error: any) {
    errorMessage.value =
      error.data?.message ||
      'Failed to send your message. Please try again or email us directly.';
  } finally {
    isSubmitting.value = false;
  }
};
</script>
