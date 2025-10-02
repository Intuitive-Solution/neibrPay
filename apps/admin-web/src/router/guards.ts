import type { Router } from 'vue-router';
import { useAuthStore } from '../stores/auth';

export function setupAuthGuards(router: Router) {
  // Guard for protected routes (requires authentication)
  router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    // Initialize auth if not already done
    if (!authStore.isInitialized) {
      authStore.initializeAuth();

      // Wait for auth initialization
      await new Promise(resolve => {
        const unwatch = authStore.$subscribe((mutation, state) => {
          if (state.isInitialized) {
            unwatch();
            resolve(true);
          }
        });
      });
    }

    // Check if route requires authentication
    const requiresAuth = to.matched.some(
      record => record.meta.requiresAuth !== false
    );
    const isAuthRoute =
      to.name === 'Login' ||
      to.name === 'Signup' ||
      to.name === 'ForgotPassword' ||
      to.name === 'ResetPassword';

    if (requiresAuth && !authStore.isAuthenticated) {
      // Redirect to login if not authenticated
      next({ name: 'Login' });
    } else if (isAuthRoute && authStore.isAuthenticated) {
      // Redirect to dashboard if already authenticated and trying to access auth pages
      next({ name: 'Dashboard' });
    } else {
      next();
    }
  });
}

// Helper function to check if user has specific role
export function hasRole(role: string): boolean {
  const authStore = useAuthStore();
  // This would need to be implemented based on your user model
  // For now, we'll assume all authenticated users are admins
  return authStore.isAuthenticated;
}

// Helper function to check if user belongs to specific tenant
export function belongsToTenant(tenantId: string): boolean {
  const authStore = useAuthStore();
  return authStore.tenant?.id === tenantId;
}
