import type { Router } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import { setAuthTokenGetter } from '@neibrpay/api-client';

export function setupAuthGuards(router: Router) {
  // Set up token getter for API client - use getActivePinia to ensure Pinia is initialized
  setAuthTokenGetter(() => {
    try {
      const authStore = useAuthStore();
      return authStore.token;
    } catch {
      return null;
    }
  });

  router.beforeEach(async (to, from, next) => {
    // Get auth store inside the guard (after Pinia is initialized)
    const authStore = useAuthStore();

    // Initialize auth if not already done
    if (!authStore.isInitialized) {
      await authStore.initializeAuth();
    }

    // Check if route requires authentication
    if (to.meta.requiresAuth) {
      if (!authStore.isAuthenticated) {
        // Redirect to auth page
        next({ name: 'UnifiedAuth', query: { redirect: to.fullPath } });
      } else {
        next();
      }
    } else {
      // If user is authenticated and trying to access auth page, redirect to dashboard
      if (to.name === 'UnifiedAuth' && authStore.isAuthenticated) {
        const redirect = to.query.redirect as string | undefined;
        next(redirect || '/');
      } else {
        next();
      }
    }
  });
}
