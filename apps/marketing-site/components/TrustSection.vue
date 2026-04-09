<template>
  <section class="bg-neutral-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2
        class="text-3xl md:text-4xl font-bold text-primary-800 text-center mb-4"
      >
        Built on Platforms You Trust
      </h2>
      <p class="text-lg text-gray-600 text-center mb-12 max-w-2xl mx-auto">
        Your financial data is protected by the same platforms trusted by
        millions of businesses worldwide.
      </p>

      <!-- Integration Partners -->
      <div
        class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-16 max-w-4xl mx-auto"
      >
        <div
          class="bg-white rounded-xl p-6 text-center shadow-sm border border-gray-100"
        >
          <div
            class="w-14 h-14 mx-auto rounded-xl bg-indigo-50 flex items-center justify-center mb-4"
          >
            <svg
              class="w-7 h-7 text-indigo-600"
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
          <h3 class="text-lg font-semibold text-gray-900 mb-1">Stripe</h3>
          <p class="text-sm text-gray-500">
            Card & ACH payments powered by Stripe
          </p>
        </div>

        <div
          class="bg-white rounded-xl p-6 text-center shadow-sm border border-gray-100"
        >
          <div
            class="w-14 h-14 mx-auto rounded-xl bg-purple-50 flex items-center justify-center mb-4"
          >
            <svg
              class="w-7 h-7 text-purple-600"
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
          <h3 class="text-lg font-semibold text-gray-900 mb-1">Zelle</h3>
          <p class="text-sm text-gray-500">Direct bank transfers via Zelle</p>
        </div>

        <div
          class="bg-white rounded-xl p-6 text-center shadow-sm border border-gray-100"
        >
          <div
            class="w-14 h-14 mx-auto rounded-xl bg-green-50 flex items-center justify-center mb-4"
          >
            <svg
              class="w-7 h-7 text-green-600"
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
          <h3 class="text-lg font-semibold text-gray-900 mb-1">Plaid</h3>
          <p class="text-sm text-gray-500">
            Secure bank verification through Plaid
          </p>
        </div>
      </div>

      <!-- Security Badges -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6 max-w-3xl mx-auto">
        <div v-for="badge in badges" :key="badge.label" class="text-center">
          <div
            class="w-10 h-10 mx-auto rounded-lg bg-primary-50 flex items-center justify-center mb-3"
          >
            <component :is="badge.icon" />
          </div>
          <p class="text-sm font-medium text-gray-700">{{ badge.label }}</p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { h } from 'vue';

const ShieldIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-5 h-5 text-primary-600',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
      }),
    ]
  );

const LockIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-5 h-5 text-primary-600',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
      }),
    ]
  );

const CheckCircleIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-5 h-5 text-primary-600',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
      }),
    ]
  );

const DatabaseIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-5 h-5 text-primary-600',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4',
      }),
    ]
  );

const badges = [
  { label: 'PCI-compliant payments', icon: ShieldIcon },
  { label: 'Bank-grade encryption', icon: LockIcon },
  { label: 'SOC 2 ready infrastructure', icon: CheckCircleIcon },
  { label: 'Isolated data per community', icon: DatabaseIcon },
];
</script>
