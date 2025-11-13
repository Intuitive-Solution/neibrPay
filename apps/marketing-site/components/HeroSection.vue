<template>
  <section class="bg-white py-16 md:py-24 lg:py-32">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center">
        <!-- Main Headline - Wise Style -->
        <h1
          class="text-4xl md:text-5xl lg:text-6xl font-bold text-primary-800 mb-6 leading-tight"
        >
          HOA MANAGEMENT MADE SIMPLE
        </h1>

        <!-- Sub-headline -->
        <p
          class="text-xl md:text-2xl text-text-secondary mb-10 max-w-3xl mx-auto"
        >
          Streamline invoicing, payments, vendor management, and community
          communication all in one platform. Built for modern HOAs.
        </p>

        <!-- CTAs -->
        <div
          class="flex flex-col sm:flex-row gap-4 justify-center items-center"
        >
          <a
            :href="adminWebUrl + '/auth'"
            class="btn-primary btn-lg"
            @click="trackGetStarted"
          >
            Get Started
          </a>
          <a
            :href="calendlyUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-outline btn-lg"
            @click="trackBookDemo"
          >
            Book a Demo
          </a>
        </div>

        <!-- Optional: Trust indicators or stats -->
        <div class="mt-16 pt-8 border-t border-gray-200">
          <p class="text-sm text-gray-500 mb-4">
            Trusted by HOAs across the country
          </p>
          <!-- Placeholder for trust badges or stats -->
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const config = useRuntimeConfig();
const adminWebUrl = config.public.adminWebUrl;
const calendlyUrl = config.public.calendlyUrl;
const { $posthog } = useNuxtApp();

// Track CTA clicks
const trackGetStarted = () => {
  if (process.client && $posthog) {
    $posthog.capture('marketing_cta_clicked', {
      cta_type: 'get_started',
      cta_location: 'hero',
    });
  }
};

const trackBookDemo = () => {
  if (process.client && $posthog) {
    $posthog.capture('marketing_cta_clicked', {
      cta_type: 'book_demo',
      cta_location: 'hero',
    });
  }
};
</script>
