import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { unitsApi, unitKeys } from '@neibrpay/api-client';
import { computed, type Ref } from 'vue';
import type {
  Unit,
  CreateUnitRequest,
  UpdateUnitRequest,
  ApiError,
} from '@neibrpay/models';

// Query hooks
export function useUnits(includeDeleted: Ref<boolean> | boolean = false) {
  const includeDeletedRef = computed(() =>
    typeof includeDeleted === 'boolean' ? includeDeleted : includeDeleted.value
  );

  return useQuery({
    queryKey: computed(() =>
      unitKeys.list({ includeDeleted: includeDeletedRef.value })
    ),
    queryFn: () => unitsApi.getUnits(includeDeletedRef.value),
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

export function useUnit(id: number) {
  return useQuery({
    queryKey: unitKeys.detail(id),
    queryFn: () => unitsApi.getUnit(id),
    enabled: !!id,
  });
}

// Mutation hooks with optimistic updates
export function useCreateUnit() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateUnitRequest) => unitsApi.createUnit(data),
    onMutate: async newUnit => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: unitKeys.lists() });

      // Snapshot the previous value
      const previousUnits = queryClient.getQueryData<Unit[]>(unitKeys.list());

      // Optimistically update to the new value
      const optimisticUnit: Unit = {
        id: Date.now(), // Temporary ID
        title: newUnit.title,
        address: newUnit.address,
        city: newUnit.city,
        state: newUnit.state,
        zip_code: newUnit.zip_code,
        starting_balance: newUnit.starting_balance,
        balance_as_of_date: newUnit.balance_as_of_date,
        tenant_id: 1, // Will be set by backend
        is_active: true,
        created_at: new Date().toISOString(),
        updated_at: new Date().toISOString(),
      };

      queryClient.setQueryData<Unit[]>(unitKeys.list(), old =>
        old ? [optimisticUnit, ...old] : [optimisticUnit]
      );

      // Return a context object with the snapshotted value
      return { previousUnits };
    },
    onError: (err: ApiError, newUnit, context) => {
      // If the mutation fails, use the context returned from onMutate to roll back
      if (context?.previousUnits) {
        queryClient.setQueryData(unitKeys.list(), context.previousUnits);
      }
    },
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: unitKeys.lists() });
    },
  });
}

export function useUpdateUnit() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateUnitRequest }) =>
      unitsApi.updateUnit(id, data),
    onMutate: async ({ id, data }) => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: unitKeys.lists() });
      await queryClient.cancelQueries({ queryKey: unitKeys.detail(id) });

      // Snapshot the previous values
      const previousUnits = queryClient.getQueryData<Unit[]>(unitKeys.list());
      const previousUnit = queryClient.getQueryData<Unit>(unitKeys.detail(id));

      // Optimistically update the unit in the list
      queryClient.setQueryData<Unit[]>(unitKeys.list(), old =>
        old?.map(unit =>
          unit.id === id
            ? { ...unit, ...data, updated_at: new Date().toISOString() }
            : unit
        )
      );

      // Optimistically update the individual unit
      queryClient.setQueryData<Unit>(unitKeys.detail(id), old =>
        old ? { ...old, ...data, updated_at: new Date().toISOString() } : old
      );

      return { previousUnits, previousUnit };
    },
    onError: (err: ApiError, { id }, context) => {
      // Roll back on error
      if (context?.previousUnits) {
        queryClient.setQueryData(unitKeys.list(), context.previousUnits);
      }
      if (context?.previousUnit) {
        queryClient.setQueryData(unitKeys.detail(id), context.previousUnit);
      }
    },
    onSettled: (data, error, { id }) => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: unitKeys.lists() });
      queryClient.invalidateQueries({ queryKey: unitKeys.detail(id) });
    },
  });
}

export function useDeleteUnit() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => unitsApi.deleteUnit(id),
    onMutate: async id => {
      // Cancel any outgoing refetches
      await queryClient.cancelQueries({ queryKey: unitKeys.lists() });

      // Snapshot the previous value
      const previousUnits = queryClient.getQueryData<Unit[]>(unitKeys.list());

      // Optimistically update to the new value
      queryClient.setQueryData<Unit[]>(
        unitKeys.list(),
        old => old?.filter(unit => unit.id !== id) || []
      );

      return { previousUnits };
    },
    onError: (err: ApiError, id, context) => {
      // If the mutation fails, use the context returned from onMutate to roll back
      if (context?.previousUnits) {
        queryClient.setQueryData(unitKeys.list(), context.previousUnits);
      }
    },
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: unitKeys.lists() });
    },
  });
}

export function useRestoreUnit() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => unitsApi.restoreUnit(id),
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: unitKeys.lists() });
    },
  });
}

export function useForceDeleteUnit() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => unitsApi.forceDeleteUnit(id),
    onSettled: () => {
      // Always refetch after error or success
      queryClient.invalidateQueries({ queryKey: unitKeys.lists() });
    },
  });
}
