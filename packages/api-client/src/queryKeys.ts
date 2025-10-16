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

export const vendorKeys = {
  all: ['vendors'] as const,
  lists: () => [...vendorKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...vendorKeys.lists(), { filters }] as const,
  details: () => [...vendorKeys.all, 'detail'] as const,
  detail: (id: number) => [...vendorKeys.details(), id] as const,
} as const;

export const expenseKeys = {
  all: ['expenses'] as const,
  lists: () => [...expenseKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...expenseKeys.lists(), { filters }] as const,
  details: () => [...expenseKeys.all, 'detail'] as const,
  detail: (id: number) => [...expenseKeys.details(), id] as const,
  attachments: (id: number) =>
    [...expenseKeys.detail(id), 'attachments'] as const,
} as const;

export const invoiceKeys = {
  all: ['invoices'] as const,
  lists: () => [...invoiceKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...invoiceKeys.lists(), { filters }] as const,
  details: () => [...invoiceKeys.all, 'detail'] as const,
  detail: (id: number) => [...invoiceKeys.details(), id] as const,
  attachments: (id: number) =>
    [...invoiceKeys.detail(id), 'attachments'] as const,
  pdf: (id: number) => [...invoiceKeys.detail(id), 'pdf'] as const,
} as const;

export const paymentKeys = {
  all: ['payments'] as const,
  lists: () => [...paymentKeys.all, 'list'] as const,
  list: (filters: Record<string, any> = {}) =>
    [...paymentKeys.lists(), { filters }] as const,
  details: () => [...paymentKeys.all, 'detail'] as const,
  detail: (id: number) => [...paymentKeys.details(), id] as const,
} as const;

export const queryKeys = {
  residents: residentKeys,
  units: unitKeys,
  charges: chargeKeys,
  vendors: vendorKeys,
  expenses: expenseKeys,
  invoices: invoiceKeys,
  payments: paymentKeys,
} as const;
