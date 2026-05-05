<template>
  <section id="features" class="bg-neutral-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="section-headline text-center mb-4">
        Everything You Need to Manage Your HOA
      </h2>
      <p class="section-subheadline text-center mb-16">
        Comprehensive tools designed for modern community management
      </p>

      <div
        v-for="(group, groupIndex) in categoryGroups"
        :key="group.id"
        :class="[groupIndex < categoryGroups.length - 1 ? 'mb-20' : '']"
      >
        <div class="text-center mb-8">
          <h3 class="text-2xl md:text-3xl font-bold text-primary-800 mb-2">
            {{ group.label }}
          </h3>
          <p class="text-gray-600 max-w-2xl mx-auto">
            {{ group.subtitle }}
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <NuxtLink
            v-for="feature in group.features"
            :key="feature.slug"
            :to="`/features/${feature.slug}`"
            class="group bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200 block"
          >
            <div
              class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
            >
              <component :is="featureIcons[feature.slug]" />
            </div>
            <h3
              class="text-xl font-semibold text-primary-800 mb-2 group-hover:text-primary-600 transition-colors"
            >
              {{ feature.title }}
            </h3>
            <p class="text-gray-600 mb-4">
              {{ feature.shortDescription }}
            </p>
            <span
              class="inline-flex items-center text-sm font-medium text-primary-700 group-hover:text-primary-500 transition-colors"
            >
              Learn more
              <svg
                class="w-4 h-4 ml-1 group-hover:translate-x-1 transition-transform"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </span>
          </NuxtLink>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed, h } from 'vue';
import { useFeatureData } from '~/composables/useFeatureData';

const { getFeaturesByCategory } = useFeatureData();

const categoryGroups = computed(() => [
  {
    id: 'financial',
    label: 'Financial',
    subtitle:
      'Invoice residents, track every expense, build budgets, and keep the books audit-ready.',
    features: getFeaturesByCategory('financial'),
  },
  {
    id: 'management',
    label: 'Management',
    subtitle:
      'Run the community day-to-day violations, ARC requests, voting, meetings, events, and the owner portal.',
    features: getFeaturesByCategory('management'),
  },
  {
    id: 'communications',
    label: 'Communications',
    subtitle:
      'Reach every resident by email and push notification, and keep a record of what was said.',
    features: getFeaturesByCategory('communications'),
  },
]);

const svg = (d: string) =>
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
        d,
      }),
    ]
  );

const InvoiceIcon = () =>
  svg(
    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'
  );

const VendorIcon = () =>
  svg(
    'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'
  );

const DocumentIcon = () =>
  svg(
    'M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z'
  );

const PortalIcon = () =>
  svg(
    'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'
  );

const SecurityIcon = () =>
  svg(
    'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z'
  );

const ReportIcon = () =>
  svg(
    'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'
  );

const AnnouncementIcon = () =>
  svg(
    'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'
  );

const ViolationIcon = () =>
  svg(
    'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'
  );

const RequestFormIcon = () =>
  svg(
    'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2zM12 3v4a1 1 0 001 1h4'
  );

const BankIcon = () =>
  svg('M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11m16-11v11M8 14v3m4-3v3m4-3v3');

const ReserveFundIcon = () =>
  svg(
    'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2zm12 6a2 2 0 11-4 0 2 2 0 014 0z'
  );

const VoteIcon = () =>
  svg(
    'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'
  );

const MeetingIcon = () =>
  svg(
    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'
  );

const CalendarIcon = () =>
  svg(
    'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'
  );

const featureIcons: Record<string, any> = {
  'invoice-payment-management': InvoiceIcon,
  'vendor-expense-tracking': VendorIcon,
  'budgets-and-reports': ReportIcon,
  'bank-reconciliation': BankIcon,
  'reserve-fund-management': ReserveFundIcon,
  violations: ViolationIcon,
  'architectural-requests': RequestFormIcon,
  'online-voting-polls': VoteIcon,
  'meeting-management': MeetingIcon,
  'events-calendar': CalendarIcon,
  'document-storage': DocumentIcon,
  'owner-portal': PortalIcon,
  'multi-tenant-architecture': SecurityIcon,
  'announcements-communication': AnnouncementIcon,
};
</script>
