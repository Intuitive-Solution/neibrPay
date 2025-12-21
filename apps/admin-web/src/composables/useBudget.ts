import { useMutation, useQuery, useQueryClient } from '@tanstack/vue-query';
import { computed, unref, type Ref } from 'vue';
import { budgetApi, queryKeys } from '@neibrpay/api-client';
import type {
  BudgetCategory,
  BudgetData,
  BudgetAuditLog,
  CreateBudgetCategoryDto,
  UpdateBudgetCategoryDto,
  UpdateBudgetEntriesDto,
} from '@neibrpay/models';

// Get all budget categories
export function useBudgetCategories(
  type?: 'income' | 'expense' | Ref<'income' | 'expense'>
) {
  return useQuery({
    queryKey: computed(() => {
      const unwrappedType = unref(type);
      return queryKeys.budget.categoryList(unwrappedType);
    }),
    queryFn: () => {
      const unwrappedType = unref(type);
      return budgetApi.getCategories(unwrappedType);
    },
    staleTime: 5 * 60 * 1000, // 5 minutes
  });
}

// Get budget data for a specific year
export function useBudget(year: number | Ref<number>) {
  return useQuery({
    queryKey: computed(() => {
      const unwrappedYear = unref(year);
      return queryKeys.budget.budget(unwrappedYear);
    }),
    queryFn: () => {
      const unwrappedYear = unref(year);
      return budgetApi.getBudget(unwrappedYear);
    },
    staleTime: 2 * 60 * 1000, // 2 minutes
  });
}

// Get audit logs for a specific year
export function useBudgetAuditLogs(year: number | Ref<number>) {
  return useQuery({
    queryKey: computed(() => {
      const unwrappedYear = unref(year);
      return queryKeys.budget.auditLogs(unwrappedYear);
    }),
    queryFn: () => {
      const unwrappedYear = unref(year);
      return budgetApi.getAuditLogs(unwrappedYear);
    },
    staleTime: 1 * 60 * 1000, // 1 minute
  });
}

// Create budget category mutation
export function useCreateBudgetCategory() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: CreateBudgetCategoryDto) =>
      budgetApi.createCategory(data),
    onSuccess: () => {
      // Invalidate category lists
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.categories(),
      });
      // Invalidate budget data for all years (categories affect all years)
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.all,
      });
    },
  });
}

// Update budget category mutation
export function useUpdateBudgetCategory() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ id, data }: { id: number; data: UpdateBudgetCategoryDto }) =>
      budgetApi.updateCategory(id, data),
    onSuccess: () => {
      // Invalidate category lists
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.categories(),
      });
      // Invalidate budget data for all years
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.all,
      });
    },
  });
}

// Delete budget category mutation
export function useDeleteBudgetCategory() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (id: number) => budgetApi.deleteCategory(id),
    onSuccess: () => {
      // Invalidate category lists
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.categories(),
      });
      // Invalidate budget data for all years
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.all,
      });
    },
  });
}

// Update budget entries mutation
export function useUpdateBudgetEntries() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: (data: UpdateBudgetEntriesDto) => budgetApi.updateEntries(data),
    onSuccess: (_, variables) => {
      // Extract year from entries (assuming all entries are for the same year)
      const year = variables.entries[0]?.year;
      if (year) {
        // Invalidate budget data for that year
        queryClient.invalidateQueries({
          queryKey: queryKeys.budget.budget(year),
        });
        // Invalidate audit logs
        queryClient.invalidateQueries({
          queryKey: queryKeys.budget.auditLogs(year),
        });
      }
    },
  });
}

// Copy budget mutation
export function useCopyBudget() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: ({ fromYear, toYear }: { fromYear: number; toYear: number }) =>
      budgetApi.copyBudget(fromYear, toYear),
    onSuccess: (_, variables) => {
      // Invalidate budget data for target year
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.budget(variables.toYear),
      });
      // Invalidate audit logs for target year
      queryClient.invalidateQueries({
        queryKey: queryKeys.budget.auditLogs(variables.toYear),
      });
    },
  });
}
