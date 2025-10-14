export enum ChargeCategory {
  HOA_FEE = 'hoa_fee',
  MAINTENANCE = 'maintenance',
  PENALTIES = 'penalties',
  SPECIAL_ASSESSMENT = 'special_assessment',
  OTHER = 'other',
}

export interface Charge {
  id: number;
  tenant_id: number;
  title: string;
  description?: string;
  amount: number;
  category: ChargeCategory;
  is_active: boolean;
  created_by: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string;
  creator?: {
    id: number;
    name: string;
    email: string;
  };
}

export interface CreateChargeDto {
  title: string;
  description?: string;
  amount: number;
  category: ChargeCategory;
  is_active?: boolean;
}

export interface UpdateChargeDto {
  title?: string;
  description?: string;
  amount?: number;
  category?: ChargeCategory;
  is_active?: boolean;
}

export interface ChargeFilters {
  category?: ChargeCategory | string;
  is_active?: boolean | string;
  include_deleted?: boolean;
  search?: string;
}

export interface ChargeListResponse {
  data: Charge[];
  meta: {
    total: number;
    category?: ChargeCategory;
    is_active?: boolean;
    include_deleted?: boolean;
    search?: string;
  };
}

export interface ChargeResponse {
  data: Charge;
  message?: string;
}

// Helper function to get category display name
export function getChargeCategoryDisplayName(category: ChargeCategory): string {
  const displayNames: Record<ChargeCategory, string> = {
    [ChargeCategory.HOA_FEE]: 'HOA Fee',
    [ChargeCategory.MAINTENANCE]: 'Maintenance',
    [ChargeCategory.PENALTIES]: 'Penalties',
    [ChargeCategory.SPECIAL_ASSESSMENT]: 'Special Assessment',
    [ChargeCategory.OTHER]: 'Other',
  };

  return displayNames[category] || category;
}

// Helper function to get all category options for dropdowns
export function getChargeCategoryOptions(): Array<{
  value: ChargeCategory;
  label: string;
}> {
  return Object.values(ChargeCategory).map(category => ({
    value: category,
    label: getChargeCategoryDisplayName(category),
  }));
}
