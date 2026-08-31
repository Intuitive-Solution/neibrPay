import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { pollsApi, pollKeys } from '@neibrpay/api-client';
import { computed, unref, type Ref, type ComputedRef } from 'vue';
import type {
  CastVoteDto,
  CreatePollDto,
  PollFilters,
  UpdatePollDto,
} from '@neibrpay/models';

// Query hooks
export function usePolls(
  filters?: Ref<PollFilters> | PollFilters,
  options?: { enabled?: Ref<boolean> | ComputedRef<boolean> | boolean }
) {
  const filtersRef = computed(() =>
    typeof filters === 'object' && filters !== null && !('value' in filters)
      ? filters
      : (filters as Ref<PollFilters> | undefined)?.value || {}
  );

  return useQuery({
    queryKey: computed(() => pollKeys.list(filtersRef.value)),
    queryFn: () => pollsApi.list(filtersRef.value),
    staleTime: 60 * 1000, // 1 minute - tallies move while a poll is open
    enabled: computed(() =>
      options?.enabled === undefined ? true : !!unref(options.enabled)
    ),
  });
}

export function usePoll(
  id: Ref<number | null> | ComputedRef<number | null> | number | null
) {
  return useQuery({
    queryKey: computed(() => {
      const idValue = unref(id);
      return idValue ? pollKeys.detail(idValue) : ['polls', 'detail', null];
    }),
    queryFn: () => {
      const idValue = unref(id);
      if (!idValue) {
        throw new Error('Poll ID is required');
      }
      return pollsApi.get(idValue);
    },
    enabled: computed(() => !!unref(id)),
    staleTime: 30 * 1000,
  });
}

export function useUserPolls() {
  return useQuery({
    queryKey: pollKeys.forUser(),
    queryFn: () => pollsApi.forUser(),
    staleTime: 2 * 60 * 1000,
    refetchOnWindowFocus: true,
  });
}

// Mutation hooks
export function useCreatePoll() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreatePollDto) => pollsApi.create(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: pollKeys.lists() });
      queryClient.invalidateQueries({ queryKey: pollKeys.forUser() });
    },
  });
}

export function useUpdatePoll() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdatePollDto }) =>
      pollsApi.update(id, data),
    onSuccess: (_data, variables) => {
      queryClient.invalidateQueries({ queryKey: pollKeys.lists() });
      queryClient.invalidateQueries({
        queryKey: pollKeys.detail(variables.id),
      });
      queryClient.invalidateQueries({ queryKey: pollKeys.forUser() });
    },
  });
}

export function usePublishPoll() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => pollsApi.publish(id),
    onSuccess: (_data, id) => {
      queryClient.invalidateQueries({ queryKey: pollKeys.lists() });
      queryClient.invalidateQueries({ queryKey: pollKeys.detail(id) });
      queryClient.invalidateQueries({ queryKey: pollKeys.forUser() });
    },
  });
}

export function useClosePoll() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => pollsApi.close(id),
    onSuccess: (_data, id) => {
      queryClient.invalidateQueries({ queryKey: pollKeys.lists() });
      queryClient.invalidateQueries({ queryKey: pollKeys.detail(id) });
      queryClient.invalidateQueries({ queryKey: pollKeys.forUser() });
    },
  });
}

export function useRemindPollNonVoters() {
  return useMutation({
    mutationFn: (id: number) => pollsApi.remind(id),
  });
}

export function useDeletePoll() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => pollsApi.delete(id),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: pollKeys.lists() });
      queryClient.invalidateQueries({ queryKey: pollKeys.forUser() });
    },
  });
}

export function useCastVote() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: CastVoteDto }) =>
      pollsApi.vote(id, data),
    onSuccess: (_data, variables) => {
      queryClient.invalidateQueries({ queryKey: pollKeys.forUser() });
      queryClient.invalidateQueries({
        queryKey: pollKeys.detail(variables.id),
      });
    },
  });
}
