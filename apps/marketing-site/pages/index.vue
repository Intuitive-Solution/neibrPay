<template>
  <div>
    <AppHeader />
    <main>
      <HeroSection />
      <FeaturesSection />
      <HowItWorksSection />
      <TrustSection />
      <PricingSection />
      <FaqSection />
      <ContactFormSection />
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
useHead({
  title: 'NeibrPay — HOA Dues Collection & Community Management Platform',
  meta: [
    {
      name: 'description',
      content:
        'Collect HOA dues online via Zelle, ACH, or card. Manage vendors, store documents, and communicate with residents. Built for self-managed HOAs and small community associations.',
    },
  ],
});
</script>
