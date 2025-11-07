import { apiClient } from './apiClient';
import type {
  CreateAnnouncementDto,
  UpdateAnnouncementDto,
  AnnouncementFilters,
  AnnouncementListResponse,
  AnnouncementResponse,
  UserAnnouncementsResponse,
} from '@neibrpay/models';

export const announcementsApi = {
  /**
   * Get all announcements with optional filters (admin only)
   */
  list: async (
    filters?: AnnouncementFilters
  ): Promise<AnnouncementListResponse> => {
    const params = new URLSearchParams();

    if (filters?.status && filters.status !== 'all') {
      params.append('status', filters.status);
    }

    if (filters?.include_deleted) {
      params.append('include_deleted', 'true');
    }

    const queryString = params.toString();
    const url = queryString
      ? `/announcements?${queryString}`
      : '/announcements';

    const response = await apiClient.get(url);
    return response.data;
  },

  /**
   * Get announcements for the current user (all authenticated users)
   */
  forUser: async (): Promise<UserAnnouncementsResponse> => {
    const response = await apiClient.get('/announcements/for-user');
    return response.data;
  },

  /**
   * Create a new announcement (admin only)
   */
  create: async (
    data: CreateAnnouncementDto
  ): Promise<AnnouncementResponse> => {
    const response = await apiClient.post('/announcements', data);
    return response.data;
  },

  /**
   * Get a single announcement by ID (admin only)
   */
  get: async (id: number): Promise<AnnouncementResponse> => {
    const response = await apiClient.get(`/announcements/${id}`);
    return response.data;
  },

  /**
   * Update an announcement (admin only, creator only)
   */
  update: async (
    id: number,
    data: UpdateAnnouncementDto
  ): Promise<AnnouncementResponse> => {
    const response = await apiClient.put(`/announcements/${id}`, data);
    return response.data;
  },

  /**
   * Delete an announcement (admin only, soft delete)
   */
  delete: async (id: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(`/announcements/${id}`);
    return response.data;
  },
};
