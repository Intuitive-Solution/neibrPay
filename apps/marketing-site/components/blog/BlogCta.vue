<template>
  <aside
    class="not-prose my-10 rounded-2xl border border-primary-100 bg-primary-50 p-6 md:p-8"
  >
    <div
      class="flex flex-col md:flex-row md:items-center md:justify-between gap-5"
    >
      <div>
        <p
          class="text-xs font-semibold uppercase tracking-wider text-primary-700 mb-1"
        >
          {{ eyebrow }}
        </p>
        <h3 class="text-xl md:text-2xl font-bold text-primary-900 mb-1">
          {{ heading }}
        </h3>
        <p class="text-gray-700 text-sm md:text-base max-w-xl">
          {{ description }}
        </p>
      </div>
      <div class="flex flex-wrap gap-3 md:flex-shrink-0">
        <NuxtLink :to="primaryHref" class="btn-primary">
          {{ primaryLabel }}
        </NuxtLink>
        <NuxtLink v-if="secondaryHref" :to="secondaryHref" class="btn-outline">
          {{ secondaryLabel }}
        </NuxtLink>
      </div>
    </div>
  </aside>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    eyebrow?: string;
    heading?: string;
    description?: string;
    primaryLabel?: string;
    primaryHref?: string;
    secondaryLabel?: string;
    secondaryHref?: string;
  }>(),
  {
    eyebrow: 'Try NeibrPay',
    heading: 'Run your self managed HOA on autopilot',
    description:
      'Collect dues online, automate reminders, and ditch spreadsheets. Built for boards under 150 units.',
    primaryLabel: 'Get Started Free',
    primaryHref: '/get-started',
    secondaryLabel: 'See Features',
    secondaryHref: '/#features',
  }
);
</script>
