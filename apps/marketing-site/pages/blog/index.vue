<template>
  <div>
    <AppHeader />
    <main class="bg-white">
      <!-- Hero -->
      <section class="bg-white border-b border-gray-100">
        <div
          class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-20 text-center"
        >
          <p
            class="text-sm font-semibold uppercase tracking-wider text-primary-700 mb-3"
          >
            NeibrPay Blog
          </p>
          <h1
            class="text-4xl md:text-5xl lg:text-6xl font-bold text-primary-900 mb-5 leading-tight"
          >
            Practical guides for self-managed HOAs
          </h1>
          <p class="text-lg md:text-xl text-gray-600 max-w-3xl mx-auto">
            How-tos, comparisons, and pricing breakdowns for volunteer boards
            under 150 units. Written by people who've actually run an HOA.
          </p>
        </div>
      </section>

      <!-- Featured -->
      <section v-if="featuredPost" class="bg-neutral-50 py-12 md:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
          <p
            class="text-xs font-semibold uppercase tracking-wider text-primary-700 mb-4"
          >
            Featured Article
          </p>
          <NuxtLink
            :to="`/blog/${featuredPost.slug}`"
            class="group block rounded-2xl bg-white border border-gray-200 overflow-hidden hover:shadow-md transition-all"
          >
            <div
              class="grid grid-cols-1 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] gap-0 items-stretch md:min-h-0"
            >
              <!--
                Image is position:absolute so its intrinsic width (~1024px) cannot inflate the
                grid track. Wrapper clips overflow; img never participates in grid min-content sizing.
              -->
              <div
                class="relative isolate flex min-h-0 min-w-0 items-center justify-center overflow-hidden bg-neutral-100 px-4 py-5 sm:px-5 sm:py-6 md:h-full md:px-6 md:py-8"
              >
                <!--
                  object-contain + max-height keeps the entire artwork visible (no crop).
                  Slightly smaller caps than a full-bleed hero so the card feels balanced.
                -->
                <img
                  :src="featuredPost.thumbnail.src"
                  :alt="featuredPost.thumbnail.alt"
                  :width="featuredPost.thumbnail.width"
                  :height="featuredPost.thumbnail.height"
                  class="block h-auto w-auto max-h-[200px] max-w-full object-contain sm:max-h-[240px] md:max-h-[280px]"
                  loading="eager"
                  fetchpriority="high"
                  decoding="async"
                />
              </div>
              <div class="min-w-0 p-6 md:p-10 flex flex-col justify-center">
                <div class="flex flex-wrap items-center gap-2 mb-4">
                  <span
                    class="inline-flex items-center rounded-full bg-primary-100 text-primary-800 px-3 py-1 text-xs font-semibold"
                  >
                    {{ featuredPost.category }}
                  </span>
                  <span
                    v-if="featuredPost.isPillar"
                    class="inline-flex items-center rounded-full bg-accent-100 text-accent-800 px-3 py-1 text-xs font-semibold"
                  >
                    Pillar Guide
                  </span>
                </div>
                <h2
                  class="text-2xl md:text-3xl font-bold text-primary-900 mb-4 leading-tight group-hover:text-primary-700 transition-colors"
                >
                  {{ featuredPost.title }}
                </h2>
                <p
                  class="text-gray-600 text-base md:text-lg mb-5 leading-relaxed"
                >
                  {{ featuredPost.excerpt }}
                </p>
                <div
                  class="flex flex-wrap items-center gap-3 text-sm text-gray-500"
                >
                  <span class="font-medium text-gray-700">
                    {{ featuredPost.author.name }}
                  </span>
                  <span class="text-gray-300">•</span>
                  <time :datetime="featuredPost.publishedAt">
                    {{ formatDate(featuredPost.publishedAt) }}
                  </time>
                  <span class="text-gray-300">•</span>
                  <span>{{ featuredPost.readMinutes }} min read</span>
                </div>
              </div>
            </div>
          </NuxtLink>
        </div>
      </section>

      <!-- All Posts -->
      <section class="bg-white py-12 md:py-16">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
          <h2 class="text-2xl md:text-3xl font-bold text-primary-800 mb-8">
            All Articles
          </h2>

          <div
            v-if="otherPosts.length"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
          >
            <NuxtLink
              v-for="post in otherPosts"
              :key="post.slug"
              :to="`/blog/${post.slug}`"
              class="group block rounded-xl bg-white border border-gray-200 overflow-hidden hover:shadow-md transition-all"
            >
              <div
                class="relative min-h-0 min-w-0 bg-neutral-100 overflow-hidden"
                :style="{
                  aspectRatio: `${post.thumbnail.width} / ${post.thumbnail.height}`,
                }"
              >
                <img
                  :src="post.thumbnail.src"
                  :alt="post.thumbnail.alt"
                  :width="post.thumbnail.width"
                  :height="post.thumbnail.height"
                  class="block h-full w-full max-w-full min-w-0 object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                  loading="lazy"
                  decoding="async"
                />
              </div>
              <div class="p-6">
                <p
                  class="text-xs font-semibold uppercase tracking-wider text-primary-700 mb-2"
                >
                  {{ post.category }}
                </p>
                <h3
                  class="text-lg font-semibold text-primary-900 mb-2 group-hover:text-primary-700 transition-colors leading-snug"
                >
                  {{ post.title }}
                </h3>
                <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                  {{ post.excerpt }}
                </p>
                <div class="flex items-center gap-2 text-xs text-gray-500">
                  <time :datetime="post.publishedAt">
                    {{ formatDate(post.publishedAt) }}
                  </time>
                  <span class="text-gray-300">•</span>
                  <span>{{ post.readMinutes }} min read</span>
                </div>
              </div>
            </NuxtLink>
          </div>

          <div
            v-else
            class="rounded-xl bg-neutral-50 border border-gray-200 p-10 text-center"
          >
            <p class="text-gray-600 mb-2 font-medium">
              More articles are on the way.
            </p>
            <p class="text-sm text-gray-500">
              We're publishing 10 deep guides a month for self-managed boards.
              The featured article above is just the start.
            </p>
          </div>
        </div>
      </section>

      <RecentBlogsSection />
      <ContactFormSection />
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useBlogData } from '~/composables/useBlogData';

const { posts, featuredPost } = useBlogData();

const otherPosts = computed(() =>
  featuredPost ? posts.filter(p => p.slug !== featuredPost.slug) : posts
);

const formatDate = (iso: string) =>
  new Date(`${iso}T00:00:00Z`).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
    timeZone: 'UTC',
  });

const canonicalHref = useCanonicalHref();

useHead({
  title: 'NeibrPay Blog — HOA Management Guides for Self-Managed Boards',
  meta: [
    {
      name: 'description',
      content:
        'Practical guides, comparisons, and pricing breakdowns for self-managed HOA boards. Run your community without a property management company.',
    },
  ],
  link: [{ rel: 'canonical', href: canonicalHref }],
});
</script>
