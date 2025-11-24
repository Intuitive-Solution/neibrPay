import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { apiClient } from './apiClient';
import { settingsKeys } from './queryKeys';

export interface SettingsData {
  tenant: {
    id: number;
    name: string;
    address: string;
    city: string;
    state: string;
    zip_code: string;
    phone: string;
    email: string;
    settings: {
      currency: string;
      currency_format: string;
      timezone: string;
      date_format: string;
      first_month_of_year: string;
      stripe_connect_id: string | null;
      stripe_connect_status: 'not_connected' | 'pending' | 'active';
      charges_enabled: boolean;
      details_submitted: boolean;
    };
  };
  user: {
    id: number;
    name: string;
    email: string;
    phone_number: string;
  };
}

export interface UpdateTenantSettingsRequest {
  name?: string;
  address?: string;
  phone?: string;
}

export interface UpdateUserProfileRequest {
  name?: string;
  email?: string;
  phone_number?: string;
}

export interface UpdatePasswordRequest {
  current_password: string;
  new_password: string;
  new_password_confirmation: string;
}

export interface UpdateLocalizationRequest {
  currency?: string;
  currency_format?: string;
  timezone?: string;
  date_format?: string;
  first_month_of_year?: string;
}

export interface SettingsResponse {
  tenant: SettingsData['tenant'];
  user: SettingsData['user'];
}

export interface TenantUpdateResponse {
  message: string;
  tenant: {
    id: number;
    name: string;
    address: string;
    phone: string;
  };
}

export interface UserProfileUpdateResponse {
  message: string;
  user: {
    id: number;
    name: string;
    email: string;
    phone_number: string;
  };
}

export interface LocalizationUpdateResponse {
  message: string;
  settings: {
    currency: string;
    currency_format: string;
    timezone: string;
    date_format: string;
    first_month_of_year: string;
  };
}

export const settingsApi = {
  /**
   * Get all settings (tenant + user + localization)
   */
  async getSettings(): Promise<SettingsData> {
    const response = await apiClient.get<SettingsResponse>('/settings');
    return response.data;
  },

  /**
   * Update tenant/community settings
   */
  async updateTenantSettings(
    data: UpdateTenantSettingsRequest
  ): Promise<TenantUpdateResponse> {
    const response = await apiClient.put<TenantUpdateResponse>('/tenant', data);
    return response.data;
  },

  /**
   * Update user profile
   */
  async updateUserProfile(
    data: UpdateUserProfileRequest
  ): Promise<UserProfileUpdateResponse> {
    const response = await apiClient.put<UserProfileUpdateResponse>(
      '/auth/user/profile',
      data
    );
    return response.data;
  },

  /**
   * Change password
   */
  async updatePassword(
    data: UpdatePasswordRequest
  ): Promise<{ message: string }> {
    const response = await apiClient.put<{ message: string }>(
      '/auth/user/password',
      data
    );
    return response.data;
  },

  /**
   * Update localization settings
   */
  async updateLocalization(
    data: UpdateLocalizationRequest
  ): Promise<LocalizationUpdateResponse> {
    const response = await apiClient.put<LocalizationUpdateResponse>(
      '/tenant/localization',
      data
    );
    return response.data;
  },
};

/**
 * TanStack Query hook to fetch settings
 */
export function useSettings() {
  return useQuery({
    queryKey: settingsKeys.detail(),
    queryFn: () => settingsApi.getSettings(),
  });
}

/**
 * TanStack Query mutation to update tenant settings
 */
export function useUpdateTenantSettings() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateTenantSettingsRequest) =>
      settingsApi.updateTenantSettings(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
    },
  });
}

/**
 * TanStack Query mutation to update user profile
 */
export function useUpdateUserProfile() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateUserProfileRequest) =>
      settingsApi.updateUserProfile(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
    },
  });
}

/**
 * TanStack Query mutation to update password
 */
export function useUpdatePassword() {
  return useMutation({
    mutationFn: (data: UpdatePasswordRequest) =>
      settingsApi.updatePassword(data),
  });
}

/**
 * TanStack Query mutation to update localization settings
 */
export function useUpdateLocalization() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateLocalizationRequest) =>
      settingsApi.updateLocalization(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
    },
  });
}
