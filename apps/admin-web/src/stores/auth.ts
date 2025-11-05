import { defineStore } from 'pinia';
import { ref, computed, readonly } from 'vue';
import { onAuthStateChanged, User } from 'firebase/auth';
import { auth } from '../config/firebase';
import {
  authService,
  type AuthUser,
  type TenantData,
  type SignupData,
  type GoogleSignupData,
  type MemberSignupData,
  type MemberGoogleSignupData,
  type LoginData,
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
  const isEmailVerified = computed(() => user.value?.emailVerified || false);
  const userDisplayName = computed(
    () => user.value?.displayName || user.value?.email || 'User'
  );
  const tenantName = computed(() => tenant.value?.name || '');

  // Role-based getters
  const isResident = computed(() => user.value?.role === 'resident');
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isBookkeeper = computed(() => user.value?.role === 'bookkeeper');

  // Actions
  const setUser = (
    authUser: AuthUser,
    tenantData: TenantData,
    authToken: string
  ) => {
    user.value = authUser;
    tenant.value = tenantData;
    token.value = authToken;
    error.value = null;
  };

  const clearUser = () => {
    user.value = null;
    tenant.value = null;
    token.value = null;
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

  // Authentication methods
  const signupWithEmail = async (data: SignupData) => {
    try {
      setLoading(true);
      clearError();

      const result = await authService.signupWithEmail(data);
      setUser(result.user, result.tenant, result.token);

      return result;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Signup failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const signupWithGoogle = async (data: GoogleSignupData) => {
    try {
      setLoading(true);
      clearError();

      const result = await authService.signupWithGoogle(data);
      setUser(result.user, result.tenant, result.token);

      return result;
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : 'Google signup failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const memberSignupWithEmail = async (data: MemberSignupData) => {
    try {
      setLoading(true);
      clearError();

      const result = await authService.memberSignupWithEmail(data);
      setUser(result.user, result.tenant, result.token);

      return result;
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : 'Member signup failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const memberSignupWithGoogle = async (
    data: MemberGoogleSignupData,
    options?: { validateEmailMatch?: string }
  ) => {
    try {
      setLoading(true);
      clearError();

      const result = await authService.memberSignupWithGoogle(data, options);
      setUser(result.user, result.tenant, result.token);

      return result;
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : 'Member Google signup failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const signinWithEmail = async (data: LoginData) => {
    try {
      setLoading(true);
      clearError();

      const result = await authService.signinWithEmail(data);
      setUser(result.user, result.tenant, result.token);

      return result;
    } catch (err) {
      const errorMessage = err instanceof Error ? err.message : 'Signin failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const signinWithGoogle = async () => {
    try {
      setLoading(true);
      clearError();

      const result = await authService.signinWithGoogle();
      setUser(result.user, result.tenant, result.token);

      return result;
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : 'Google signin failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const signout = async () => {
    try {
      setLoading(true);
      clearError();

      await authService.signout();
      clearUser();
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : 'Signout failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  const signinWithMagicLink = async (customToken: string) => {
    try {
      setLoading(true);
      clearError();

      // Import Firebase auth function
      const { signInWithCustomToken } = await import('firebase/auth');
      const { auth } = await import('../config/firebase');

      // Sign in with custom token
      const userCredential = await signInWithCustomToken(auth, customToken);
      const idToken = await userCredential.user.getIdToken();

      // Exchange token with backend
      const result = await authService.exchangeMagicToken(idToken);
      setUser(result.user, result.tenant, result.token);

      return result;
    } catch (err) {
      const errorMessage =
        err instanceof Error ? err.message : 'Magic link authentication failed';
      setError(errorMessage);
      throw err;
    } finally {
      setLoading(false);
    }
  };

  // Initialize auth state listener
  const initializeAuth = () => {
    if (isInitialized.value) return;

    onAuthStateChanged(auth, async (firebaseUser: User | null) => {
      if (firebaseUser) {
        try {
          // Get fresh token
          const idToken = await firebaseUser.getIdToken();

          // Get user and tenant data from backend
          const response = await fetch(
            `${(import.meta as any).env?.VITE_API_URL || 'http://localhost:8000/api'}/auth/me`,
            {
              method: 'GET',
              headers: {
                Authorization: `Bearer ${idToken}`,
              },
              signal: AbortSignal.timeout(10000), // 10 second timeout
            }
          );

          if (response.ok) {
            const result = await response.json();
            setUser(
              {
                uid: firebaseUser.uid,
                email: firebaseUser.email,
                displayName: firebaseUser.displayName,
                emailVerified: firebaseUser.emailVerified,
                phoneNumber: firebaseUser.phoneNumber,
                photoURL: firebaseUser.photoURL,
                role: result.user?.role,
              },
              result.tenant,
              idToken
            );
          } else {
            // Token might be invalid, clear user
            clearUser();
          }
        } catch (error) {
          console.error('Error fetching user data:', error);
          clearUser();
        }
      } else {
        clearUser();
      }

      isInitialized.value = true;
    });
  };

  // Refresh token
  const refreshToken = async () => {
    const currentUser = auth.currentUser;
    if (currentUser) {
      try {
        const newToken = await currentUser.getIdToken(true);
        token.value = newToken;
        return newToken;
      } catch (error) {
        console.error('Error refreshing token:', error);
        clearUser();
        throw error;
      }
    }
    return null;
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
    signupWithEmail,
    signupWithGoogle,
    memberSignupWithEmail,
    memberSignupWithGoogle,
    signinWithEmail,
    signinWithGoogle,
    signinWithMagicLink,
    signout,
    initializeAuth,
    refreshToken,
    setError,
    clearError,
  };
});
