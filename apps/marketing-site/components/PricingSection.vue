<template>
  <section id="pricing" class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Headline -->
      <h2 class="section-headline text-center mb-4">
        Simple, Transparent Pricing
      </h2>
      <p class="section-subheadline text-center mb-12">
        Choose the plan that fits your community size
      </p>

      <!-- Monthly/Annual Toggle -->
      <div class="flex justify-center mb-12">
        <div class="inline-flex items-center bg-gray-100 rounded-lg p-1">
          <button
            @click="
              isAnnual = false;
              trackPricingToggle(false);
            "
            :class="[
              'px-6 py-2 rounded-md font-medium transition-all',
              !isAnnual
                ? 'bg-white text-primary-800 shadow-sm'
                : 'text-gray-600 hover:text-gray-900',
            ]"
          >
            Monthly
          </button>
          <button
            @click="
              isAnnual = true;
              trackPricingToggle(true);
            "
            :class="[
              'px-6 py-2 rounded-md font-medium transition-all relative',
              isAnnual
                ? 'bg-white text-primary-800 shadow-sm'
                : 'text-gray-600 hover:text-gray-900',
            ]"
          >
            Annual
            <span
              class="absolute -top-2 -right-2 bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full"
            >
              Save $40
            </span>
          </button>
        </div>
      </div>

      <!-- Pricing Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
        <!-- Starter Plan -->
        <div
          class="bg-white border-2 border-gray-200 rounded-xl p-8 hover:border-primary-300 transition-colors"
        >
          <h3 class="text-2xl font-bold text-primary-800 mb-2">Starter</h3>
          <p class="text-gray-600 mb-6">Perfect for small communities</p>

          <div class="mb-6">
            <div class="flex items-baseline">
              <span class="text-5xl font-bold text-primary-800"
                >${{ monthlyPrice }}</span
              >
              <span class="text-gray-600 ml-2">/month</span>
            </div>
            <p v-if="isAnnual" class="text-sm text-gray-500 mt-2">
              Billed annually: $200/year
            </p>
            <p v-else class="text-sm text-gray-500 mt-2">Billed monthly</p>
          </div>

          <ul class="space-y-3 mb-8">
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">Up to 25 units</span>
            </li>
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">All core features</span>
            </li>
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">Email support</span>
            </li>
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">Owner portal access</span>
            </li>
          </ul>

          <a
            :href="adminWebUrl + '/auth'"
            class="btn-primary w-full text-center block"
            @click="trackPricingCTA('starter', 'pricing_card')"
          >
            Get Started
          </a>
        </div>

        <!-- Enterprise Plan -->
        <div
          class="bg-primary-50 border-2 border-primary-300 rounded-xl p-8 relative"
        >
          <div class="absolute top-4 right-4">
            <span
              class="bg-primary-600 text-white text-xs font-semibold px-3 py-1 rounded-full"
            >
              Popular
            </span>
          </div>

          <h3 class="text-2xl font-bold text-primary-800 mb-2">Enterprise</h3>
          <p class="text-gray-600 mb-6">For larger communities</p>

          <div class="mb-6">
            <div class="flex items-baseline">
              <span class="text-5xl font-bold text-primary-800">Custom</span>
            </div>
            <p class="text-sm text-gray-500 mt-2">Beyond 25 units</p>
          </div>

          <ul class="space-y-3 mb-8">
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">Unlimited units</span>
            </li>
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">All core features</span>
            </li>
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">Priority support</span>
            </li>
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">Custom integrations</span>
            </li>
            <li class="flex items-start">
              <svg
                class="w-5 h-5 text-primary-600 mr-2 mt-0.5 flex-shrink-0"
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
              <span class="text-gray-700">Dedicated account manager</span>
            </li>
          </ul>

          <a
            :href="calendlyUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-primary w-full text-center block"
            @click="trackPricingCTA('enterprise', 'pricing_card')"
          >
            Talk to Us
          </a>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';

const config = useRuntimeConfig();
const adminWebUrl = config.public.adminWebUrl;
const calendlyUrl = config.public.calendlyUrl;
const { $posthog } = useNuxtApp();

const isAnnual = ref(false);

const monthlyPrice = computed(() => {
  return isAnnual.value ? '16.67' : '19.99';
});

// Track pricing interactions
const trackPricingToggle = (isAnnualValue: boolean) => {
  if (process.client && $posthog) {
    $posthog.capture('pricing_toggle', {
      billing_period: isAnnualValue ? 'annual' : 'monthly',
    });
  }
};

const trackPricingCTA = (plan: string, location: string) => {
  if (process.client && $posthog) {
    $posthog.capture('pricing_cta_clicked', {
      plan,
      location,
    });
  }
};
</script>
