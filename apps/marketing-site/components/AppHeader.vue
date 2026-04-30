<template>
  <header class="sticky top-0 z-50 bg-white border-b border-gray-200">
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo -->
        <NuxtLink to="/" class="flex items-center">
          <NeibrPayLogo size="md" />
        </NuxtLink>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center gap-6">
          <!-- Features Dropdown -->
          <div
            class="relative"
            @mouseenter="featuresOpen = true"
            @mouseleave="featuresOpen = false"
          >
            <button
              class="flex items-center gap-1 text-gray-700 hover:text-primary-600 font-medium transition-colors py-2"
              @click="featuresOpen = !featuresOpen"
            >
              Features
              <svg
                class="w-4 h-4 transition-transform"
                :class="{ 'rotate-180': featuresOpen }"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"
                />
              </svg>
            </button>

            <Transition
              enter-active-class="transition ease-out duration-100"
              enter-from-class="transform opacity-0 scale-95"
              enter-to-class="transform opacity-100 scale-100"
              leave-active-class="transition ease-in duration-75"
              leave-from-class="transform opacity-100 scale-100"
              leave-to-class="transform opacity-0 scale-95"
            >
              <div
                v-if="featuresOpen"
                class="absolute left-0 mt-0 w-72 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50"
              >
                <NuxtLink
                  v-for="feature in features"
                  :key="feature.slug"
                  :to="`/features/${feature.slug}`"
                  class="block px-4 py-2.5 text-sm text-gray-700 hover:bg-primary-50 hover:text-primary-700 transition-colors"
                  @click="featuresOpen = false"
                >
                  <span class="font-medium">{{ feature.title }}</span>
                </NuxtLink>
              </div>
            </Transition>
          </div>

          <NuxtLink
            to="/#pricing"
            class="text-gray-700 hover:text-primary-600 font-medium transition-colors"
          >
            Pricing
          </NuxtLink>

          <a
            :href="appAuthUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="text-gray-700 hover:text-primary-600 font-medium transition-colors"
            @click="trackHeaderCTA('login')"
          >
            Login
          </a>

          <a
            :href="appAuthUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="btn-outline btn-sm"
            @click="trackHeaderCTA('make_payment')"
          >
            Make Payment
          </a>

          <NuxtLink
            to="/get-started"
            class="btn-primary"
            @click="trackHeaderCTA('get_started')"
          >
            Get Started
          </NuxtLink>
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
      <div v-if="mobileMenuOpen" class="md:hidden pb-4 space-y-1">
        <p
          class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wider"
        >
          Features
        </p>
        <NuxtLink
          v-for="feature in features"
          :key="feature.slug"
          :to="`/features/${feature.slug}`"
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md text-sm"
        >
          {{ feature.title }}
        </NuxtLink>

        <div class="border-t border-gray-200 my-2" />

        <NuxtLink
          to="/#pricing"
          @click="mobileMenuOpen = false"
          class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md font-medium"
        >
          Pricing
        </NuxtLink>
        <a
          :href="appAuthUrl"
          target="_blank"
          rel="noopener noreferrer"
          @click="
            mobileMenuOpen = false;
            trackHeaderCTA('login');
          "
          class="block px-3 py-2 text-gray-700 hover:bg-gray-50 rounded-md font-medium"
        >
          Login
        </a>
        <a
          :href="appAuthUrl"
          target="_blank"
          rel="noopener noreferrer"
          @click="
            mobileMenuOpen = false;
            trackHeaderCTA('make_payment');
          "
          class="block btn-outline btn-sm text-center mt-2"
        >
          Make Payment
        </a>
        <NuxtLink
          to="/get-started"
          class="block btn-primary text-center mt-2"
          @click="
            mobileMenuOpen = false;
            trackHeaderCTA('get_started');
          "
        >
          Get Started
        </NuxtLink>
      </div>
    </nav>
  </header>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useFeatureData } from '~/composables/useFeatureData';

const appAuthUrl = 'https://app.neibrpay.com/auth';
const { features } = useFeatureData();

const mobileMenuOpen = ref(false);
const featuresOpen = ref(false);

const trackHeaderCTA = (type: string) => {
  trackGtagEvent('header_cta_clicked', { cta_type: type });
};
</script>
