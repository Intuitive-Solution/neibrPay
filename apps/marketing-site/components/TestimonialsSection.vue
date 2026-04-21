<template>
  <section id="testimonials" class="bg-white py-16 md:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2 class="section-headline text-center mb-4">
        Loved by Volunteer Boards
      </h2>
      <p class="section-subheadline text-center mb-12">
        What treasurers and board members say after switching to NeibrPay
      </p>

      <!-- Desktop: 3-column grid. Mobile: horizontal scroll-snap carousel. -->
      <div
        class="flex gap-6 overflow-x-auto md:overflow-visible snap-x snap-mandatory md:grid md:grid-cols-3 md:gap-8 pb-4 md:pb-0 -mx-4 px-4 md:mx-0 md:px-0"
      >
        <figure
          v-for="(t, index) in testimonials"
          :key="index"
          class="flex-shrink-0 w-[85%] sm:w-[70%] md:w-auto snap-center bg-neutral-50 rounded-xl p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col"
        >
          <div class="mb-4 flex gap-0.5 text-yellow-400" aria-hidden="true">
            <svg
              v-for="n in 5"
              :key="n"
              class="w-5 h-5"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.45a1 1 0 00-.364 1.118l1.287 3.956c.3.921-.755 1.688-1.539 1.118l-3.37-2.45a1 1 0 00-1.175 0l-3.37 2.45c-.783.57-1.838-.197-1.539-1.118l1.287-3.956a1 1 0 00-.364-1.118L2.05 9.383c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.956z"
              />
            </svg>
          </div>
          <blockquote
            class="text-gray-800 text-lg leading-relaxed mb-6 flex-grow"
          >
            &ldquo;{{ t.quote }}&rdquo;
          </blockquote>
          <figcaption class="text-sm text-gray-500 font-medium">
            &mdash; {{ t.attribution }}
          </figcaption>
        </figure>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
const testimonials = [
  {
    quote:
      "Transitioning from Excel to NeibrPay felt heavenly. It's hard to imagine managing without it.",
    attribution: 'HOA Treasurer, 28-unit condo community',
  },
  {
    quote:
      'We replaced three spreadsheets and a Dropbox folder with one tool our residents actually log into.',
    attribution: 'Board President, 36-unit townhome association',
  },
  {
    quote:
      'Payments went from "whenever someone remembers" to automatic. Late payments dropped within a month.',
    attribution: 'HOA Treasurer, 45-unit condo association',
  },
];
</script>
