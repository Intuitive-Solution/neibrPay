import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { announcementsApi, announcementKeys } from '@neibrpay/api-client';
import { computed, unref, type Ref, type ComputedRef } from 'vue';
import type {
  Announcement,
  CreateAnnouncementDto,
  UpdateAnnouncementDto,
  AnnouncementFilters,
  ApiError,
} from '@neibrpay/models';

// Query hooks
export function useAnnouncements(
  filters?: Ref<AnnouncementFilters> | AnnouncementFilters
) {
  const filtersRef = computed(() =>
    typeof filters === 'object' && filters !== null && !('value' in filters)
      ? filters
      : (filters as Ref<AnnouncementFilters> | undefined)?.value || {}
  );

  return useQuery({
    queryKey: computed(() => announcementKeys.list(filtersRef.value)),
    queryFn: () => announcementsApi.list(filtersRef.value),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

export function useUserAnnouncements() {
  return useQuery({
    queryKey: announcementKeys.forUser(),
    queryFn: () => announcementsApi.forUser(),
    staleTime: 2 * 60 * 1000, // 2 minutes - shorter for user-facing data
    refetchOnWindowFocus: true,
  });
}

export function useAnnouncement(
  id: Ref<number | null> | ComputedRef<number | null> | number | null
) {
  return useQuery({
    queryKey: computed(() => {
      const idValue = unref(id);
      return idValue
        ? announcementKeys.detail(idValue)
        : ['announcements', 'detail', null];
    }),
    queryFn: () => {
      const idValue = unref(id);
      if (!idValue) {
        throw new Error('Announcement ID is required');
      }
      return announcementsApi.get(idValue);
    },
    enabled: computed(() => !!unref(id)),
  });
}

// Mutation hooks
export function useCreateAnnouncement() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateAnnouncementDto) => announcementsApi.create(data),
    onSuccess: () => {
      // Invalidate both admin list and user announcements
      queryClient.invalidateQueries({ queryKey: announcementKeys.lists() });
      queryClient.invalidateQueries({ queryKey: announcementKeys.forUser() });
    },
  });
}

export function useUpdateAnnouncement() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateAnnouncementDto }) =>
      announcementsApi.update(id, data),
    onSuccess: (data, variables) => {
      // Invalidate queries
      queryClient.invalidateQueries({ queryKey: announcementKeys.lists() });
      queryClient.invalidateQueries({
        queryKey: announcementKeys.detail(variables.id),
      });
      queryClient.invalidateQueries({ queryKey: announcementKeys.forUser() });
    },
  });
}

export function useDeleteAnnouncement() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => announcementsApi.delete(id),
    onSuccess: () => {
      // Invalidate queries
      queryClient.invalidateQueries({ queryKey: announcementKeys.lists() });
      queryClient.invalidateQueries({ queryKey: announcementKeys.forUser() });
    },
  });
}
