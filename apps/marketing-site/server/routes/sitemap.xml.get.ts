import { getRequestURL } from 'h3';
import { useFeatureData } from '../../composables/useFeatureData';
import { useBlogData } from '../../composables/useBlogData';

interface SitemapEntry {
  path: string;
  changefreq: string;
  priority: string;
  lastmod?: string;
}

const STATIC_PATHS: SitemapEntry[] = [
  { path: '/', changefreq: 'weekly', priority: '1.0' },
  { path: '/about', changefreq: 'monthly', priority: '0.7' },
  { path: '/get-started', changefreq: 'monthly', priority: '0.9' },
  { path: '/contact', changefreq: 'monthly', priority: '0.8' },
  { path: '/support', changefreq: 'monthly', priority: '0.8' },
  { path: '/blog', changefreq: 'weekly', priority: '0.8' },
  { path: '/privacy', changefreq: 'yearly', priority: '0.4' },
  { path: '/terms', changefreq: 'yearly', priority: '0.4' },
];

function buildFeaturePaths(): SitemapEntry[] {
  const { features } = useFeatureData();
  return features.map(f => ({
    path: `/features/${f.slug}`,
    changefreq: 'monthly',
    priority: '0.8',
  }));
}

function buildBlogPaths(): SitemapEntry[] {
  const { posts } = useBlogData();
  return posts.map(p => ({
    path: `/blog/${p.slug}`,
    changefreq: 'monthly',
    priority: p.isPillar ? '0.9' : '0.7',
    lastmod: p.updatedAt || p.publishedAt,
  }));
}

function escapeXml(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

export default defineEventHandler(event => {
  const config = useRuntimeConfig();
  const publicSiteUrl =
    (config.public.siteUrl as string | undefined)?.replace(/\/$/, '') || '';

  const origin =
    publicSiteUrl ||
    `${getRequestURL(event).protocol}//${getRequestURL(event).host}`;

  const defaultLastmod = new Date().toISOString().split('T')[0];

  const paths: SitemapEntry[] = [
    ...STATIC_PATHS,
    ...buildFeaturePaths(),
    ...buildBlogPaths(),
  ];

  const urls = paths
    .map(({ path, changefreq, priority, lastmod }) => {
      // Netlify serves directory-style URLs with a trailing slash; match <loc> to avoid 301 from sitemap.
      const locPath = path === '/' ? '/' : `${path.replace(/\/$/, '')}/`;
      return `
  <url>
    <loc>${escapeXml(`${origin}${locPath === '/' ? '/' : locPath}`)}</loc>
    <lastmod>${lastmod || defaultLastmod}</lastmod>
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`;
    })
    .join('');

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${urls}
</urlset>`;

  setHeader(event, 'Content-Type', 'application/xml; charset=utf-8');
  setHeader(event, 'Cache-Control', 'public, max-age=3600, s-maxage=86400');

  return xml;
});
