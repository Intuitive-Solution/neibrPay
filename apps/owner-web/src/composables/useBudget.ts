import { useQuery } from '@tanstack/vue-query';
import { computed, unref, type Ref } from 'vue';
import { budgetApi, queryKeys } from '@neibrpay/api-client';
import type { BudgetData } from '@neibrpay/models';

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
