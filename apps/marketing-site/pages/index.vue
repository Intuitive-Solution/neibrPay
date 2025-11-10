<template>
  <div>
    <AppHeader />
    <main>
      <HeroSection />
      <FeaturesSection />
      <PricingSection />
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
useHead({
  title: 'NeibrPay - HOA Management Made Simple',
  meta: [
    {
      name: 'description',
      content:
        'Comprehensive HOA management platform for invoicing, payments, vendor management, and community communication. Built for modern HOAs.',
    },
  ],
});
</script>
