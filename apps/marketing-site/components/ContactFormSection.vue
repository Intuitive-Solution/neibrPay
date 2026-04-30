<template>
  <section id="contact" class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16">
        <!-- Left: Copy -->
        <div class="flex flex-col justify-center">
          <h2 class="text-3xl md:text-4xl font-bold text-primary-800 mb-4">
            Ready to simplify your HOA management?
          </h2>
          <p class="text-lg text-gray-600 mb-8">
            Have a question or need help choosing the right plan? Send us a
            message and we'll get back to you within one business day.
          </p>

          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0"
              >
                <svg
                  class="w-5 h-5 text-primary-600"
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
              </div>
              <div>
                <p class="text-sm text-gray-500">Email us directly</p>
                <a
                  href="mailto:support@neibrpay.com"
                  class="text-primary-700 hover:text-primary-800 font-semibold"
                >
                  support@neibrpay.com
                </a>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg bg-primary-50 flex items-center justify-center flex-shrink-0"
              >
                <svg
                  class="w-5 h-5 text-primary-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div>
                <p class="text-sm text-gray-500">Response time</p>
                <p class="text-gray-800 font-semibold">
                  Within 24 hours on business days
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Form -->
        <div class="bg-neutral-50 rounded-xl p-6 md:p-8 border border-gray-100">
          <div v-if="!isSubmitted">
            <form @submit.prevent="submitForm" class="space-y-5">
              <div>
                <label for="contact-name" class="form-label">
                  Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="contact-name"
                  v-model="form.name"
                  type="text"
                  required
                  class="form-input"
                  placeholder="Your full name"
                />
              </div>

              <div>
                <label for="contact-email" class="form-label">
                  Email <span class="text-red-500">*</span>
                </label>
                <input
                  id="contact-email"
                  v-model="form.email"
                  type="email"
                  required
                  class="form-input"
                  placeholder="you@example.com"
                />
              </div>

              <div>
                <label for="contact-community" class="form-label">
                  Community Name <span class="text-red-500">*</span>
                </label>
                <input
                  id="contact-community"
                  v-model="form.communityName"
                  type="text"
                  required
                  class="form-input"
                  placeholder="Your HOA or community name"
                />
              </div>

              <div>
                <label for="contact-message" class="form-label">
                  Message <span class="text-red-500">*</span>
                </label>
                <textarea
                  id="contact-message"
                  v-model="form.message"
                  required
                  rows="4"
                  class="form-input resize-vertical"
                  :placeholder="messagePlaceholder"
                />
              </div>

              <button
                type="submit"
                :disabled="isSubmitting"
                class="btn-primary w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!isSubmitting">Send Message</span>
                <span v-else class="flex items-center gap-2">
                  <svg
                    class="animate-spin h-4 w-4"
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
                    />
                    <path
                      class="opacity-75"
                      fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                  </svg>
                  Sending...
                </span>
              </button>

              <div
                v-if="errorMessage"
                class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg"
              >
                <p class="text-sm">{{ errorMessage }}</p>
              </div>
            </form>
          </div>

          <!-- Success state -->
          <div v-else class="text-center py-8">
            <div
              class="mx-auto flex items-center justify-center h-14 w-14 rounded-full bg-primary-100 mb-4"
            >
              <svg
                class="h-7 w-7 text-primary-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M5 13l4 4L19 7"
                />
              </svg>
            </div>
            <h3 class="text-xl font-semibold text-primary-800 mb-2">
              Message sent!
            </h3>
            <p class="text-gray-600">We'll get back to you within 24 hours.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
  featureContext?: string;
}>();

const form = ref({
  name: '',
  email: '',
  communityName: '',
  message: '',
});

const isSubmitting = ref(false);
const isSubmitted = ref(false);
const errorMessage = ref('');

const messagePlaceholder = computed(() => {
  if (props.featureContext) {
    return `I'm interested in ${props.featureContext}. Tell me more...`;
  }
  return 'How can we help you?';
});

const submitForm = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';

  if (form.value.message.length > 2000) {
    errorMessage.value = 'Message must be 2000 characters or less.';
    isSubmitting.value = false;
    return;
  }

  const messageBody = props.featureContext
    ? `[Feature Interest: ${props.featureContext}]\n\n${form.value.message}`
    : form.value.message;

  try {
    const response = await $fetch('/api/contact', {
      method: 'POST',
      body: {
        name: form.value.name,
        email: form.value.email,
        communityName: form.value.communityName,
        message: messageBody,
      },
    });

    if (response.success) {
      isSubmitted.value = true;

      trackGtagEvent('homepage_contact_form_submitted', {
        feature_context: props.featureContext || 'none',
      });
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
