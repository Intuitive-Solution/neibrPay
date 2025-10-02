// Query keys for TanStack Query

export const residentKeys = {
  all: ['residents'] as const,
  lists: () => [...residentKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...residentKeys.lists(), { filters }] as const,
  details: () => [...residentKeys.all, 'detail'] as const,
  detail: (id: number) => [...residentKeys.details(), id] as const,
} as const;

export const queryKeys = {
  residents: residentKeys,
} as const;
