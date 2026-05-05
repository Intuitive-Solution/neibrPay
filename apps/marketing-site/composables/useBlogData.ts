export type BlogCategory =
  | 'Guides'
  | 'Comparisons'
  | 'Pricing'
  | 'How-To'
  | 'Best Practices'
  | 'AI & Automation';

export interface BlogAuthor {
  name: string;
  role: string;
  initials: string;
}

export interface BlogImage {
  /** Path relative to `/public` (e.g. `/images/blog/<slug>/hero.svg`). */
  src: string;
  alt: string;
  width: number;
  height: number;
}

export interface BlogPost {
  /** Article title used in cards, hero, and <title>. */
  title: string;
  /** URL slug — used in `/blog/<slug>`. */
  slug: string;
  /** Short summary for cards, OpenGraph, and meta description fallback. */
  excerpt: string;
  /** Meta description (≤ 160 chars recommended). Falls back to excerpt. */
  metaDescription?: string;
  /** Primary keyword phrase the article targets (kept for editorial reference). */
  primaryKeyword: string;
  category: BlogCategory;
  /** ISO date (YYYY-MM-DD). Used for sitemap lastmod and display. */
  publishedAt: string;
  /** ISO date (YYYY-MM-DD). Optional, falls back to publishedAt. */
  updatedAt?: string;
  /** Estimated reading time in minutes. */
  readMinutes: number;
  author: BlogAuthor;
  /**
   * Card thumbnail used on `/blog` listing AND as og:image / twitter:image.
   * Recommended: 1200 × 630 (1.91:1) — matches Facebook OG and renders well
   * inside our card grid. Keep file size < 250 KB (WebP/JPG preferred).
   */
  thumbnail: BlogImage;
  /**
   * Article hero / header image, rendered between the meta and the body.
   * Recommended: 1600 × 900 (16:9). Will be displayed at the article's
   * max-w-3xl (~768 px) width on desktop, so 1600 px gives crisp 2× display.
   */
  heroImage: BlogImage;
  /** Mark as pillar/long-form to render an enhanced layout (TOC, etc). */
  isPillar?: boolean;
  /** Tags for related-post discovery. */
  tags: string[];
  /** Related slugs to surface at the end of the article. */
  relatedSlugs?: string[];
}

const DEFAULT_AUTHOR: BlogAuthor = {
  name: 'NeibrPay Team',
  role: 'HOA Software Specialists',
  initials: 'NP',
};

const posts: BlogPost[] = [
  {
    title:
      'The Ultimate Guide to HOA Management Software for Self-Managed Boards in 2026',
    slug: 'ultimate-guide-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A complete, no-fluff guide to choosing HOA management software for self-managed boards in 2026 — features, pricing, must-have integrations, and how to roll it out without overwhelming your volunteers.',
    metaDescription:
      'The 2026 guide to HOA management software for self-managed boards. Compare features, pricing, AI tools, and rollout steps for small HOAs (under 150 units).',
    primaryKeyword: 'HOA management software for self-managed boards',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 18,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'The Ultimate Guide to HOA Management Software for Self-Managed Boards in 2026 — NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'The Ultimate Guide to HOA Management Software for Self-Managed Boards in 2026 — NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'self-managed HOA',
      'HOA software',
      'volunteer board',
      'dues collection',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
];

export function useBlogData() {
  const allPosts = [...posts].sort((a, b) =>
    b.publishedAt.localeCompare(a.publishedAt)
  );

  const getPostBySlug = (slug: string): BlogPost | undefined =>
    posts.find(p => p.slug === slug);

  const getRelatedPosts = (slugs: string[] = []): BlogPost[] =>
    slugs
      .map(slug => posts.find(p => p.slug === slug))
      .filter((p): p is BlogPost => Boolean(p));

  const featuredPost = allPosts[0];
  const recentPosts = allPosts.slice(0, 6);

  const latestPosts = (limit = 3, excludeSlug?: string): BlogPost[] =>
    allPosts.filter(p => p.slug !== excludeSlug).slice(0, limit);

  return {
    posts: allPosts,
    featuredPost,
    recentPosts,
    latestPosts,
    getPostBySlug,
    getRelatedPosts,
  };
}
