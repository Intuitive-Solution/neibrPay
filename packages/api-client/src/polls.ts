import { apiClient } from './apiClient';
import type {
  CastVoteDto,
  CreatePollDto,
  ResidentPoll,
  ResidentPollListResponse,
  Poll,
  PollFilters,
  PollListResponse,
  PollResponsePayload,
  PollResults,
  UpdatePollDto,
} from '@neibrpay/models';

export const pollsApi = {
  /**
   * Get all polls for the tenant (admin only)
   */
  list: async (filters?: PollFilters): Promise<PollListResponse> => {
    const params = new URLSearchParams();

    if (filters?.status && filters.status !== 'all') {
      params.append('status', filters.status);
    }

    const queryString = params.toString();
    const url = queryString ? `/polls?${queryString}` : '/polls';

    const response = await apiClient.get(url);
    return response.data;
  },

  /**
   * Get a single poll with tallies and participation roster (admin only)
   */
  get: async (id: number): Promise<Poll> => {
    const response = await apiClient.get(`/polls/${id}`);
    return response.data.data;
  },

  /**
   * Create a poll (admin only)
   */
  create: async (data: CreatePollDto): Promise<PollResponsePayload> => {
    const response = await apiClient.post('/polls', data);
    return response.data;
  },

  /**
   * Update a poll (admin only)
   */
  update: async (
    id: number,
    data: UpdatePollDto
  ): Promise<PollResponsePayload> => {
    const response = await apiClient.put(`/polls/${id}`, data);
    return response.data;
  },

  /**
   * Publish a draft poll (admin only)
   */
  publish: async (id: number): Promise<PollResponsePayload> => {
    const response = await apiClient.post(`/polls/${id}/publish`);
    return response.data;
  },

  /**
   * Close an open poll early (admin only)
   */
  close: async (id: number): Promise<PollResponsePayload> => {
    const response = await apiClient.post(`/polls/${id}/close`);
    return response.data;
  },

  /**
   * Nudge the units that have not voted yet (admin only)
   */
  remind: async (
    id: number
  ): Promise<{ message: string; meta: { reminded_unit_count: number } }> => {
    const response = await apiClient.post(`/polls/${id}/remind`);
    return response.data;
  },

  /**
   * Soft delete a poll (admin only)
   */
  delete: async (id: number): Promise<{ message: string }> => {
    const response = await apiClient.delete(`/polls/${id}`);
    return response.data;
  },

  /**
   * Get polls the current user's unit(s) are targeted by
   */
  forUser: async (): Promise<ResidentPollListResponse> => {
    const response = await apiClient.get('/polls/for-user');
    return response.data;
  },

  /**
   * Cast the caller's unit vote
   */
  vote: async (
    id: number,
    data: CastVoteDto
  ): Promise<{ data: ResidentPoll; message: string }> => {
    const response = await apiClient.post(`/polls/${id}/vote`, data);
    return response.data;
  },

  /**
   * Get the tally, if results visibility allows it for the caller
   */
  results: async (id: number): Promise<PollResults> => {
    const response = await apiClient.get(`/polls/${id}/results`);
    return response.data.data;
  },
};
