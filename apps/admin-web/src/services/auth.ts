import { apiClient } from '@neibrpay/api-client';

export interface CheckEmailResponse {
  exists: boolean;
  requires_signup: boolean;
  tenant_name?: string;
}

export interface SignupData {
  email: string;
  fullName: string;
  phoneNumber: string;
  communityName: string;
}

export interface GoogleSignupData {
  phoneNumber: string;
  communityName: string;
}

export interface AuthUser {
  id: number;
  name: string;
  email: string;
  role: string;
  phone_number?: string;
  avatar_url?: string;
  email_verified: boolean;
  is_active: boolean;
  last_login_at?: string;
}

export interface TenantData {
  id: number;
  name: string;
  slug: string;
  trial_ends_at?: string;
  subscription_ends_at?: string;
  is_active: boolean;
  can_access: boolean;
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

      if (error instanceof TypeError && error.message.includes('fetch')) {
        throw new Error(
          'Network error. Please check your internet connection and try again.'
        );
      }

      throw error;
    }
  }

  /**
   * Check if email exists in the system
   */
  async checkEmail(email: string): Promise<CheckEmailResponse> {
    try {
      const response = await this.makeRequest(
        `${this.baseURL}/auth/check-email`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ email }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.error || 'Failed to check email');
      }

      return await response.json();
    } catch (error) {
      console.error('Check email error:', error);
      throw error;
    }
  }

  /**
   * Send verification code to email
   */
  async sendVerificationCode(email: string): Promise<void> {
    try {
      const response = await this.makeRequest(
        `${this.baseURL}/auth/send-code`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ email }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.error || 'Failed to send verification code');
      }
    } catch (error) {
      console.error('Send verification code error:', error);
      throw error;
    }
  }

  /**
   * Verify code and get verification token
   */
  async verifyCode(email: string, code: string): Promise<string> {
    try {
      const response = await this.makeRequest(
        `${this.baseURL}/auth/verify-code`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({ email, code }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.error || 'Invalid verification code');
      }

      const result = await response.json();
      if (!result.valid || !result.verification_token) {
        throw new Error('Invalid verification code');
      }

      return result.verification_token;
    } catch (error) {
      console.error('Verify code error:', error);
      throw error;
    }
  }

  /**
   * Signup new user with verification code
   */
  async signup(
    data: SignupData,
    verificationToken: string
  ): Promise<AuthResponse> {
    try {
      const response = await this.makeRequest(`${this.baseURL}/auth/signup`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email: data.email,
          verification_token: verificationToken,
          full_name: data.fullName,
          phone_number: data.phoneNumber,
          community_name: data.communityName,
        }),
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.error || 'Failed to create account');
      }

      const result = await response.json();
      return {
        user: result.user,
        tenant: result.tenant,
        token: result.token,
      };
    } catch (error) {
      console.error('Signup error:', error);
      throw error;
    }
  }

  /**
   * Login existing user with verification code
   */
  async login(email: string, verificationToken: string): Promise<AuthResponse> {
    try {
      const response = await this.makeRequest(`${this.baseURL}/auth/login`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          email,
          verification_token: verificationToken,
        }),
      });

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.error || 'Login failed');
      }

      const result = await response.json();
      return {
        user: result.user,
        tenant: result.tenant,
        token: result.token,
      };
    } catch (error) {
      console.error('Login error:', error);
      throw error;
    }
  }

  /**
   * Initiate Google OAuth flow
   * This redirects the browser to Google OAuth
   */
  async initiateGoogleAuth(): Promise<void> {
    try {
      // Redirect browser to backend Google OAuth endpoint
      // The backend will redirect to Google, then Google redirects back to backend callback
      window.location.href = `${this.baseURL}/auth/google/redirect`;
    } catch (error) {
      console.error('Initiate Google auth error:', error);
      throw error;
    }
  }

  /**
   * Signup new user with Google OAuth
   */
  async googleSignup(
    googleToken: string,
    data: GoogleSignupData
  ): Promise<AuthResponse> {
    try {
      const response = await this.makeRequest(
        `${this.baseURL}/auth/google/signup`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            google_token: googleToken,
            phone_number: data.phoneNumber,
            community_name: data.communityName,
          }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(
          errorData.error || 'Failed to create account with Google'
        );
      }

      const result = await response.json();
      return {
        user: result.user,
        tenant: result.tenant,
        token: result.token,
      };
    } catch (error) {
      console.error('Google signup error:', error);
      throw error;
    }
  }

  /**
   * Authenticate with magic link token
   */
  async magicLinkAuth(token: string): Promise<AuthResponse> {
    try {
      const response = await this.makeRequest(
        `${this.baseURL}/auth/magic-link`,
        {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            token,
          }),
        }
      );

      if (!response.ok) {
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.error || 'Magic link authentication failed');
      }

      const result = await response.json();
      return {
        user: result.user,
        tenant: result.tenant,
        token: result.token,
      };
    } catch (error) {
      console.error('Magic link auth error:', error);
      throw error;
    }
  }

  /**
   * Get current authenticated user
   */
  async getCurrentUser(): Promise<{ user: AuthUser; tenant: TenantData }> {
    try {
      const token = this.getToken();
      if (!token) {
        throw new Error('No authentication token found');
      }

      const response = await this.makeRequest(`${this.baseURL}/auth/me`, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        if (response.status === 401) {
          this.clearToken();
          throw new Error('Authentication expired. Please sign in again.');
        }
        const errorData = await response.json().catch(() => ({}));
        throw new Error(errorData.error || 'Failed to get user information');
      }

      return await response.json();
    } catch (error) {
      console.error('Get current user error:', error);
      throw error;
    }
  }

  /**
   * Logout user
   */
  async logout(): Promise<void> {
    try {
      const token = this.getToken();
      if (token) {
        await this.makeRequest(`${this.baseURL}/auth/logout`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${token}`,
          },
        });
      }
    } catch (error) {
      console.error('Logout error:', error);
      // Continue with logout even if request fails
    } finally {
      this.clearToken();
    }
  }

  /**
   * Set authentication token
   */
  setToken(token: string): void {
    localStorage.setItem('auth_token', token);
  }

  /**
   * Get authentication token
   */
  getToken(): string | null {
    return localStorage.getItem('auth_token');
  }

  /**
   * Clear authentication token
   */
  clearToken(): void {
    localStorage.removeItem('auth_token');
  }
}

export const authService = new AuthService();
