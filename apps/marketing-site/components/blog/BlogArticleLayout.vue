<template>
  <article>
    <!-- Hero -->
    <section class="bg-white border-b border-gray-100">
      <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 pt-10 md:pt-14 pb-10">
        <NuxtLink
          to="/blog"
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
          All Articles
        </NuxtLink>

        <div class="flex flex-wrap items-center gap-2 mb-5">
          <span
            class="inline-flex items-center rounded-full bg-primary-100 text-primary-800 px-3 py-1 text-xs font-semibold"
          >
            {{ post.category }}
          </span>
          <span
            v-if="post.isPillar"
            class="inline-flex items-center rounded-full bg-accent-100 text-accent-800 px-3 py-1 text-xs font-semibold"
          >
            Pillar Guide
          </span>
        </div>

        <h1
          class="text-3xl md:text-4xl lg:text-5xl font-bold text-primary-900 leading-tight mb-5"
        >
          {{ post.title }}
        </h1>
        <p class="text-lg md:text-xl text-gray-600 leading-relaxed mb-8">
          {{ post.excerpt }}
        </p>

        <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600">
          <div class="flex items-center gap-3">
            <div
              class="w-10 h-10 rounded-full bg-primary-100 text-primary-800 font-bold flex items-center justify-center text-sm"
              aria-hidden="true"
            >
              {{ post.author.initials }}
            </div>
            <div>
              <p class="font-semibold text-gray-900 leading-tight">
                {{ post.author.name }}
              </p>
              <p class="text-xs text-gray-500 leading-tight">
                {{ post.author.role }}
              </p>
            </div>
          </div>
          <span class="hidden sm:inline text-gray-300">•</span>
          <time :datetime="post.publishedAt">
            {{ formattedDate }}
          </time>
          <span class="hidden sm:inline text-gray-300">•</span>
          <span>{{ post.readMinutes }} min read</span>
        </div>
      </div>
    </section>

    <!-- Hero / Header Image -->
    <section v-if="post.heroImage" class="bg-white">
      <div
        class="mx-auto px-4 sm:px-6 lg:px-8 -mt-2 md:mt-0"
        :class="heroIsWide ? 'max-w-4xl' : 'max-w-2xl'"
      >
        <figure
          class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm bg-neutral-50"
        >
          <img
            :src="post.heroImage.src"
            :alt="post.heroImage.alt"
            :width="post.heroImage.width"
            :height="post.heroImage.height"
            class="w-full h-auto block"
            loading="eager"
            fetchpriority="high"
            decoding="async"
          />
        </figure>
      </div>
    </section>

    <!-- Body + Sidebar -->
    <section class="bg-white">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-16">
        <div class="lg:grid lg:grid-cols-[1fr,260px] lg:gap-12">
          <!-- Article body -->
          <div class="prose-blog max-w-3xl mx-auto lg:mx-0">
            <slot />
          </div>

          <!-- Sticky sidebar (TOC + CTA) -->
          <aside
            v-if="tableOfContents.length"
            class="hidden lg:block"
            aria-label="Article navigation"
          >
            <div class="sticky top-24 space-y-6">
              <nav>
                <p
                  class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3"
                >
                  On this page
                </p>
                <ul class="space-y-2 text-sm">
                  <li v-for="item in tableOfContents" :key="item.id">
                    <a
                      :href="`#${item.id}`"
                      class="block text-gray-600 hover:text-primary-700 transition-colors leading-snug"
                    >
                      {{ item.label }}
                    </a>
                  </li>
                </ul>
              </nav>

              <div
                class="rounded-xl border border-primary-100 bg-primary-50 p-5"
              >
                <p class="text-sm font-semibold text-primary-900 mb-1">
                  Run your HOA on NeibrPay
                </p>
                <p class="text-xs text-gray-700 mb-4">
                  Online dues, vendor payments, and a homeowner portal — built
                  for self-managed boards.
                </p>
                <NuxtLink
                  to="/get-started"
                  class="btn-primary btn-sm w-full justify-center"
                >
                  Get Started
                </NuxtLink>
              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>

    <!-- Related Posts -->
    <section
      v-if="relatedPosts.length"
      class="bg-neutral-50 border-t border-gray-100 py-16"
    >
      <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2
          class="text-2xl md:text-3xl font-bold text-primary-800 text-center mb-12"
        >
          Keep Reading
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <NuxtLink
            v-for="related in relatedPosts"
            :key="related.slug"
            :to="`/blog/${related.slug}`"
            class="group block rounded-xl bg-white border border-gray-200 overflow-hidden hover:shadow-md transition-all"
          >
            <div
              class="relative min-h-0 min-w-0 bg-neutral-100 overflow-hidden"
              :style="{
                aspectRatio: `${related.thumbnail.width} / ${related.thumbnail.height}`,
              }"
            >
              <img
                :src="related.thumbnail.src"
                :alt="related.thumbnail.alt"
                :width="related.thumbnail.width"
                :height="related.thumbnail.height"
                class="block h-full w-full max-w-full min-w-0 object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                loading="lazy"
                decoding="async"
              />
            </div>
            <div class="p-6">
              <p
                class="text-xs font-semibold uppercase tracking-wider text-primary-700 mb-2"
              >
                {{ related.category }}
              </p>
              <h3
                class="text-lg font-semibold text-primary-900 mb-2 group-hover:text-primary-700 transition-colors leading-snug"
              >
                {{ related.title }}
              </h3>
              <p class="text-sm text-gray-600 line-clamp-3">
                {{ related.excerpt }}
              </p>
            </div>
          </NuxtLink>
        </div>
      </div>
    </section>
  </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { BlogPost } from '~/composables/useBlogData';

interface TocItem {
  id: string;
  label: string;
}

const props = defineProps<{
  post: BlogPost;
  tableOfContents?: TocItem[];
  relatedPosts?: BlogPost[];
}>();

const tableOfContents = computed<TocItem[]>(() => props.tableOfContents ?? []);
const relatedPosts = computed<BlogPost[]>(() => props.relatedPosts ?? []);

/**
 * Constrain the hero figure when the image isn't appreciably wider than tall.
 * Wide images (>= 1.5:1, e.g. 16:9 or 1.91:1) get the full max-w-4xl container;
 * square or portrait images render at max-w-2xl so the article isn't pushed
 * absurdly far down the page.
 */
const heroIsWide = computed(() => {
  const img = props.post.heroImage;
  if (!img || !img.height) return true;
  return img.width / img.height >= 1.5;
});

const formattedDate = computed(() =>
  new Date(`${props.post.publishedAt}T00:00:00Z`).toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
    timeZone: 'UTC',
  })
);
</script>
