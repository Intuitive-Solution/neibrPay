<template>
  <div>
    <AppHeader />
    <main class="bg-white">
      <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
        <div class="text-center">
          <!-- Success Icon -->
          <div
            class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6"
          >
            <svg
              class="h-12 w-12 text-green-600"
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

          <!-- Thank You Message -->
          <h1 class="text-4xl md:text-5xl font-bold text-primary-800 mb-4">
            Thank You!
          </h1>
          <p class="text-xl text-gray-600 mb-8">
            We've received your message and will get back to you soon.
          </p>

          <!-- Additional Info -->
          <div class="bg-gray-50 rounded-lg p-6 mb-8 text-left">
            <h2 class="text-lg font-semibold text-gray-900 mb-3">
              What happens next?
            </h2>
            <ul class="space-y-2 text-gray-700">
              <li class="flex items-start">
                <svg
                  class="h-5 w-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
                <span>Our team will review your message</span>
              </li>
              <li class="flex items-start">
                <svg
                  class="h-5 w-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
                <span>We'll respond via email at the address you provided</span>
              </li>
              <li class="flex items-start">
                <svg
                  class="h-5 w-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
                <span>Typically within 24 hours during business days</span>
              </li>
            </ul>
          </div>

          <!-- Alternative Contact -->
          <p class="text-gray-600 mb-6">
            Need immediate assistance? You can also reach us at
            <a
              href="mailto:support@neibrpay.com"
              class="text-primary-600 hover:text-primary-700 font-semibold"
            >
              support@neibrpay.com
            </a>
            or
            <NuxtLink
              to="/support"
              class="text-primary-600 hover:text-primary-700 font-semibold"
            >
              request a callback
            </NuxtLink>
            .
          </p>

          <!-- Back to Home Button -->
          <NuxtLink to="/" class="btn-primary inline-flex items-center">
            <svg
              class="h-5 w-5 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
              />
            </svg>
            Back to Home
          </NuxtLink>
        </div>
      </div>
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
useHead({
  title: 'Thank You - NeibrPay Contact',
  meta: [
    {
      name: 'description',
      content:
        'Thank you for contacting NeibrPay. We will get back to you soon.',
    },
  ],
});
</script>
