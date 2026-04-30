import { computed, toValue } from 'vue';
import type { MaybeRefOrGetter } from 'vue';

/**
 * Path shape for `<link rel="canonical">`, matching
 * `server/routes/sitemap.xml.get.ts` (`locPath`): root is `/`, else
 * trailing slash for directory-style URLs on Netlify.
 */
export function canonicalLocPath(path: string): string {
  const p = path.startsWith('/') ? path : `/${path}`;
  return p === '/' ? '/' : `${p.replace(/\/$/, '')}/`;
}

export function canonicalHrefForSite(siteUrl: string, path: string): string {
  const origin = siteUrl.replace(/\/$/, '');
  const locPath = canonicalLocPath(path);
  return locPath === '/' ? `${origin}/` : `${origin}${locPath}`;
}

/**
 * Absolute canonical URL for the current or given path, using
 * `runtimeConfig.public.siteUrl`.
 */
export function useCanonicalHref(path?: MaybeRefOrGetter<string>) {
  const route = useRoute();
  const config = useRuntimeConfig();

  return computed(() => {
    const raw = path != null ? toValue(path) : route.path;
    const siteUrl =
      String(config.public.siteUrl ?? '').replace(/\/$/, '') ||
      'https://neibrpay.com';
    return canonicalHrefForSite(siteUrl, raw);
  });
}
