// Query keys for TanStack Query

export const residentKeys = {
  all: ['residents'] as const,
  lists: () => [...residentKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...residentKeys.lists(), { filters }] as const,
  details: () => [...residentKeys.all, 'detail'] as const,
  detail: (id: number) => [...residentKeys.details(), id] as const,
} as const;

export const unitKeys = {
  all: ['units'] as const,
  lists: () => [...unitKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...unitKeys.lists(), { filters }] as const,
  details: () => [...unitKeys.all, 'detail'] as const,
  detail: (id: number) => [...unitKeys.details(), id] as const,
  documents: (id: number) => [...unitKeys.detail(id), 'documents'] as const,
} as const;

export const chargeKeys = {
  all: ['charges'] as const,
  lists: () => [...chargeKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...chargeKeys.lists(), { filters }] as const,
  details: () => [...chargeKeys.all, 'detail'] as const,
  detail: (id: number) => [...chargeKeys.details(), id] as const,
} as const;

export const queryKeys = {
  residents: residentKeys,
  units: unitKeys,
  charges: chargeKeys,
} as const;
