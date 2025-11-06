import { defineStore } from 'pinia';
import { ref, computed, readonly } from 'vue';
import {
  authService,
  type AuthUser,
  type TenantData,
  type AuthResponse,
} from '../services/auth';

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref<AuthUser | null>(null);
  const tenant = ref<TenantData | null>(null);
  const token = ref<string | null>(null);
  const isLoading = ref(false);
  const error = ref<string | null>(null);
  const isInitialized = ref(false);

  // Getters
  const isAuthenticated = computed(() => !!user.value && !!token.value);
  const isEmailVerified = computed(() => user.value?.email_verified || false);
  const userDisplayName = computed(
    () => user.value?.name || user.value?.email || 'User'
  );
  const tenantName = computed(() => tenant.value?.name || '');

  // Role-based getters
  const isResident = computed(() => user.value?.role === 'resident');
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isBookkeeper = computed(() => user.value?.role === 'bookkeeper');

  // Actions
  const setAuth = (authData: AuthResponse) => {
    user.value = authData.user;
    tenant.value = authData.tenant;
    token.value = authData.token;
    authService.setToken(authData.token);
    error.value = null;
  };

  const clearAuth = () => {
    user.value = null;
    tenant.value = null;
    token.value = null;
    authService.clearToken();
    error.value = null;
  };

  const setError = (errorMessage: string) => {
    error.value = errorMessage;
  };

  const clearError = () => {
    error.value = null;
  };

  const setLoading = (loading: boolean) => {
    isLoading.value = loading;
  };

  // Initialize auth state from localStorage
  const initializeAuth = async () => {
    if (isInitialized.value) return;

    const savedToken = authService.getToken();
    if (savedToken) {
      token.value = savedToken;
      try {
        const result = await authService.getCurrentUser();
        user.value = result.user;
        tenant.value = result.tenant;
      } catch (error) {
        // Token is invalid, clear it
        clearAuth();
      }
    }

    isInitialized.value = true;
  };

  // Logout
  const logout = async () => {
    try {
      setLoading(true);
      clearError();

      await authService.logout();
      clearAuth();
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Logout failed';
      setError(errorMessage);
      // Clear auth anyway
      clearAuth();
    } finally {
      setLoading(false);
    }
  };

  // Refresh user data
  const refreshUser = async () => {
    try {
      const result = await authService.getCurrentUser();
      user.value = result.user;
      tenant.value = result.tenant;
    } catch (error) {
      console.error('Error refreshing user data:', error);
      // If refresh fails, user might be logged out
      clearAuth();
    }
  };

  return {
    // State
    user: readonly(user),
    tenant: readonly(tenant),
    token: readonly(token),
    isLoading: readonly(isLoading),
    error: readonly(error),
    isInitialized: readonly(isInitialized),

    // Getters
    isAuthenticated,
    isEmailVerified,
    userDisplayName,
    tenantName,
    isResident,
    isAdmin,
    isBookkeeper,

    // Actions
    setAuth,
    clearAuth,
    setError,
    clearError,
    setLoading,
    initializeAuth,
    logout,
    refreshUser,
  };
});
