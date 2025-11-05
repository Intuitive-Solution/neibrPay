import {
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  signInWithPopup,
  signInWithCustomToken,
  signOut,
  sendEmailVerification,
  sendPasswordResetEmail,
  confirmPasswordReset,
  User,
  UserCredential,
  AuthError,
} from 'firebase/auth';
import { auth, googleProvider } from '../config/firebase';

export interface SignupData {
  communityName: string;
  email: string;
  fullName: string;
  phoneNumber: string;
  password: string;
}

export interface GoogleSignupData {
  communityName: string;
  phoneNumber?: string;
}

export interface MemberSignupData {
  residentId: number;
  email: string;
  fullName: string;
  phoneNumber: string;
  password: string;
}

export interface MemberGoogleSignupData {
  residentId: number;
  email: string;
  fullName: string;
  phoneNumber?: string;
}

export interface LoginData {
  email: string;
  password: string;
}

export interface AuthUser {
  uid: string;
  email: string | null;
  displayName: string | null;
  emailVerified: boolean;
  phoneNumber: string | null;
  photoURL: string | null;
  role?: string;
}

export interface TenantData {
  id: string;
  name: string;
  created_at: string;
  updated_at: string;
}

export interface AuthResponse {
  user: AuthUser;
  tenant: TenantData;
  token: string;
}

class AuthService {
  private baseURL =
    (import.meta as any).env?.VITE_API_URL || 'http://localhost:8000/api';
  private readonly REQUEST_TIMEOUT = 10000; // 10 seconds

  /**
   * Convert Firebase User to our AuthUser interface
   */
  private mapFirebaseUser(user: User): AuthUser {
    return {
      uid: user.uid,
      email: user.email,
      displayName: user.displayName,
      emailVerified: user.emailVerified,
      phoneNumber: user.phoneNumber,
      photoURL: user.photoURL,
    };
  }

  /**
   * Make a network request with timeout handling
   */
  private async makeRequest(
    url: string,
    options: RequestInit
  ): Promise<Response> {
    const controller = new AbortController();
    const timeoutId = setTimeout(
      () => controller.abort(),
      this.REQUEST_TIMEOUT
    );

    try {
      const response = await fetch(url, {
        ...options,
        signal: controller.signal,
      });
      clearTimeout(timeoutId);
      return response;
    } catch (error) {
      clearTimeout(timeoutId);

      if (error instanceof Error && error.name === 'AbortError') {
        throw new Error(
          'Request timed out. Please check your connection and try again.'
        );
      }

      // Handle other network errors
      if (error instanceof TypeError && error.message.includes('fetch')) {
        throw new Error(
          'Network error. Please check your internet connection and try again.'
        );
      }

      throw error;
    }
  }

  /**
   * Sign up with email and password
   */
  async signupWithEmail(data: SignupData): Promise<AuthResponse> {
    try {
      // Create user with Firebase
      const userCredential: UserCredential =
        await createUserWithEmailAndPassword(auth, data.email, data.password);

      const user = userCredential.user;

      // Send email verification
      await sendEmailVerification(user);

      // Get ID token
      const idToken = await user.getIdToken();

      // Create tenant and user in backend
      const response = await this.makeRequest(`${this.baseURL}/auth/signup`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${idToken}`,
        },
        body: JSON.stringify({
          community_name: data.communityName,
          full_name: data.fullName,
          phone_number: data.phoneNumber,
          firebase_uid: user.uid,
          email: user.email,
        }),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.message || 'Failed to create account');
      }

      const result = await response.json();

      return {
        user: {
          ...this.mapFirebaseUser(user),
          role: result.user?.role || 'admin', // Default to admin for new signups
        },
        tenant: result.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Signup error:', error);
      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Sign up with Google using popup (primary method)
   */
  async signupWithGoogle(data: GoogleSignupData): Promise<AuthResponse> {
    try {
      console.log('Starting Google signup process...');

      // Sign in with Google popup
      const googleResult = await signInWithPopup(auth, googleProvider);
      const user = googleResult.user;

      console.log('Google signup successful:', user.email);

      // Get ID token
      const idToken = await user.getIdToken();
      console.log('ID token obtained');

      // Create tenant and user in backend
      const response = await this.makeRequest(
        `${this.baseURL}/auth/google-signup`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${idToken}`,
          },
          body: JSON.stringify({
            community_name: data.communityName,
            phone_number: data.phoneNumber,
            firebase_uid: user.uid,
            email: user.email,
            full_name: user.displayName,
          }),
        }
      );

      console.log('Backend response status:', response.status);

      if (!response.ok) {
        const errorData = await response.json();
        console.error('Backend error:', errorData);
        throw new Error(
          errorData.message || 'Failed to create account with Google'
        );
      }

      const responseData = await response.json();
      console.log('Backend signup successful');

      return {
        user: {
          ...this.mapFirebaseUser(user),
          role: responseData.user?.role || 'admin', // Default to admin for new signups
        },
        tenant: responseData.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Google signup error:', error);

      // Handle specific Firebase auth errors
      if (error instanceof Error) {
        if (error.message.includes('popup-closed-by-user')) {
          throw new Error('Google sign-in was cancelled. Please try again.');
        } else if (error.message.includes('popup-blocked')) {
          throw new Error(
            'Popup was blocked by your browser. Please allow popups and try again.'
          );
        } else if (error.message.includes('network-request-failed')) {
          throw new Error(
            'Network error. Please check your internet connection and try again.'
          );
        } else if (error.message.includes('auth/popup-closed-by-user')) {
          throw new Error('Google sign-in was cancelled. Please try again.');
        } else if (error.message.includes('auth/popup-blocked')) {
          throw new Error(
            'Popup was blocked by your browser. Please allow popups and try again.'
          );
        }
      }

      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Member signup with email/password (joins existing tenant)
   */
  async memberSignupWithEmail(data: MemberSignupData): Promise<AuthResponse> {
    try {
      // Create user with Firebase
      const userCredential: UserCredential =
        await createUserWithEmailAndPassword(auth, data.email, data.password);

      const user = userCredential.user;

      // Send email verification
      await sendEmailVerification(user);

      // Get ID token
      const idToken = await user.getIdToken();

      // Update existing resident record in backend
      const response = await this.makeRequest(
        `${this.baseURL}/auth/member-signup`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${idToken}`,
          },
          body: JSON.stringify({
            resident_id: data.residentId,
            full_name: data.fullName,
            phone_number: data.phoneNumber,
            firebase_uid: user.uid,
            email: user.email,
          }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Failed to activate account');
      }

      const result = await response.json();

      return {
        user: {
          ...this.mapFirebaseUser(user),
          role: result.user?.role || 'resident',
        },
        tenant: result.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Member signup error:', error);
      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Member signup with Google (joins existing tenant)
   */
  async memberSignupWithGoogle(
    data: MemberGoogleSignupData,
    options?: { validateEmailMatch?: string }
  ): Promise<AuthResponse> {
    try {
      console.log('Starting member Google signup process...');

      // Sign in with Google popup
      const googleResult = await signInWithPopup(auth, googleProvider);
      const user = googleResult.user;

      console.log('Google signup successful:', user.email);

      // Validate email match if required
      if (
        options?.validateEmailMatch &&
        user.email !== options.validateEmailMatch
      ) {
        // Sign out the user since email doesn't match
        await signOut(auth);
        throw new Error(
          `Google account email must match ${options.validateEmailMatch}. Please use the correct Google account.`
        );
      }

      // Get ID token
      const idToken = await user.getIdToken();
      console.log('ID token obtained');

      // Update existing resident record in backend
      const response = await this.makeRequest(
        `${this.baseURL}/auth/member-google-signup`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${idToken}`,
          },
          body: JSON.stringify({
            resident_id: data.residentId,
            phone_number: data.phoneNumber,
            firebase_uid: user.uid,
            email: user.email,
            full_name: user.displayName || data.fullName,
          }),
        }
      );

      console.log('Backend response status:', response.status);

      if (!response.ok) {
        const errorData = await response.json();
        console.error('Backend error:', errorData);
        // Sign out on error
        await signOut(auth);
        throw new Error(
          errorData.error || 'Failed to activate account with Google'
        );
      }

      const responseData = await response.json();
      console.log('Backend member signup successful');

      return {
        user: {
          ...this.mapFirebaseUser(user),
          role: responseData.user?.role || 'resident',
        },
        tenant: responseData.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Member Google signup error:', error);

      // Handle specific Firebase auth errors
      if (error instanceof Error) {
        if (error.message.includes('popup-closed-by-user')) {
          throw new Error('Google sign-in was cancelled. Please try again.');
        } else if (error.message.includes('popup-blocked')) {
          throw new Error(
            'Popup was blocked by your browser. Please allow popups and try again.'
          );
        } else if (error.message.includes('network-request-failed')) {
          throw new Error(
            'Network error. Please check your internet connection and try again.'
          );
        }
      }

      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Sign in with email and password
   */
  async signinWithEmail(data: LoginData): Promise<AuthResponse> {
    try {
      const userCredential: UserCredential = await signInWithEmailAndPassword(
        auth,
        data.email,
        data.password
      );

      const user = userCredential.user;
      const idToken = await user.getIdToken();

      // Get user and tenant data from backend
      const response = await this.makeRequest(`${this.baseURL}/auth/me`, {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${idToken}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));

        // Handle specific error cases
        if (response.status === 404) {
          throw new Error('Your account was not found. Please sign up.');
        }

        // Use backend error message if available, otherwise generic message
        throw new Error(errorData.message || 'Failed to get user data');
      }

      const result = await response.json();

      return {
        user: {
          ...this.mapFirebaseUser(user),
          role: result.user?.role,
        },
        tenant: result.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Signin error:', error);
      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Sign in with Google
   */
  async signinWithGoogle(): Promise<AuthResponse> {
    try {
      const googleResult = await signInWithPopup(auth, googleProvider);
      const user = googleResult.user;
      const idToken = await user.getIdToken();

      // Get user and tenant data from backend
      const response = await this.makeRequest(`${this.baseURL}/auth/me`, {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${idToken}`,
        },
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));

        // Handle specific error cases
        if (response.status === 404) {
          throw new Error('Your account was not found. Please sign up.');
        }

        // Use backend error message if available, otherwise generic message
        throw new Error(errorData.message || 'Failed to get user data');
      }

      const responseData = await response.json();

      return {
        user: {
          ...this.mapFirebaseUser(user),
          role: responseData.user?.role,
        },
        tenant: responseData.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Google signin error:', error);
      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Sign out
   */
  async signout(): Promise<void> {
    try {
      await signOut(auth);
    } catch (error) {
      console.error('Signout error:', error);
      throw error;
    }
  }

  /**
   * Get current user
   */
  getCurrentUser(): User | null {
    return auth.currentUser;
  }

  /**
   * Get current user's ID token
   */
  async getCurrentUserToken(): Promise<string | null> {
    const user = this.getCurrentUser();
    if (user) {
      return await user.getIdToken();
    }
    return null;
  }

  /**
   * Exchange magic link token for user data
   * Frontend should use signInWithCustomToken() first, then call this
   */
  async exchangeMagicToken(idToken: string): Promise<AuthResponse> {
    try {
      const response = await this.makeRequest(
        `${this.baseURL}/auth/exchange-magic-token`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            id_token: idToken,
          }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.message || 'Failed to exchange magic token');
      }

      const result = await response.json();

      return {
        user: {
          uid: result.user.id.toString(),
          email: result.user.email,
          displayName: result.user.name,
          emailVerified: result.user.email_verified,
        },
        tenant: result.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Magic token exchange error:', error);
      throw error;
    }
  }

  /**
   * Send password reset email
   */
  async sendPasswordResetEmail(email: string): Promise<void> {
    try {
      await sendPasswordResetEmail(auth, email);
    } catch (error) {
      console.error('Password reset error:', error);
      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Confirm password reset with action code and new password
   */
  async confirmPasswordReset(
    actionCode: string,
    newPassword: string
  ): Promise<void> {
    try {
      await confirmPasswordReset(auth, actionCode, newPassword);
    } catch (error) {
      console.error('Password reset confirmation error:', error);
      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Handle Firebase Auth errors and convert to user-friendly messages
   */
  private handleAuthError(error: AuthError): Error {
    switch (error.code) {
      // Existing errors with improved messages
      case 'auth/email-already-in-use':
        return new Error(
          'An account with this email already exists. Please try signing in instead.'
        );
      case 'auth/weak-password':
        return new Error('Password must be at least 8 characters long.');
      case 'auth/invalid-email':
        return new Error('Please enter a valid email address.');
      case 'auth/user-not-found':
        return new Error(
          'No account found with this email address. Please check your email or sign up for a new account.'
        );
      case 'auth/wrong-password':
        return new Error(
          'Incorrect password. Please try again or reset your password.'
        );
      case 'auth/too-many-requests':
        return new Error(
          'Too many failed attempts. Please wait a few minutes before trying again.'
        );
      case 'auth/popup-closed-by-user':
        return new Error(
          'Sign-in was cancelled. Please try again if you want to continue.'
        );
      case 'auth/popup-blocked':
        return new Error(
          'Popup was blocked by your browser. Please allow popups for this site and try again.'
        );
      case 'auth/network-request-failed':
        return new Error(
          'Network error. Please check your internet connection and try again.'
        );

      // New error codes
      case 'auth/invalid-credential':
        return new Error(
          'The provided credentials are invalid. Please check your email and password.'
        );
      case 'auth/user-disabled':
        return new Error(
          'Your account has been disabled. Please contact support for assistance.'
        );
      case 'auth/email-already-exists':
        return new Error(
          'An account with this email already exists. Please use a different email address.'
        );
      case 'auth/operation-not-allowed':
        return new Error(
          'This sign-in method is currently disabled. Please try a different method.'
        );

      // Additional common errors
      case 'auth/id-token-expired':
        return new Error('Your session has expired. Please sign in again.');
      case 'auth/id-token-revoked':
        return new Error(
          'Your session has been revoked. Please sign in again.'
        );
      case 'auth/invalid-argument':
        return new Error(
          'Invalid information provided. Please check your input and try again.'
        );
      case 'auth/invalid-password':
        return new Error('Password must be at least 8 characters long.');
      case 'auth/invalid-phone-number':
        return new Error('Please enter a valid phone number.');
      case 'auth/phone-number-already-exists':
        return new Error(
          'This phone number is already associated with an account.'
        );
      case 'auth/requires-recent-login':
        return new Error(
          'For security reasons, please sign in again to complete this action.'
        );
      case 'auth/credential-already-in-use':
        return new Error(
          'This credential is already associated with a different account.'
        );
      case 'auth/quota-exceeded':
        return new Error(
          'Service temporarily unavailable. Please try again later.'
        );
      case 'auth/captcha-check-failed':
        return new Error('Security verification failed. Please try again.');
      case 'auth/invalid-user-token':
        return new Error('Your session is invalid. Please sign in again.');
      case 'auth/user-token-expired':
        return new Error('Your session has expired. Please sign in again.');
      case 'auth/null-user':
        return new Error('No user is currently signed in.');
      case 'auth/app-deleted':
        return new Error('This application has been deleted.');
      case 'auth/keychain-error':
        return new Error('Keychain access error. Please try again.');
      case 'auth/internal-error':
        return new Error('An internal error occurred. Please try again later.');

      // Password reset specific errors
      case 'auth/invalid-action-code':
        return new Error(
          'The password reset link is invalid or has expired. Please request a new one.'
        );
      case 'auth/expired-action-code':
        return new Error(
          'The password reset link has expired. Please request a new one.'
        );

      default:
        return new Error(
          error.message || 'An unexpected error occurred. Please try again.'
        );
    }
  }
}

export const authService = new AuthService();
