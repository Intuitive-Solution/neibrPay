<template>
  <div>
    <AppHeader />
    <main class="bg-white">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <div
          class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-start"
        >
          <!-- Left: Value Prop -->
          <div class="lg:sticky lg:top-24">
            <h1 class="text-3xl md:text-4xl font-bold text-primary-800 mb-4">
              Get Started with NeibrPay
            </h1>
            <p class="text-lg text-gray-600 mb-8">
              Tell us about your community and our team will reach out within
              <strong class="text-gray-800">24 hours</strong> to get you set up.
            </p>

            <ul class="space-y-4 mb-8">
              <li class="flex items-start gap-3">
                <div
                  class="mt-1 w-5 h-5 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0"
                >
                  <svg
                    class="w-3 h-3 text-primary-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <span class="text-gray-700"
                  >Collect dues online via Zelle, ACH, or card</span
                >
              </li>
              <li class="flex items-start gap-3">
                <div
                  class="mt-1 w-5 h-5 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0"
                >
                  <svg
                    class="w-3 h-3 text-primary-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <span class="text-gray-700"
                  >Manage vendors, expenses, and budgets</span
                >
              </li>
              <li class="flex items-start gap-3">
                <div
                  class="mt-1 w-5 h-5 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0"
                >
                  <svg
                    class="w-3 h-3 text-primary-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <span class="text-gray-700"
                  >Self-service owner portal for residents</span
                >
              </li>
              <li class="flex items-start gap-3">
                <div
                  class="mt-1 w-5 h-5 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0"
                >
                  <svg
                    class="w-3 h-3 text-primary-600"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="3"
                      d="M5 13l4 4L19 7"
                    />
                  </svg>
                </div>
                <span class="text-gray-700"
                  >Set up in under 10 minutes — no credit card required</span
                >
              </li>
            </ul>

            <div class="flex items-center gap-6 text-sm text-gray-500">
              <div class="flex items-center gap-2">
                <svg
                  class="w-4 h-4 text-primary-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"
                  />
                </svg>
                <span>Bank-grade security</span>
              </div>
              <div class="flex items-center gap-2">
                <svg
                  class="w-4 h-4 text-primary-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  />
                </svg>
                <span>PCI compliant</span>
              </div>
            </div>
          </div>

          <!-- Right: Form -->
          <div
            class="bg-neutral-50 rounded-xl p-6 md:p-8 shadow-sm border border-gray-100"
          >
            <div v-if="!isSubmitted">
              <h2 class="text-xl font-semibold text-gray-900 mb-6">
                Tell us about your community
              </h2>

              <form @submit.prevent="submitForm" class="space-y-5">
                <div>
                  <label for="name" class="form-label">
                    Full Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="form-input"
                    placeholder="Your full name"
                  />
                </div>

                <div>
                  <label for="phone" class="form-label">
                    Phone Number <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    required
                    class="form-input"
                    placeholder="(555) 123-4567"
                  />
                </div>

                <div>
                  <label for="email" class="form-label">
                    Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="form-input"
                    placeholder="you@example.com"
                  />
                </div>

                <div>
                  <label for="community" class="form-label">
                    Community Name <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="community"
                    v-model="form.communityName"
                    type="text"
                    required
                    class="form-input"
                    placeholder="Your HOA or community name"
                  />
                </div>

                <div>
                  <label for="units" class="form-label">
                    Number of Units <span class="text-red-500">*</span>
                  </label>
                  <select
                    id="units"
                    v-model="form.numberOfUnits"
                    required
                    class="form-input"
                  >
                    <option value="" disabled>Select unit count</option>
                    <option value="1-10">1 – 10 units</option>
                    <option value="11-25">11 – 25 units</option>
                    <option value="26-50">26 – 50 units</option>
                    <option value="51-100">51 – 100 units</option>
                    <option value="101-250">101 – 250 units</option>
                    <option value="250+">250+ units</option>
                  </select>
                </div>

                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="btn-primary w-full justify-center disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <span v-if="!isSubmitting">Get Started</span>
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
                    Submitting...
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

            <!-- Success State -->
            <div v-else class="text-center py-8">
              <div
                class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-primary-100 mb-6"
              >
                <svg
                  class="h-8 w-8 text-primary-600"
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
              <h2 class="text-2xl font-bold text-primary-800 mb-3">
                Thank you!
              </h2>
              <p class="text-gray-600 mb-6">
                One of our agents will get in touch with you within
                <strong class="text-gray-800">24 hours</strong>.
              </p>
              <p class="text-sm text-gray-500 mb-8">
                We sent a confirmation to <strong>{{ form.email }}</strong
                >. Check your inbox for next steps.
              </p>
              <NuxtLink to="/" class="btn-primary"> Back to Home </NuxtLink>
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

const canonicalHref = useCanonicalHref();

useHead({
  title: 'Get Started - NeibrPay',
  meta: [
    {
      name: 'description',
      content:
        'Get started with NeibrPay HOA management. Tell us about your community and our team will contact you within 24 hours.',
    },
  ],
  link: [{ rel: 'canonical', href: canonicalHref }],
});

const form = ref({
  name: '',
  phone: '',
  email: '',
  communityName: '',
  numberOfUnits: '',
});

const isSubmitting = ref(false);
const isSubmitted = ref(false);
const errorMessage = ref('');

const submitForm = async () => {
  isSubmitting.value = true;
  errorMessage.value = '';

  try {
    const response = await $fetch('/api/get-started', {
      method: 'POST',
      body: {
        name: form.value.name,
        phone: form.value.phone,
        email: form.value.email,
        communityName: form.value.communityName,
        numberOfUnits: form.value.numberOfUnits,
      },
    });

    if (response.success) {
      isSubmitted.value = true;

      trackGtagEvent('get_started_form_submitted', {
        units: form.value.numberOfUnits,
        community: form.value.communityName,
      });
    }
  } catch (error: any) {
    errorMessage.value =
      error.data?.message ||
      'Something went wrong. Please try again or email us at support@neibrpay.com';
  } finally {
    isSubmitting.value = false;
  }
};
</script>
