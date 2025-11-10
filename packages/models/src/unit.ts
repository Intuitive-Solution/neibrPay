// Unit data models and types

import type { Resident } from './resident';

export interface Unit {
  id: number;
  title: string;
  address: string;
  city: string;
  state: string;
  zip_code: string;
  starting_balance: number;
  balance_as_of_date: string;
  tenant_id: number;
  is_active: boolean;
  deleted_at?: string | null;
  created_at: string;
  updated_at: string;
  tenant?: {
    id: number;
    name: string;
    slug: string;
  };
  owners?: Resident[];
  documents?: UnitDocument[];
  pivot?: {
    type: 'owner' | 'tenant';
  };
}

export interface UnitWithResident {
  id: number;
  title: string;
  resident_name: string;
  address: string;
  city: string;
  state: string;
  zip_code: string;
  starting_balance: number;
  balance_as_of_date: string;
}

export interface CreateUnitRequest {
  title: string;
  address: string;
  city: string;
  state: string;
  zip_code: string;
  starting_balance: number;
  balance_as_of_date: string;
}

export interface UpdateUnitRequest {
  title?: string;
  address?: string;
  city?: string;
  state?: string;
  zip_code?: string;
  starting_balance?: number;
  balance_as_of_date?: string;
}

export interface UnitsResponse {
  data: Unit[];
  meta: {
    total: number;
    include_deleted: boolean;
  };
}

export interface UnitsWithResidentResponse {
  data: UnitWithResident[];
  meta: {
    total: number;
  };
}

export interface UnitResponse {
  data: Unit;
  message?: string;
}

// Unit Document types
export interface UnitDocument {
  id: number;
  unit_id: number;
  tenant_id: number;
  file_name: string;
  file_path: string;
  file_hash: string;
  file_size: number;
  mime_type: string;
  description?: string;
  uploaded_by: number;
  created_at: string;
  updated_at: string;
  deleted_at?: string | null;
  uploader?: {
    id: number;
    name: string;
    email: string;
  };
  file_url?: string;
  file_size_human?: string;
}

export interface UnitDocumentsResponse {
  data: UnitDocument[];
  meta: {
    total: number;
  };
}

export interface UnitDocumentResponse {
  data: UnitDocument;
  message?: string;
}

// Form validation types
export interface UnitFormData {
  title: string;
  address: string;
  city: string;
  state: string;
  zip_code: string;
  starting_balance: number;
  balance_as_of_date: string;
}

export interface UnitFormErrors {
  title?: string;
  address?: string;
  city?: string;
  state?: string;
  zip_code?: string;
  starting_balance?: string;
  balance_as_of_date?: string;
  general?: string;
}

// US States list
export const US_STATES = [
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
] as const;
