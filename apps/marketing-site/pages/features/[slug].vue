<template>
  <div v-if="feature">
    <AppHeader />
    <main>
      <!-- Feature Hero -->
      <section class="bg-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div
            class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center"
          >
            <!-- Text -->
            <div>
              <NuxtLink
                to="/#features"
                class="inline-flex items-center text-sm text-primary-700 hover:text-primary-800 font-medium mb-6"
              >
                <svg
                  class="w-4 h-4 mr-1"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M15 19l-7-7 7-7"
                  />
                </svg>
                All Features
              </NuxtLink>

              <h1
                class="text-3xl md:text-4xl lg:text-5xl font-bold text-primary-800 mb-6 leading-tight"
              >
                {{ feature.title }}
              </h1>
              <p class="text-lg md:text-xl text-gray-600 mb-8 leading-relaxed">
                {{ feature.heroDescription }}
              </p>
              <NuxtLink to="/get-started" class="btn-primary btn-lg">
                Get Started
              </NuxtLink>
            </div>

            <!-- Visual -->
            <div class="order-first lg:order-last">
              <!-- Screenshot with browser frame -->
              <div
                v-if="feature.illustrationType === 'screenshot'"
                class="rounded-xl shadow-lg overflow-hidden border border-gray-200"
              >
                <div
                  class="bg-gray-100 px-4 py-3 flex items-center gap-2 border-b border-gray-200"
                >
                  <div class="w-3 h-3 rounded-full bg-red-400" />
                  <div class="w-3 h-3 rounded-full bg-yellow-400" />
                  <div class="w-3 h-3 rounded-full bg-green-400" />
                  <div class="flex-1 mx-4">
                    <div
                      class="bg-white rounded-md px-3 py-1 text-xs text-gray-400 text-center"
                    >
                      app.neibrpay.com
                    </div>
                  </div>
                </div>
                <img
                  :src="feature.illustrationSrc"
                  :alt="`${feature.title} - NeibrPay`"
                  class="w-full h-auto"
                  loading="lazy"
                />
              </div>

              <!-- SVG illustration -->
              <div v-else class="rounded-xl p-4">
                <component :is="illustrationComponent" />
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Benefits -->
      <section class="bg-neutral-50 py-16 md:py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2
            class="text-2xl md:text-3xl font-bold text-primary-800 text-center mb-12"
          >
            What you get with {{ feature.title }}
          </h2>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              v-for="(benefit, index) in feature.benefits"
              :key="index"
              class="flex items-start gap-4 bg-white rounded-lg p-5 shadow-sm"
            >
              <div
                class="mt-0.5 w-6 h-6 rounded-full bg-primary-100 flex items-center justify-center flex-shrink-0"
              >
                <svg
                  class="w-3.5 h-3.5 text-primary-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="3"
                    d="M5 13l4 4L19 7"
                  />
                </svg>
              </div>
              <p class="text-gray-700 leading-relaxed">{{ benefit }}</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Testimonial -->
      <section v-if="feature.testimonial" class="bg-white py-16 md:py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <figure
            class="bg-primary-50 border border-primary-100 rounded-2xl p-8 md:p-12 shadow-sm"
          >
            <div class="mb-6 flex gap-0.5 text-yellow-400" aria-hidden="true">
              <svg
                v-for="n in 5"
                :key="n"
                class="w-6 h-6"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.956a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.45a1 1 0 00-.364 1.118l1.287 3.956c.3.921-.755 1.688-1.539 1.118l-3.37-2.45a1 1 0 00-1.175 0l-3.37 2.45c-.783.57-1.838-.197-1.539-1.118l1.287-3.956a1 1 0 00-.364-1.118L2.05 9.383c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.286-3.956z"
                />
              </svg>
            </div>
            <blockquote
              class="text-xl md:text-2xl text-primary-900 leading-relaxed mb-6 font-medium"
            >
              &ldquo;{{ feature.testimonial.quote }}&rdquo;
            </blockquote>
            <figcaption class="text-sm text-gray-600 font-medium">
              &mdash; {{ feature.testimonial.attribution }}
            </figcaption>
          </figure>
        </div>
      </section>

      <!-- Feature FAQ -->
      <section
        v-if="feature.faqs && feature.faqs.length"
        class="bg-neutral-50 py-16 md:py-24"
      >
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2
            class="text-2xl md:text-3xl font-bold text-primary-800 text-center mb-4"
          >
            {{ feature.title }} FAQ
          </h2>
          <p class="text-gray-600 text-center mb-12">
            Answers to the most common questions about
            {{ feature.title.toLowerCase() }}.
          </p>

          <div class="divide-y divide-gray-200 bg-white rounded-xl shadow-sm">
            <div
              v-for="(item, index) in feature.faqs"
              :key="index"
              class="accordion-item"
            >
              <button
                :aria-expanded="openFaqIndex === index"
                class="accordion-trigger"
                @click="toggleFaq(index)"
              >
                <span>{{ item.question }}</span>
                <svg
                  class="w-5 h-5 text-gray-500 transition-transform duration-200 flex-shrink-0 ml-4"
                  :class="{ 'rotate-180': openFaqIndex === index }"
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
              <div v-show="openFaqIndex === index" class="accordion-content">
                <p>{{ item.answer }}</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Related Features -->
      <section v-if="relatedFeatures.length" class="bg-white py-16 md:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2
            class="text-2xl md:text-3xl font-bold text-primary-800 text-center mb-12"
          >
            Related Features
          </h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <NuxtLink
              v-for="related in relatedFeatures"
              :key="related.slug"
              :to="`/features/${related.slug}`"
              class="group bg-neutral-50 rounded-xl p-6 hover:shadow-md transition-all duration-200 block"
            >
              <h3
                class="text-lg font-semibold text-primary-800 mb-2 group-hover:text-primary-600 transition-colors"
              >
                {{ related.title }}
              </h3>
              <p class="text-sm text-gray-600">
                {{ related.shortDescription }}
              </p>
            </NuxtLink>
          </div>
        </div>
      </section>

      <!-- Contact Form -->
      <ContactFormSection :feature-context="feature.title" />
    </main>
    <AppFooter />
  </div>

  <!-- 404 fallback -->
  <div v-else>
    <AppHeader />
    <main class="bg-white py-24">
      <div class="max-w-2xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold text-primary-800 mb-4">
          Feature not found
        </h1>
        <p class="text-gray-600 mb-8">
          The feature you're looking for doesn't exist.
        </p>
        <NuxtLink to="/#features" class="btn-primary"
          >View All Features</NuxtLink
        >
      </div>
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent, ref } from 'vue';
import { useFeatureData } from '~/composables/useFeatureData';

const route = useRoute();
const config = useRuntimeConfig();
const { getFeatureBySlug, getRelatedFeatures } = useFeatureData();

const slug = computed(() => String(route.params.slug));
const feature = computed(() => getFeatureBySlug(slug.value));
const canonicalUrl = computed(() => {
  const siteUrl = config.public.siteUrl.replace(/\/$/, '');
  return `${siteUrl}/features/${slug.value}/`;
});

const openFaqIndex = ref<number | null>(null);
const toggleFaq = (index: number) => {
  openFaqIndex.value = openFaqIndex.value === index ? null : index;
};

const relatedFeatures = computed(() => {
  if (!feature.value) return [];
  return getRelatedFeatures(feature.value.relatedFeatures);
});

const illustrationComponents: Record<string, any> = {
  DocumentIllustration: defineAsyncComponent(
    () => import('~/components/illustrations/DocumentIllustration.vue')
  ),
  PortalIllustration: defineAsyncComponent(
    () => import('~/components/illustrations/PortalIllustration.vue')
  ),
  SecurityIllustration: defineAsyncComponent(
    () => import('~/components/illustrations/SecurityIllustration.vue')
  ),
  AnnouncementIllustration: defineAsyncComponent(
    () => import('~/components/illustrations/AnnouncementIllustration.vue')
  ),
};

const illustrationComponent = computed(() => {
  if (!feature.value || feature.value.illustrationType !== 'svg') return null;
  return illustrationComponents[feature.value.illustrationSrc] || null;
});

if (feature.value) {
  useHead({
    title: `${feature.value.title} - NeibrPay`,
    meta: [{ name: 'description', content: feature.value.metaDescription }],
    link: [{ rel: 'canonical', href: canonicalUrl.value }],
  });
} else {
  setResponseStatus(404);
}
</script>
