<template>
  <section id="faq" class="bg-white py-16 md:py-24">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="section-headline text-center mb-4">
        Frequently Asked Questions
      </h2>
      <p class="section-subheadline text-center mb-12">
        Everything you need to know about NeibrPay
      </p>

      <div class="divide-y divide-gray-200">
        <div
          v-for="(item, index) in faqItems"
          :key="index"
          class="accordion-item"
        >
          <button
            @click="toggle(index)"
            class="accordion-trigger"
            :aria-expanded="openIndex === index"
          >
            <span>{{ item.question }}</span>
            <svg
              class="w-5 h-5 text-gray-500 transition-transform duration-200 flex-shrink-0 ml-4"
              :class="{ 'rotate-180': openIndex === index }"
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
          <div v-show="openIndex === index" class="accordion-content">
            <p>{{ item.answer }}</p>
          </div>
        </div>
      </div>

      <div class="text-center mt-12">
        <p class="text-gray-600 mb-4">Still have questions?</p>
        <NuxtLink to="/contact" class="btn-primary"> Contact Us </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref } from 'vue';

const openIndex = ref<number | null>(null);

const toggle = (index: number) => {
  openIndex.value = openIndex.value === index ? null : index;
};

const faqItems = [
  {
    question: 'What is NeibrPay?',
    answer:
      'NeibrPay is an all-in-one HOA management platform that helps community associations collect dues, manage vendors and expenses, store documents, and communicate with residents. It replaces spreadsheets, paper checks, and scattered tools with a single, easy-to-use system.',
  },
  {
    question: 'Who is NeibrPay for?',
    answer:
      'NeibrPay is built for self-managed HOAs, small community associations, and property managers who want a modern, affordable way to handle dues collection, financial management, and resident communication. Whether you manage 5 units or 250+, NeibrPay scales with your community.',
  },
  {
    question: 'How quickly can I get set up?',
    answer:
      "Most communities are up and running in under 10 minutes. Create your account, add your units and residents, set up your dues schedule, and you're ready to start collecting payments. No technical expertise required — our team is here to help if you need it.",
  },
  {
    question: 'How do residents pay their dues?',
    answer:
      'Residents can pay through multiple convenient options: Zelle for direct bank transfers, ACH bank debits, or credit/debit card. Card and ACH payments are processed securely through Stripe, and bank account verification is handled through Plaid. Residents access their payment portal online — no app download required.',
  },
  {
    question: "Is my HOA's financial data secure?",
    answer:
      "Absolutely. NeibrPay uses enterprise-grade security with complete data isolation between communities. Payment processing is PCI-compliant through Stripe, bank verification uses Plaid's secure infrastructure, and all data is encrypted at rest and in transit. Your community's financial data is never shared or accessible by other organizations.",
  },
  {
    question: 'What does NeibrPay cost — and how does it compare to PayHOA?',
    answer:
      'NeibrPay starts at $19.99/month for communities with up to 20 units, or $16.67/month when billed annually ($200/year). For larger communities, we offer custom Enterprise pricing. All plans include every core feature — invoicing, payments, vendor tracking, document storage, online voting, meetings, and the owner portal. We built NeibrPay specifically for small self-managed HOAs (under 50 units) that want modern software without paying enterprise per-unit pricing.',
  },
  {
    question: 'How do I migrate from spreadsheets or PayHOA?',
    answer:
      'Most small HOAs migrate in an afternoon. You can import your unit, resident, and balance data from CSV (works directly with a spreadsheet export or a PayHOA owner export). Our self-serve onboarding walks you through adding units, inviting residents, and configuring dues. If you want a hand, our team will do a white-glove migration with you on a call — no extra charge. Zero downtime for your community.',
  },
  {
    question: 'Do you offer a free trial?',
    answer:
      'Yes, we offer a 30-day free trial with full access to all features. No credit card required to get started. Sign up, set up your community, and experience the full platform before committing. Our team is available to help you every step of the way.',
  },
];
</script>
