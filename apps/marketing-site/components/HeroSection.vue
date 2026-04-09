<template>
  <section class="bg-white py-16 md:py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-4xl mx-auto">
        <h1
          class="text-4xl md:text-5xl lg:text-6xl font-bold text-primary-800 mb-6 leading-tight"
        >
          The <em class="not-italic text-primary">easiest</em> way to manage
          your HOA
        </h1>

        <p
          class="text-xl md:text-2xl text-text-secondary mb-10 max-w-3xl mx-auto"
        >
          Collect dues, manage vendors, and communicate with residents &mdash;
          all in one place.
        </p>

        <div class="flex flex-col items-center gap-4 mb-6">
          <NuxtLink
            to="/get-started"
            class="btn-primary btn-lg"
            @click="trackCTA('get_started', 'hero')"
          >
            Get Started
          </NuxtLink>
          <NuxtLink
            to="/get-started"
            class="btn-text text-sm"
            @click="trackCTA('free_trial', 'hero')"
          >
            or start a 30-day free trial
          </NuxtLink>
        </div>

        <div class="mb-6">
          <p class="text-sm text-gray-500">
            Looking for HOA software for under 50 units?
            <a
              :href="calendlyUrl"
              target="_blank"
              rel="noopener noreferrer"
              class="text-primary-700 hover:text-primary-800 font-semibold underline-offset-4 hover:underline"
              @click="trackCTA('tailored_demo', 'hero')"
            >
              Get a tailored demo
            </a>
          </p>
        </div>

        <p class="text-sm text-gray-400 mb-12">
          Perfect for self-managed HOAs and small community associations.
        </p>

        <div class="border-t border-gray-200 pt-8">
          <p
            class="text-xs text-gray-400 uppercase tracking-wider font-medium mb-6"
          >
            Trusted integrations
          </p>
          <div
            class="flex flex-col sm:flex-row items-center justify-center gap-8 sm:gap-12"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center"
              >
                <svg
                  class="w-5 h-5 text-indigo-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                  />
                </svg>
              </div>
              <div class="text-left">
                <p class="text-sm font-semibold text-gray-800">Stripe</p>
                <p class="text-xs text-gray-500">Card & ACH payments</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg bg-purple-50 flex items-center justify-center"
              >
                <svg
                  class="w-5 h-5 text-purple-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"
                  />
                </svg>
              </div>
              <div class="text-left">
                <p class="text-sm font-semibold text-gray-800">Zelle</p>
                <p class="text-xs text-gray-500">Bank transfers</p>
              </div>
            </div>

            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center"
              >
                <svg
                  class="w-5 h-5 text-green-600"
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
              </div>
              <div class="text-left">
                <p class="text-sm font-semibold text-gray-800">Plaid</p>
                <p class="text-xs text-gray-500">Secure bank verification</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const config = useRuntimeConfig();
const calendlyUrl = config.public.calendlyUrl;
const { $posthog } = useNuxtApp();

const trackCTA = (type: string, location: string) => {
  if (process.client && $posthog) {
    $posthog.capture('marketing_cta_clicked', {
      cta_type: type,
      cta_location: location,
    });
  }
};
</script>
