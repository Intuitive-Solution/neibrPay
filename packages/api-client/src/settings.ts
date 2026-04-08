import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { computed, type Ref } from 'vue';
import { apiClient, fileUploadClient } from './apiClient';
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
      reminders?: {
        invoice_due?: {
          enabled?: boolean;
          pre_due_offsets_days?: number[];
          post_due_interval_days?: number;
          post_due_max_reminders?: number | null;
          post_due_stop_after_days?: number | null;
        };
        events?: {
          enabled?: boolean;
        };
      };
      currency: string;
      currency_format: string;
      timezone: string;
      date_format: string;
      first_month_of_year: string;
      stripe_connect_id: string | null;
      stripe_connect_status: 'not_connected' | 'pending' | 'active';
      charges_enabled: boolean;
      details_submitted: boolean;
      zelle_enabled?: boolean;
      zelle_email?: string | null;
      zelle_phone?: string | null;
      has_zelle_qr?: boolean;
      zelle_instructions?: string | null;
      has_logo?: boolean;
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

export interface UpdateZelleSettingsRequest {
  zelle_enabled?: boolean;
  zelle_email?: string | null;
  zelle_phone?: string | null;
  zelle_instructions?: string | null;
}

export interface UpdateReminderSettingsRequest {
  invoice_due?: {
    enabled?: boolean;
    pre_due_offsets_days?: number[];
    post_due_interval_days?: number;
    post_due_max_reminders?: number | null;
    post_due_stop_after_days?: number | null;
  };
  events?: {
    enabled?: boolean;
  };
}

export interface UploadZelleQrResponse {
  message: string;
  zelle_qr_path: string;
  zelle_qr_url: string | null;
}

export interface UploadHoaLogoResponse {
  message: string;
  logo_path: string;
  logo_url: string | null;
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

  /**
   * Update Zelle payment settings
   */
  async updateZelleSettings(
    data: UpdateZelleSettingsRequest
  ): Promise<{ message: string; settings: Record<string, unknown> }> {
    const response = await apiClient.put<{
      message: string;
      settings: Record<string, unknown>;
    }>('/tenant/zelle', data);
    return response.data;
  },

  /**
   * Update reminder settings
   */
  async updateReminderSettings(
    data: UpdateReminderSettingsRequest
  ): Promise<{ message: string; settings: Record<string, unknown> }> {
    const response = await apiClient.put<{
      message: string;
      settings: Record<string, unknown>;
    }>('/tenant/reminders', data);
    return response.data;
  },

  /**
   * Upload Zelle QR code image
   */
  async uploadZelleQr(file: File): Promise<UploadZelleQrResponse> {
    const formData = new FormData();
    formData.append('file', file);
    const response = await fileUploadClient.post<UploadZelleQrResponse>(
      '/tenant/zelle-qr',
      formData
    );
    return response.data;
  },

  /**
   * Remove Zelle QR code image
   */
  async removeZelleQr(): Promise<{ message: string }> {
    const response = await apiClient.delete<{ message: string }>(
      '/tenant/zelle-qr'
    );
    return response.data;
  },

  /**
   * Upload HOA/community logo
   */
  async uploadHoaLogo(file: File): Promise<UploadHoaLogoResponse> {
    const formData = new FormData();
    formData.append('file', file);
    const response = await fileUploadClient.post<UploadHoaLogoResponse>(
      '/tenant/hoa-logo',
      formData
    );
    return response.data;
  },

  /**
   * Remove HOA/community logo
   */
  async removeHoaLogo(): Promise<{ message: string }> {
    const response = await apiClient.delete<{ message: string }>(
      '/tenant/hoa-logo'
    );
    return response.data;
  },

  /**
   * Get a short-lived signed URL for the tenant HOA logo (same pattern as Invoice PDF).
   */
  async getHoaLogoUrl(): Promise<{ file_url: string }> {
    const response = await apiClient.get<{ data: { file_url: string } }>(
      '/tenant/hoa-logo/url'
    );
    return response.data.data;
  },

  /**
   * Get a short-lived signed URL for the tenant Zelle QR image (same pattern as Invoice PDF).
   */
  async getZelleQrUrl(): Promise<{ file_url: string }> {
    const response = await apiClient.get<{ data: { file_url: string } }>(
      '/tenant/zelle-qr/url'
    );
    return response.data.data;
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

/**
 * TanStack Query mutation to update Zelle settings
 */
export function useUpdateZelleSettings() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateZelleSettingsRequest) =>
      settingsApi.updateZelleSettings(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
    },
  });
}

/**
 * TanStack Query mutation to update reminder settings
 */
export function useUpdateReminderSettings() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateReminderSettingsRequest) =>
      settingsApi.updateReminderSettings(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
    },
  });
}

/**
 * TanStack Query mutation to remove Zelle QR code
 */
export function useRemoveZelleQr() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => settingsApi.removeZelleQr(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
      queryClient.invalidateQueries({ queryKey: settingsKeys.zelleQrUrl() });
    },
  });
}

/**
 * TanStack Query mutation to upload HOA logo
 */
export function useUploadHoaLogo() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (file: File) => settingsApi.uploadHoaLogo(file),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
      queryClient.invalidateQueries({ queryKey: settingsKeys.hoaLogoUrl() });
    },
  });
}

/**
 * TanStack Query mutation to remove HOA logo
 */
export function useRemoveHoaLogo() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: () => settingsApi.removeHoaLogo(),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: settingsKeys.detail() });
      queryClient.invalidateQueries({ queryKey: settingsKeys.hoaLogoUrl() });
    },
  });
}

/** Stale time for tenant asset signed URLs (4 min; backend expiry is 6 min) */
const TENANT_ASSET_URL_STALE_MS = 4 * 60 * 1000;

/**
 * Fetch short-lived signed URL for tenant HOA logo (same pattern as Invoice PDF).
 * Enabled only when settings indicate has_logo. Refetches before expiry.
 */
export function useTenantLogoUrl(settingsData: Ref<SettingsData | undefined>) {
  const query = useQuery({
    queryKey: settingsKeys.hoaLogoUrl(),
    queryFn: () => settingsApi.getHoaLogoUrl(),
    enabled: computed(
      () => settingsData.value?.tenant?.settings?.has_logo === true
    ),
    staleTime: TENANT_ASSET_URL_STALE_MS,
  });
  return {
    logoUrl: computed(() => query.data.value?.file_url ?? null),
    isLoadingLogo: query.isLoading,
    refetchLogo: query.refetch,
  };
}

/**
 * Fetch short-lived signed URL for tenant Zelle QR image (same pattern as Invoice PDF).
 * Enabled only when settings indicate has_zelle_qr. Refetches before expiry.
 */
export function useTenantZelleQrUrl(
  settingsData: Ref<SettingsData | undefined>
) {
  const query = useQuery({
    queryKey: settingsKeys.zelleQrUrl(),
    queryFn: () => settingsApi.getZelleQrUrl(),
    enabled: computed(
      () => settingsData.value?.tenant?.settings?.has_zelle_qr === true
    ),
    staleTime: TENANT_ASSET_URL_STALE_MS,
  });
  return {
    zelleQrUrl: computed(() => query.data.value?.file_url ?? null),
    isLoadingZelleQr: query.isLoading,
    refetchZelleQr: query.refetch,
  };
}
