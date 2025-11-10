<template>
  <section id="features" class="bg-neutral-50 py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Section Headline -->
      <h2 class="section-headline text-center mb-4">
        Everything You Need to Manage Your HOA
      </h2>
      <p class="section-subheadline text-center mb-12">
        Comprehensive tools designed for modern community management
      </p>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div
          v-for="feature in features"
          :key="feature.title"
          class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-shadow"
        >
          <div
            class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center mb-4"
          >
            <component :is="feature.icon" />
          </div>
          <h3 class="text-xl font-semibold text-primary-800 mb-2">
            {{ feature.title }}
          </h3>
          <p class="text-gray-600">
            {{ feature.description }}
          </p>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { h } from 'vue';

// Icon components as render functions
const InvoiceIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
      }),
    ]
  );

const VendorIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
      }),
    ]
  );

const DocumentIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
      }),
    ]
  );

const PortalIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z',
      }),
    ]
  );

const SecurityIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6',
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

const ReportIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
      }),
    ]
  );

const AnnouncementIcon = () =>
  h(
    'svg',
    {
      fill: 'none',
      stroke: 'currentColor',
      viewBox: '0 0 24 24',
      class: 'w-6 h-6',
    },
    [
      h('path', {
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'stroke-width': '2',
        d: 'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z',
      }),
    ]
  );

const features = [
  {
    title: 'Invoice & Payment Management',
    description:
      'Create, send, and track invoices with automated payment reminders. Support for partial payments and late fees.',
    icon: InvoiceIcon,
  },
  {
    title: 'Vendor & Expense Tracking',
    description:
      "Manage vendor relationships and track expenses with receipt attachments. Streamline your HOA's financial operations.",
    icon: VendorIcon,
  },
  {
    title: 'Document Storage',
    description:
      'Centralized document management for unit-level and community-wide files. Easy access for board members and residents.',
    icon: DocumentIcon,
  },
  {
    title: 'Owner Portal',
    description:
      'Self-service portal for residents to view invoices, make payments, and access community documents.',
    icon: PortalIcon,
  },
  {
    title: 'Multi-tenant Architecture',
    description:
      'Secure, isolated data for each HOA. Built for scale with enterprise-grade security and reliability.',
    icon: SecurityIcon,
  },
  {
    title: 'Financial Reporting',
    description:
      'Comprehensive financial reports and analytics. Track income, expenses, and budget performance.',
    icon: ReportIcon,
  },
  {
    title: 'Announcements & Communication',
    description:
      'Send announcements to residents via email and push notifications. Keep your community informed.',
    icon: AnnouncementIcon,
  },
];
</script>
