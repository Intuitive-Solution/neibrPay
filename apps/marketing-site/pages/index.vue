<template>
  <div>
    <AppHeader />
    <main>
      <HeroSection />
      <FeaturesSection />
      <HowItWorksSection />
      <TrustSection />
      <TestimonialsSection />
      <PricingSection />
      <FaqSection />
      <ContactFormSection />
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
const canonicalHref = useCanonicalHref();

useHead({
  title:
    'NeibrPay — The Modern HOA Platform for Self-Managed Boards Under 50 Units',
  meta: [
    {
      name: 'description',
      content:
        'NeibrPay is the modern HOA platform for volunteer treasurers and board members running self-managed communities under 50 units. Collect dues online, track every dollar, and migrate from spreadsheets or PayHOA in an afternoon.',
    },
  ],
  link: [{ rel: 'canonical', href: canonicalHref }],
});
</script>
