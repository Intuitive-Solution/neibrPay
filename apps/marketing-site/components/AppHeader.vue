<template>
  <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center">
          <NeibrPayLogo size="md" />
        </NuxtLink>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center gap-8">
          <NuxtLink
            to="#features"
            class="text-gray-700 hover:text-primary-600 font-medium transition-colors"
          >
            Features
          </NuxtLink>
          <NuxtLink
            to="#pricing"
            class="text-gray-700 hover:text-primary-600 font-medium transition-colors"
          >
            Pricing
          </NuxtLink>
          <a
            :href="calendlyUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="text-gray-700 hover:text-primary-600 font-medium transition-colors"
            @click="trackHeaderCTA('book_demo')"
          >
            Book a Demo
          </a>
          <a
            :href="adminWebUrl + '/auth'"
            class="btn-primary"
            @click="trackHeaderCTA('get_started')"
          >
            Get Started
          </a>
        </div>

        <!-- Mobile Menu Button -->
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="md:hidden p-2 rounded-md text-gray-700 hover:bg-gray-100"
          aria-label="Toggle menu"
        >
          <svg
            class="h-6 w-6"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              v-if="!mobileMenuOpen"
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 6h16M4 12h16M4 18h16"
            />
            <path
              v-else
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Mobile Menu -->
      <div v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-2">
        <NuxtLink
          to="#features"
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md font-medium"
        >
          Features
        </NuxtLink>
        <NuxtLink
          to="#pricing"
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md font-medium"
        >
          Pricing
        </NuxtLink>
        <a
          :href="calendlyUrl"
          target="_blank"
          rel="noopener noreferrer"
          @click="
            mobileMenuOpen = false;
            trackHeaderCTA('book_demo');
          "
          class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md font-medium"
        >
          Book a Demo
        </a>
        <a
          :href="adminWebUrl + '/auth'"
          class="block btn-primary text-center mt-2"
          @click="trackHeaderCTA('get_started')"
        >
          Get Started
        </a>
      </div>
    </nav>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const config = useRuntimeConfig();
const adminWebUrl = config.public.adminWebUrl;
const calendlyUrl = config.public.calendlyUrl;
const { $posthog } = useNuxtApp();

const mobileMenuOpen = ref(false);

// Track header CTA clicks
const trackHeaderCTA = (type: string) => {
  if (process.client && $posthog) {
    $posthog.capture('header_cta_clicked', {
      cta_type: type,
    });
  }
};
</script>
