import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { residentsApi, residentKeys } from '@neibrpay/api-client';
import { computed, type Ref } from 'vue';
import type {
  Resident,
  CreateResidentRequest,
  UpdateResidentRequest,
  ApiError,
} from '@neibrpay/models';

// Query hooks
export function useResidents(includeDeleted: Ref<boolean> | boolean = false) {
  const includeDeletedRef = computed(() =>
    typeof includeDeleted === 'boolean' ? includeDeleted : includeDeleted.value
  );

  return useQuery({
    queryKey: computed(() =>
      residentKeys.list({ includeDeleted: includeDeletedRef.value })
    ),
    queryFn: () => residentsApi.getResidents(includeDeletedRef.value),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

export function useResident(id: number) {
  return useQuery({
    queryKey: residentKeys.detail(id),
    queryFn: () => residentsApi.getResident(id),
    enabled: !!id,
  });
}

export function useResidentUnits(id: number) {
  return useQuery({
    queryKey: ['residents', id, 'units'],
    queryFn: () => residentsApi.getResidentUnits(id),
    enabled: !!id,
    staleTime: 30 * 1000, // 30 seconds - shorter stale time for more frequent updates
    refetchOnWindowFocus: true, // Refetch when window regains focus
  });
}

export function useAvailableUnitsForResident(id: number) {
  return useQuery({
    queryKey: ['residents', id, 'available-units'],
    queryFn: () => residentsApi.getAvailableUnitsForResident(id),
    enabled: !!id,
    staleTime: 30 * 1000, // 30 seconds - shorter stale time for more frequent updates
    refetchOnWindowFocus: true, // Refetch when window regains focus
  });
}

// Mutation hooks with optimistic updates
export function useCreateResident() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateResidentRequest) =>
      residentsApi.createResident(data),
    onMutate: async newResident => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: residentKeys.lists() });

      // Snapshot the previous value
      const previousResidents = queryClient.getQueryData<Resident[]>(
        residentKeys.list()
      );

      // Optimistically update to the new value
      const optimisticResident: Resident = {
        id: Date.now(), // Temporary ID
        name: newResident.name,
        email: newResident.email,
        phone: newResident.phone,
        role: 'resident',
        tenant_id: 1, // Will be set by backend
        is_active: true,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      };

      queryClient.setQueryData<Resident[]>(residentKeys.list(), old =>
        old ? [optimisticResident, ...old] : [optimisticResident]
      );

      // Return a context object with the snapshotted value
      return { previousResidents };
    },
    onError: (err: ApiError, newResident, context) => {
      // If the mutation fails, use the context returned from onMutate to roll back
      if (context?.previousResidents) {
        queryClient.setQueryData(
          residentKeys.list(),
          context.previousResidents
        );
      }
    },
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: residentKeys.lists() });
    },
  });
}

export function useUpdateResident() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateResidentRequest }) =>
      residentsApi.updateResident(id, data),
    onMutate: async ({ id, data }) => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: residentKeys.lists() });
      await queryClient.cancelQueries({ queryKey: residentKeys.detail(id) });

      // Snapshot the previous values
      const previousResidents = queryClient.getQueryData<Resident[]>(
        residentKeys.list()
      );
      const previousResident = queryClient.getQueryData<Resident>(
        residentKeys.detail(id)
      );

      // Optimistically update the resident in the list
      queryClient.setQueryData<Resident[]>(residentKeys.list(), old =>
        old?.map(resident =>
          resident.id === id
            ? { ...resident, ...data, updated_at: new Date().toISOString() }
            : resident
        )
      );

      // Optimistically update the individual resident
      queryClient.setQueryData<Resident>(residentKeys.detail(id), old =>
        old ? { ...old, ...data, updated_at: new Date().toISOString() } : old
      );

      return { previousResidents, previousResident };
    },
    onError: (err: ApiError, { id }, context) => {
      // Roll back on error
      if (context?.previousResidents) {
        queryClient.setQueryData(
          residentKeys.list(),
          context.previousResidents
        );
      }
      if (context?.previousResident) {
        queryClient.setQueryData(
          residentKeys.detail(id),
          context.previousResident
        );
      }
    },
    onSettled: (data, error, { id }) => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: residentKeys.lists() });
      queryClient.invalidateQueries({ queryKey: residentKeys.detail(id) });
    },
  });
}

export function useDeleteResident() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => residentsApi.deleteResident(id),
    onMutate: async id => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: residentKeys.lists() });

      // Snapshot the previous value
      const previousResidents = queryClient.getQueryData<Resident[]>(
        residentKeys.list()
      );

      // Optimistically remove the resident
      queryClient.setQueryData<Resident[]>(residentKeys.list(), old =>
        old?.filter(resident => resident.id !== id)
      );

      return { previousResidents };
    },
    onError: (err: ApiError, id, context) => {
      // Roll back on error
      if (context?.previousResidents) {
        queryClient.setQueryData(
          residentKeys.list(),
          context.previousResidents
        );
      }
    },
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: residentKeys.lists() });
    },
  });
}

export function useRestoreResident() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => residentsApi.restoreResident(id),
    onMutate: async id => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: residentKeys.lists() });

      // Snapshot the previous value
      const previousResidents = queryClient.getQueryData<Resident[]>(
        residentKeys.list()
      );

      // Optimistically add the resident back (we'll need to fetch the restored data)
      // For now, just invalidate to refetch
      queryClient.invalidateQueries({ queryKey: residentKeys.lists() });

      return { previousResidents };
    },
    onError: (err: ApiError, id, context) => {
      // Roll back on error
      if (context?.previousResidents) {
        queryClient.setQueryData(
          residentKeys.list(),
          context.previousResidents
        );
      }
    },
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: residentKeys.lists() });
    },
  });
}

export function useForceDeleteResident() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => residentsApi.forceDeleteResident(id),
    onMutate: async id => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: residentKeys.lists() });

      // Snapshot the previous value
      const previousResidents = queryClient.getQueryData<Resident[]>(
        residentKeys.list()
      );

      // Optimistically remove the resident
      queryClient.setQueryData<Resident[]>(residentKeys.list(), old =>
        old?.filter(resident => resident.id !== id)
      );

      return { previousResidents };
    },
    onError: (err: ApiError, id, context) => {
      // Roll back on error
      if (context?.previousResidents) {
        queryClient.setQueryData(
          residentKeys.list(),
          context.previousResidents
        );
      }
    },
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: residentKeys.lists() });
    },
  });
}
