import { getRequestURL } from 'h3';

const PATHS: { path: string; changefreq: string; priority: string }[] = [
  { path: '/', changefreq: 'weekly', priority: '1.0' },
  { path: '/contact', changefreq: 'monthly', priority: '0.8' },
  { path: '/support', changefreq: 'monthly', priority: '0.8' },
  { path: '/privacy', changefreq: 'yearly', priority: '0.4' },
  { path: '/terms', changefreq: 'yearly', priority: '0.4' },
];

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

  const lastmod = new Date().toISOString().split('T')[0];

  const urls = PATHS.map(
    ({ path, changefreq, priority }) => `
  <url>
    <loc>${escapeXml(`${origin}${path === '/' ? '/' : path}`)}</loc>
    <lastmod>${lastmod}</lastmod>
    <changefreq>${changefreq}</changefreq>
    <priority>${priority}</priority>
  </url>`
  ).join('');

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">${urls}
</urlset>`;

  setHeader(event, 'Content-Type', 'application/xml; charset=utf-8');
  setHeader(event, 'Cache-Control', 'public, max-age=3600, s-maxage=86400');

  return xml;
});
