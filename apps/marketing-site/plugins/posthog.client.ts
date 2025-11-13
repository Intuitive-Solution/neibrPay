import posthog from 'posthog-js';

export default defineNuxtPlugin(nuxtApp => {
  const config = useRuntimeConfig();

  const posthogKey = config.public.posthogKey || '';
  const posthogHost = config.public.posthogHost || 'https://us.i.posthog.com';

  if (!posthogKey) {
    console.warn('PostHog key not found. Analytics tracking disabled.');
    return;
  }

  // Initialize PostHog only on client side
  if (process.client && !(posthog as any).__loaded) {
    posthog.init(posthogKey, {
      api_host: posthogHost,
      capture_pageview: true,
      capture_pageleave: true,
      loaded: posthogInstance => {
        if (process.env.NODE_ENV === 'development') {
          posthogInstance.debug();
        }
      },
    });

    // Make PostHog available via $posthog
    return {
      provide: {
        posthog: posthog,
      },
    };
  }
});
