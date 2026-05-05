<template>
  <div v-if="post">
    <AppHeader />
    <main class="bg-white">
      <BlogArticleLayout
        :post="post"
        :table-of-contents="tableOfContents"
        :related-posts="relatedPosts"
      >
        <component :is="articleComponent" />
      </BlogArticleLayout>
    </main>
    <RecentBlogsSection v-if="!relatedPosts.length" :exclude-slug="slug" />
    <ContactFormSection />
    <AppFooter />
  </div>

  <!-- 404 fallback -->
  <div v-else>
    <AppHeader />
    <main class="bg-white py-24">
      <div class="max-w-2xl mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold text-primary-800 mb-4">
          Article not found
        </h1>
        <p class="text-gray-600 mb-8">
          The post you're looking for doesn't exist or has moved.
        </p>
        <NuxtLink to="/blog" class="btn-primary">View All Articles</NuxtLink>
      </div>
    </main>
    <AppFooter />
  </div>
</template>

<script setup lang="ts">
import { computed, defineAsyncComponent } from 'vue';
import BlogArticleLayout from '~/components/blog/BlogArticleLayout.vue';
import { useBlogData } from '~/composables/useBlogData';

const route = useRoute();
const config = useRuntimeConfig();
const { getPostBySlug, getRelatedPosts } = useBlogData();

const siteUrl =
  String(config.public.siteUrl ?? '').replace(/\/$/, '') ||
  'https://neibrpay.com';

const slug = computed(() => String(route.params.slug));
const post = computed(() => getPostBySlug(slug.value));
const canonicalHref = useCanonicalHref(computed(() => `/blog/${slug.value}`));

const relatedPosts = computed(() =>
  post.value ? getRelatedPosts(post.value.relatedSlugs) : []
);

/**
 * Map slug → article component. Each blog post is a self-contained
 * Vue component in `components/blog/articles/` so long-form content
 * stays out of the data composable and prerenders cleanly.
 */
const articleComponents: Record<
  string,
  ReturnType<typeof defineAsyncComponent>
> = {
  'ultimate-guide-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import('~/components/blog/articles/UltimateGuideHoaSoftware2026.vue')
    ),
  'florida-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareFlorida2026.vue'
        )
    ),
  'california-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareCalifornia2026.vue'
        )
    ),
  'texas-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareTexas2026.vue'
        )
    ),
  'arizona-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareArizona2026.vue'
        )
    ),
  'colorado-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareColorado2026.vue'
        )
    ),
  'nevada-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareNevada2026.vue'
        )
    ),
  'georgia-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareGeorgia2026.vue'
        )
    ),
  'north-carolina-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareNorthCarolina2026.vue'
        )
    ),
  'washington-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareWashington2026.vue'
        )
    ),
  'illinois-hoa-management-software-self-managed-boards-2026':
    defineAsyncComponent(
      () =>
        import(
          '~/components/blog/articles/UltimateGuideHoaSoftwareIllinois2026.vue'
        )
    ),
};

const articleComponent = computed(() =>
  post.value ? articleComponents[post.value.slug] || null : null
);

/**
 * Per-article table of contents. Keeping this here (vs. parsing the
 * component) lets us prerender deterministically without runtime DOM access.
 *
 * The pillar 2026 guide and the 10 state variations share the same anchor
 * IDs in the article body. State variations include an extra
 * `state-compliance` section, so they get their own slightly longer TOC.
 */
const pillarToc: Array<{ id: string; label: string }> = [
  { id: 'why-self-managed-needs-software', label: 'Why HOAs need software' },
  { id: 'what-is-hoa-software', label: 'What HOA software does' },
  { id: 'must-have-features', label: '8 must-have features' },
  { id: 'cost-breakdown', label: 'Realistic 2026 pricing' },
  { id: 'ai-and-automation', label: 'AI and automation' },
  { id: 'choose', label: 'Buyer’s checklist' },
  { id: 'comparison', label: 'Top platforms compared' },
  { id: 'switch', label: 'How to switch tools' },
  { id: 'rollout', label: '30-day rollout plan' },
  { id: 'mistakes', label: 'Mistakes to avoid' },
  { id: 'why-neibrpay', label: 'Why NeibrPay' },
  { id: 'faq', label: 'FAQ' },
  { id: 'final-cta', label: 'The bottom line' },
];

const stateToc: Array<{ id: string; label: string }> = [
  { id: 'why-self-managed-needs-software', label: 'Why HOAs need software' },
  { id: 'what-is-hoa-software', label: 'What HOA software does' },
  { id: 'must-have-features', label: '8 must-have features' },
  { id: 'cost-breakdown', label: 'Realistic 2026 pricing' },
  { id: 'state-compliance', label: 'State compliance' },
  { id: 'ai-and-automation', label: 'AI and automation' },
  { id: 'choose', label: 'Buyer’s checklist' },
  { id: 'comparison', label: 'Top platforms compared' },
  { id: 'switch', label: 'How to switch tools' },
  { id: 'rollout', label: '30-day rollout plan' },
  { id: 'mistakes', label: 'Mistakes to avoid' },
  { id: 'why-neibrpay', label: 'Why NeibrPay' },
  { id: 'faq', label: 'FAQ' },
  { id: 'final-cta', label: 'The bottom line' },
];

const tableOfContentsBySlug: Record<
  string,
  Array<{ id: string; label: string }>
> = {
  'ultimate-guide-hoa-management-software-self-managed-boards-2026': pillarToc,
  'florida-hoa-management-software-self-managed-boards-2026': stateToc,
  'california-hoa-management-software-self-managed-boards-2026': stateToc,
  'texas-hoa-management-software-self-managed-boards-2026': stateToc,
  'arizona-hoa-management-software-self-managed-boards-2026': stateToc,
  'colorado-hoa-management-software-self-managed-boards-2026': stateToc,
  'nevada-hoa-management-software-self-managed-boards-2026': stateToc,
  'georgia-hoa-management-software-self-managed-boards-2026': stateToc,
  'north-carolina-hoa-management-software-self-managed-boards-2026': stateToc,
  'washington-hoa-management-software-self-managed-boards-2026': stateToc,
  'illinois-hoa-management-software-self-managed-boards-2026': stateToc,
};

const tableOfContents = computed(() =>
  post.value ? tableOfContentsBySlug[post.value.slug] || [] : []
);

useHead(() => {
  const p = post.value;
  if (!p) {
    return { title: 'Article not found - NeibrPay' };
  }

  const description = p.metaDescription || p.excerpt;
  const shareImageUrl = `${siteUrl}${p.thumbnail.src}`;

  /**
   * Article schema improves rich-result eligibility (date, author, headline).
   * Kept inline so each article gets per-page structured data without a global
   * plugin.
   */
  const articleSchema = {
    '@context': 'https://schema.org',
    '@type': 'Article',
    headline: p.title,
    description,
    image: [shareImageUrl],
    datePublished: p.publishedAt,
    dateModified: p.updatedAt || p.publishedAt,
    author: {
      '@type': 'Organization',
      name: p.author.name,
    },
    publisher: {
      '@type': 'Organization',
      name: 'NeibrPay',
      url: `${siteUrl}/`,
    },
    mainEntityOfPage: {
      '@type': 'WebPage',
      '@id': canonicalHref.value,
    },
  };

  return {
    title: `${p.title} - NeibrPay Blog`,
    meta: [
      { name: 'description', content: description },
      { property: 'og:title', content: p.title },
      { property: 'og:description', content: description },
      { property: 'og:type', content: 'article' },
      { property: 'og:url', content: canonicalHref.value },
      { property: 'og:image', content: shareImageUrl },
      {
        property: 'og:image:width',
        content: String(p.thumbnail.width),
      },
      {
        property: 'og:image:height',
        content: String(p.thumbnail.height),
      },
      { property: 'og:image:alt', content: p.thumbnail.alt },
      { property: 'article:published_time', content: p.publishedAt },
      {
        property: 'article:modified_time',
        content: p.updatedAt || p.publishedAt,
      },
      { name: 'twitter:card', content: 'summary_large_image' },
      { name: 'twitter:title', content: p.title },
      { name: 'twitter:description', content: description },
      { name: 'twitter:image', content: shareImageUrl },
      { name: 'twitter:image:alt', content: p.thumbnail.alt },
    ],
    link: [{ rel: 'canonical', href: canonicalHref.value }],
    script: [
      {
        type: 'application/ld+json',
        innerHTML: JSON.stringify(articleSchema),
      },
    ],
  };
});

if (!post.value) {
  setResponseStatus(404);
}
</script>
