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
  /** URL slug, used in `/blog/<slug>`. */
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
   * Recommended: 1200 × 630 (1.91:1); matches Facebook OG and renders well
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
      'A complete, no-fluff guide to choosing HOA management software for self-managed boards in 2026: features, pricing, must-have integrations, and how to roll it out without overwhelming your volunteers.',
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
      alt: 'The Ultimate Guide to HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'The Ultimate Guide to HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
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
  {
    title:
      'The Ultimate Guide to Florida HOA Management Software for Self-Managed Boards in 2026',
    slug: 'florida-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Florida HOA management software for self-managed boards in 2026: Chapter 720 / 718 compliance, SIRS-aware reserves, hurricane-grade communications, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Florida HOA management software guide for 2026: Chapter 720 / 718 compliance, SIRS reserves, ACH/Zelle dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Florida HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 18,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Florida HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Florida HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Florida',
      'self-managed HOA',
      'HOA software',
      'Chapter 720',
      'SIRS',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to California HOA Management Software for Self-Managed Boards in 2026',
    slug: 'california-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to California HOA management software for self-managed boards in 2026: Davis-Stirling compliance, reserve studies, SB 326 inspections, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'California HOA management software guide for 2026: Davis-Stirling compliance, reserve studies, SB 326, ACH dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'California HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 18,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'California HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'California HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'California',
      'self-managed HOA',
      'HOA software',
      'Davis-Stirling',
      'SB 326',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to Texas HOA Management Software for Self-Managed Boards in 2026',
    slug: 'texas-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Texas HOA management software for self-managed boards in 2026: Chapter 209 compliance, §209.0062 payment plans, management certificates, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Texas HOA management software guide for 2026: Chapter 209 compliance, payment plans, management certificates, ACH dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Texas HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 17,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Texas HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Texas HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Texas',
      'self-managed HOA',
      'HOA software',
      'Chapter 209',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to Arizona HOA Management Software for Self-Managed Boards in 2026',
    slug: 'arizona-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Arizona HOA management software for self-managed boards in 2026: Title 33 compliance, ADRE-ready records, snowbird-friendly portals, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Arizona HOA management software guide for 2026: ARS Title 33 compliance, ADRE petitions, ACH dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Arizona HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 17,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Arizona HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Arizona HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Arizona',
      'self-managed HOA',
      'HOA software',
      'Title 33',
      'ADRE',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to Colorado HOA Management Software for Self-Managed Boards in 2026',
    slug: 'colorado-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Colorado HOA management software for self-managed boards in 2026: CCIOA compliance, HB 22-1137 collections workflow, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Colorado HOA management software guide for 2026: CCIOA compliance, HB 22-1137 collections, ACH dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Colorado HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 17,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Colorado HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Colorado HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Colorado',
      'self-managed HOA',
      'HOA software',
      'CCIOA',
      'HB 22-1137',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to Nevada HOA Management Software for Self-Managed Boards in 2026',
    slug: 'nevada-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Nevada HOA management software for self-managed boards in 2026: NRS 116 compliance, NRED-ready records, reserve studies, resale packages, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Nevada HOA management software guide for 2026: NRS 116 compliance, NRED, reserve studies, resale packages, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Nevada HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 17,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Nevada HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Nevada HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Nevada',
      'self-managed HOA',
      'HOA software',
      'NRS 116',
      'NRED',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to Georgia HOA Management Software for Self-Managed Boards in 2026',
    slug: 'georgia-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Georgia HOA management software for self-managed boards in 2026: POA Act / Condominium Act compliance, lien aging, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Georgia HOA management software guide for 2026: POA Act compliance, lien aging, ACH dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Georgia HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 16,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Georgia HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Georgia HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Georgia',
      'self-managed HOA',
      'HOA software',
      'POA Act',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to North Carolina HOA Management Software for Self-Managed Boards in 2026',
    slug: 'north-carolina-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to North Carolina HOA management software for self-managed boards in 2026: Chapter 47F / 47C compliance, budget ratification, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'North Carolina HOA management software guide for 2026: Chapter 47F / 47C compliance, budget ratification, ACH dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'North Carolina HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 16,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'North Carolina HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'North Carolina HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'North Carolina',
      'self-managed HOA',
      'HOA software',
      'Chapter 47F',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to Washington HOA Management Software for Self-Managed Boards in 2026',
    slug: 'washington-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Washington HOA management software for self-managed boards in 2026: WUCIOA compliance, reserve studies, resale certificates, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Washington HOA management software guide for 2026: WUCIOA compliance, reserve studies, resale certificates, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Washington HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 17,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Washington HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Washington HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Washington',
      'self-managed HOA',
      'HOA software',
      'WUCIOA',
      'RCW 64.90',
      'volunteer board',
      'buyer guide',
    ],
    relatedSlugs: [],
  },
  {
    title:
      'The Ultimate Guide to Illinois HOA Management Software for Self-Managed Boards in 2026',
    slug: 'illinois-hoa-management-software-self-managed-boards-2026',
    excerpt:
      'A no-fluff guide to Illinois HOA management software for self-managed boards in 2026: CICAA / Condominium Property Act compliance, Section 22.1 disclosures, fair pricing, and a 30-day rollout plan.',
    metaDescription:
      'Illinois HOA management software guide for 2026: CICAA / CPA compliance, Section 22.1 disclosures, ACH dues, fair pricing, and rollout for boards under 150 units.',
    primaryKeyword: 'Illinois HOA management software',
    category: 'Guides',
    publishedAt: '2026-05-01',
    readMinutes: 17,
    author: DEFAULT_AUTHOR,
    isPillar: true,
    thumbnail: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Illinois HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    heroImage: {
      src: '/images/blog/ultimate-guide-hoa-management-software-self-managed-boards-2026/cover.webp',
      alt: 'Illinois HOA Management Software for Self-Managed Boards in 2026 - NeibrPay HOA dashboard preview',
      width: 1024,
      height: 571,
    },
    tags: [
      'Illinois',
      'self-managed HOA',
      'HOA software',
      'CICAA',
      'Section 22.1',
      'volunteer board',
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
