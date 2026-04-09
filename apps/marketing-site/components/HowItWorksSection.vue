<template>
  <section id="how-it-works" class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="section-headline text-center mb-4">
        Get Up and Running in Minutes
      </h2>
      <p class="section-subheadline text-center mb-16">
        Three simple steps to modernize your HOA management
      </p>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12">
        <div v-for="(step, index) in steps" :key="index" class="text-center">
          <div class="relative mb-6">
            <div
              class="mx-auto w-16 h-16 rounded-full bg-primary-100 flex items-center justify-center"
            >
              <span class="text-2xl font-bold text-primary-700">{{
                index + 1
              }}</span>
            </div>
            <!-- Connector line (hidden on mobile and for last item) -->
            <div
              v-if="index < steps.length - 1"
              class="hidden md:block absolute top-8 left-[calc(50%+2.5rem)] w-[calc(100%-5rem)] h-0.5 bg-primary-100"
              aria-hidden="true"
            />
          </div>

          <div
            class="w-12 h-12 mx-auto rounded-lg bg-primary-50 flex items-center justify-center mb-4"
          >
            <component :is="step.icon" />
          </div>

          <h3 class="text-xl font-semibold text-primary-800 mb-3">
            {{ step.title }}
          </h3>
          <p class="text-gray-600 max-w-xs mx-auto leading-relaxed">
            {{ step.description }}
          </p>
        </div>
      </div>

      <div class="text-center mt-12">
        <NuxtLink to="/get-started" class="btn-primary btn-lg">
          Get Started — It's Free
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { h } from 'vue';

const UserPlusIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6 text-primary-600',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
      }),
    ]
  );

const CogIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6 text-primary-600',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
      }),
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
      }),
    ]
  );

const CurrencyIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6 text-primary-600',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
      }),
    ]
  );

const steps = [
  {
    title: 'Sign up',
    description:
      'Create your account in under 2 minutes. No credit card required.',
    icon: UserPlusIcon,
  },
  {
    title: 'Set up your community',
    description:
      'Add units, invite residents, and configure your dues schedule.',
    icon: CogIcon,
  },
  {
    title: 'Collect dues online',
    description:
      'Residents pay via Zelle, ACH, or card. Funds go straight to your HOA account.',
    icon: CurrencyIcon,
  },
];
</script>
