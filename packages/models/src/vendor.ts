export enum VendorCategory {
  MAINTENANCE = 'maintenance',
  LANDSCAPING = 'landscaping',
  LEGAL = 'legal',
  INSURANCE = 'insurance',
  UTILITIES = 'utilities',
  OTHER = 'other',
}

export interface Vendor {
  id: number;
  tenant_id: number;
  name: string;
  description?: string;
  category: VendorCategory;
  ein?: string;
  street_address: string;
  city: string;
  state: string;
  zip_code: string;
  website?: string;
  notes?: string;
  contact_name: string;
  contact_email: string;
  contact_phone: string;
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

export interface CreateVendorDto {
  name: string;
  description?: string;
  category: VendorCategory;
  ein?: string;
  street_address: string;
  city: string;
  state: string;
  zip_code: string;
  website?: string;
  notes?: string;
  contact_name: string;
  contact_email: string;
  contact_phone: string;
}

export interface UpdateVendorDto {
  name?: string;
  description?: string;
  category?: VendorCategory;
  ein?: string;
  street_address?: string;
  city?: string;
  state?: string;
  zip_code?: string;
  website?: string;
  notes?: string;
  contact_name?: string;
  contact_email?: string;
  contact_phone?: string;
}

export interface VendorFilters {
  category?: VendorCategory | string;
  include_deleted?: boolean;
  search?: string;
}

export interface VendorListResponse {
  data: Vendor[];
  meta: {
    total: number;
    category?: VendorCategory;
    include_deleted?: boolean;
    search?: string;
  };
}

export interface VendorResponse {
  data: Vendor;
  message?: string;
}

// Helper function to get category display name
export function getVendorCategoryDisplayName(category: VendorCategory): string {
  const displayNames: Record<VendorCategory, string> = {
    [VendorCategory.MAINTENANCE]: 'Maintenance',
    [VendorCategory.LANDSCAPING]: 'Landscaping',
    [VendorCategory.LEGAL]: 'Legal',
    [VendorCategory.INSURANCE]: 'Insurance',
    [VendorCategory.UTILITIES]: 'Utilities',
    [VendorCategory.OTHER]: 'Other',
  };

  return displayNames[category] || category;
}

// Helper function to get all category options for dropdowns
export function getVendorCategoryOptions(): Array<{
  value: VendorCategory;
  label: string;
}> {
  return Object.values(VendorCategory).map(category => ({
    value: category,
    label: getVendorCategoryDisplayName(category),
  }));
}

// Helper function to get US states options
export function getUSStates(): Array<{
  value: string;
  label: string;
}> {
  return [
    { value: 'AL', label: 'Alabama' },
    { value: 'AK', label: 'Alaska' },
    { value: 'AZ', label: 'Arizona' },
    { value: 'AR', label: 'Arkansas' },
    { value: 'CA', label: 'California' },
    { value: 'CO', label: 'Colorado' },
    { value: 'CT', label: 'Connecticut' },
    { value: 'DE', label: 'Delaware' },
    { value: 'FL', label: 'Florida' },
    { value: 'GA', label: 'Georgia' },
    { value: 'HI', label: 'Hawaii' },
    { value: 'ID', label: 'Idaho' },
    { value: 'IL', label: 'Illinois' },
    { value: 'IN', label: 'Indiana' },
    { value: 'IA', label: 'Iowa' },
    { value: 'KS', label: 'Kansas' },
    { value: 'KY', label: 'Kentucky' },
    { value: 'LA', label: 'Louisiana' },
    { value: 'ME', label: 'Maine' },
    { value: 'MD', label: 'Maryland' },
    { value: 'MA', label: 'Massachusetts' },
    { value: 'MI', label: 'Michigan' },
    { value: 'MN', label: 'Minnesota' },
    { value: 'MS', label: 'Mississippi' },
    { value: 'MO', label: 'Missouri' },
    { value: 'MT', label: 'Montana' },
    { value: 'NE', label: 'Nebraska' },
    { value: 'NV', label: 'Nevada' },
    { value: 'NH', label: 'New Hampshire' },
    { value: 'NJ', label: 'New Jersey' },
    { value: 'NM', label: 'New Mexico' },
    { value: 'NY', label: 'New York' },
    { value: 'NC', label: 'North Carolina' },
    { value: 'ND', label: 'North Dakota' },
    { value: 'OH', label: 'Ohio' },
    { value: 'OK', label: 'Oklahoma' },
    { value: 'OR', label: 'Oregon' },
    { value: 'PA', label: 'Pennsylvania' },
    { value: 'RI', label: 'Rhode Island' },
    { value: 'SC', label: 'South Carolina' },
    { value: 'SD', label: 'South Dakota' },
    { value: 'TN', label: 'Tennessee' },
    { value: 'TX', label: 'Texas' },
    { value: 'UT', label: 'Utah' },
    { value: 'VT', label: 'Vermont' },
    { value: 'VA', label: 'Virginia' },
    { value: 'WA', label: 'Washington' },
    { value: 'WV', label: 'West Virginia' },
    { value: 'WI', label: 'Wisconsin' },
    { value: 'WY', label: 'Wyoming' },
    { value: 'DC', label: 'District of Columbia' },
  ];
}
