import posthog from 'posthog-js';
import { useAuthStore } from '../stores/auth';
import { onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';

/**
 * PostHog Analytics Composable
 * Provides tracking functionality for user events and page views
 */
export function usePostHog() {
  const authStore = useAuthStore();
  const router = useRouter();

  /**
   * Initialize PostHog
   */
  const initPostHog = () => {
    const posthogKey = import.meta.env.VITE_POSTHOG_KEY || '';
    const posthogHost =
      import.meta.env.VITE_POSTHOG_HOST || 'https://us.i.posthog.com';

    if (!posthogKey) {
      console.warn('PostHog key not found. Analytics tracking disabled.');
      return;
    }

    if (typeof window !== 'undefined' && !(posthog as any).__loaded) {
      posthog.init(posthogKey, {
        api_host: posthogHost,
        person_profiles: 'identified_only', // Only create profiles for identified users
        capture_pageview: false, // We'll handle page views manually
        capture_pageleave: true,
        loaded: posthogInstance => {
          if (import.meta.env.DEV) {
            posthogInstance.debug(); // Enable debug mode in development
          }
        },
      });
    }
  };

  /**
   * Identify user after signup/login
   */
  const identifyUser = () => {
    if (!(posthog as any).__loaded || !authStore.user) return;

    posthog.identify(authStore.user.id.toString(), {
      email: authStore.user.email,
      name: authStore.user.name,
      role: authStore.user.role,
      tenant_id: authStore.tenant?.id?.toString(),
      tenant_name: authStore.tenant?.name,
      is_admin: authStore.isAdmin,
      is_resident: authStore.isResident,
      is_bookkeeper: authStore.isBookkeeper,
    });
  };

  /**
   * Reset user identification on logout
   */
  const resetUser = () => {
    if ((posthog as any).__loaded) {
      posthog.reset();
    }
  };

  /**
   * Track custom event
   */
  const trackEvent = (eventName: string, properties?: Record<string, any>) => {
    if (!(posthog as any).__loaded) return;

    posthog.capture(eventName, {
      ...properties,
      timestamp: new Date().toISOString(),
    });
  };

  /**
   * Track page view
   */
  const trackPageView = (routeName?: string, path?: string) => {
    if (!(posthog as any).__loaded) return;

    const route = router.currentRoute.value;
    posthog.capture('$pageview', {
      $current_url: window.location.href,
      path: path || route.path,
      route_name: routeName || route.name,
    });
  };

  /**
   * Track onboarding funnel events
   */
  const trackOnboardingEvent = (
    eventName: string,
    properties?: Record<string, any>
  ) => {
    trackEvent(eventName, {
      ...properties,
      funnel: 'onboarding',
    });
  };

  // Initialize PostHog on mount
  onMounted(() => {
    initPostHog();

    // Track page views on route changes
    router.afterEach(to => {
      trackPageView(to.name as string, to.path);
    });

    // Identify user if already logged in
    if (authStore.isAuthenticated) {
      identifyUser();
    }
  });

  // Watch for auth changes
  watch(
    () => authStore.isAuthenticated,
    isAuthenticated => {
      if (isAuthenticated) {
        identifyUser();
      } else {
        resetUser();
      }
    }
  );

  // Watch for user changes (e.g., after signup)
  watch(
    () => authStore.user,
    user => {
      if (user && authStore.isAuthenticated) {
        identifyUser();
      }
    },
    { deep: true }
  );

  return {
    trackEvent,
    trackPageView,
    trackOnboardingEvent,
    identifyUser,
    resetUser,
  };
}
