<template>
  <div>
    <AppHeader />
    <main class="bg-white">
      <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <h1 class="text-4xl md:text-5xl font-bold text-primary-800 mb-4">
          About NeibrPay
        </h1>
        <p class="text-gray-600 mb-10 text-lg max-w-3xl">
          NeibrPay is a dedicated HOA operations platform for self-managed
          communities and small property managers. We bring dues collection,
          vendor and expense tracking, resident communication, and financial
          reporting into one place so your board spends less time on admin work.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
          <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-primary-800 mb-2">
              What we do
            </h2>
            <ul class="space-y-2 text-gray-700 text-sm">
              <li>Collect HOA dues with card, ACH, and Zelle workflows</li>
              <li>Track vendors, invoices, receipts, and community expenses</li>
              <li>Share announcements and documents with residents</li>
            </ul>
          </div>

          <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-primary-800 mb-2">
              Who we work with
            </h2>
            <ul class="space-y-2 text-gray-700 text-sm">
              <li>Self-managed HOA board members</li>
              <li>Small community associations</li>
              <li>Property managers handling small HOA portfolios</li>
            </ul>
          </div>

          <div class="bg-gray-50 border border-gray-200 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-primary-800 mb-2">
              How we work
            </h2>
            <ul class="space-y-2 text-gray-700 text-sm">
              <li>Quick onboarding for your community and unit structure</li>
              <li>Simple setup for payment collection and reminders</li>
              <li>Responsive support with practical HOA-focused guidance</li>
            </ul>
          </div>
        </div>

        <section class="bg-primary-50 border border-primary-100 rounded-xl p-8">
          <h2 class="text-2xl font-bold text-primary-800 mb-3">
            Built for trust and clarity
          </h2>
          <p class="text-gray-700 mb-4">
            HOA finances require reliability and transparency. NeibrPay is
            designed to keep records organized, improve communication, and
            reduce manual follow-up for volunteers and managers.
          </p>
          <p class="text-gray-700">
            If you are evaluating software for your community, we can help you
            choose the right setup and rollout plan.
          </p>
          <div class="mt-6 flex flex-wrap gap-3">
            <NuxtLink to="/get-started" class="btn-primary">
              Get Started
            </NuxtLink>
            <NuxtLink to="/contact" class="btn-outline"> Contact Us </NuxtLink>
          </div>
        </section>
      </section>
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
const canonicalHref = useCanonicalHref();

useHead({
  title: 'About NeibrPay - HOA Management Platform',
  meta: [
    {
      name: 'description',
      content:
        'Learn about NeibrPay, a platform built for self-managed HOAs and small community associations to handle dues, vendors, expenses, and resident communication.',
    },
  ],
  link: [{ rel: 'canonical', href: canonicalHref }],
});
</script>
