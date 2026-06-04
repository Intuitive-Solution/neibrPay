<template>
  <section
    v-if="items.length"
    class="bg-neutral-50 border-t border-gray-100 py-12 md:py-16 lg:py-20"
    aria-labelledby="recent-blog-articles-heading"
  >
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
      <h2
        id="recent-blog-articles-heading"
        class="text-2xl md:text-3xl lg:text-4xl font-bold text-primary-900 text-center mb-10 md:mb-12"
      >
        Recent Blog Articles
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
        <NuxtLink
          v-for="post in items"
          :key="post.slug"
          :to="`/blog/${post.slug}`"
          class="group flex flex-col rounded-xl bg-white border border-gray-200 overflow-hidden hover:shadow-md transition-all duration-200"
        >
          <div
            class="relative min-h-0 min-w-0 bg-neutral-100 overflow-hidden shrink-0"
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
          <div class="p-5 md:p-6 flex-grow flex flex-col">
            <h3
              class="text-lg md:text-xl font-bold text-primary-900 leading-snug group-hover:text-primary-700 transition-colors"
            >
              {{ post.title }}
            </h3>
          </div>
        </NuxtLink>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useBlogData } from '~/composables/useBlogData';

const props = defineProps<{
  /** When set (e.g. on article pages), excludes this slug from the 3 previews. */
  excludeSlug?: string;
}>();

const { latestPosts: getLatestPosts } = useBlogData();

const items = computed(() => getLatestPosts(3, props.excludeSlug ?? undefined));
</script>
