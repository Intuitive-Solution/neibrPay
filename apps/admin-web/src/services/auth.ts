import {
  createUserWithEmailAndPassword,
  signInWithEmailAndPassword,
  signInWithPopup,
  signOut,
  sendEmailVerification,
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
      const response = await fetch(`${this.baseURL}/auth/signup`, {
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
        user: this.mapFirebaseUser(user),
        tenant: result.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Signup error:', error);
      throw this.handleAuthError(error as AuthError);
    }
  }

  /**
   * Sign up with Google
   */
  async signupWithGoogle(data: GoogleSignupData): Promise<AuthResponse> {
    try {
      // Sign in with Google popup
      const googleResult = await signInWithPopup(auth, googleProvider);
      const user = googleResult.user;

      // Get ID token
      const idToken = await user.getIdToken();

      // Create tenant and user in backend
      const response = await fetch(`${this.baseURL}/auth/google-signup`, {
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
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(
          errorData.message || 'Failed to create account with Google'
        );
      }

      const responseData = await response.json();

      return {
        user: this.mapFirebaseUser(user),
        tenant: responseData.tenant,
        token: idToken,
      };
    } catch (error) {
      console.error('Google signup error:', error);
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
      const response = await fetch(`${this.baseURL}/auth/me`, {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${idToken}`,
        },
      });

      if (!response.ok) {
        throw new Error('Failed to get user data');
      }

      const result = await response.json();

      return {
        user: this.mapFirebaseUser(user),
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
      const response = await fetch(`${this.baseURL}/auth/me`, {
        method: 'GET',
        headers: {
          Authorization: `Bearer ${idToken}`,
        },
      });

      if (!response.ok) {
        throw new Error('Failed to get user data');
      }

      const responseData = await response.json();

      return {
        user: this.mapFirebaseUser(user),
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
   * Handle Firebase Auth errors and convert to user-friendly messages
   */
  private handleAuthError(error: AuthError): Error {
    switch (error.code) {
      case 'auth/email-already-in-use':
        return new Error('An account with this email already exists');
      case 'auth/weak-password':
        return new Error('Password should be at least 6 characters');
      case 'auth/invalid-email':
        return new Error('Invalid email address');
      case 'auth/user-not-found':
        return new Error('No account found with this email');
      case 'auth/wrong-password':
        return new Error('Incorrect password');
      case 'auth/too-many-requests':
        return new Error('Too many failed attempts. Please try again later');
      case 'auth/popup-closed-by-user':
        return new Error('Sign-in was cancelled');
      case 'auth/popup-blocked':
        return new Error(
          'Popup was blocked by browser. Please allow popups and try again'
        );
      case 'auth/network-request-failed':
        return new Error('Network error. Please check your connection');
      default:
        return new Error(error.message || 'An unexpected error occurred');
    }
  }
}

export const authService = new AuthService();
