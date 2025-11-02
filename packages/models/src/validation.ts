import { z } from 'zod';

// US Phone number validation regex - accepts both (XXX) XXX-XXXX and XXX-XXX-XXXX formats
const US_PHONE_REGEX = /^(\(\d{3}\) \d{3}-\d{4}|\d{3}-\d{3}-\d{4})$/;

// Resident validation schemas
export const createResidentSchema = z.object({
  name: z
    .string()
    .min(2, 'Name must be at least 2 characters')
    .max(255, 'Name must not exceed 255 characters')
    .trim(),
  email: z
    .string()
    .email('Please enter a valid email address')
    .max(255, 'Email must not exceed 255 characters')
    .toLowerCase()
    .trim(),
  phone: z
    .string()
    .regex(US_PHONE_REGEX, 'Please enter a valid US phone number (10 digits)')
    .max(14, 'Phone number must not exceed 14 characters'),
  type: z
    .enum(['owner', 'tenant', 'others'], {
      message: 'Type must be Owner, Tenant, or Others',
    })
    .default('owner'),
  member_role: z
    .enum(['admin', 'member'], {
      message: 'Member role must be Admin or Member',
    })
    .default('member'),
});

export const updateResidentSchema = createResidentSchema.partial();

// US States list for validation
const US_STATES = [
  'AL',
  'AK',
  'AZ',
  'AR',
  'CA',
  'CO',
  'CT',
  'DE',
  'FL',
  'GA',
  'HI',
  'ID',
  'IL',
  'IN',
  'IA',
  'KS',
  'KY',
  'LA',
  'ME',
  'MD',
  'MA',
  'MI',
  'MN',
  'MS',
  'MO',
  'MT',
  'NE',
  'NV',
  'NH',
  'NJ',
  'NM',
  'NY',
  'NC',
  'ND',
  'OH',
  'OK',
  'OR',
  'PA',
  'RI',
  'SC',
  'SD',
  'TN',
  'TX',
  'UT',
  'VT',
  'VA',
  'WA',
  'WV',
  'WI',
  'WY',
] as const;

// US ZIP code validation regex
const US_ZIP_REGEX = /^\d{5}(-\d{4})?$/;

// Unit validation schemas
export const createUnitSchema = z.object({
  title: z
    .string()
    .min(2, 'Title must be at least 2 characters')
    .max(255, 'Title must not exceed 255 characters')
    .trim(),
  address: z
    .string()
    .min(5, 'Address must be at least 5 characters')
    .max(500, 'Address must not exceed 500 characters')
    .trim(),
  city: z
    .string()
    .min(2, 'City must be at least 2 characters')
    .max(100, 'City must not exceed 100 characters')
    .trim(),
  state: z.enum(US_STATES, { message: 'Please select a valid US state' }),
  zip_code: z
    .string()
    .regex(
      US_ZIP_REGEX,
      'Please enter a valid US ZIP code (12345 or 12345-6789)'
    )
    .max(10, 'ZIP code must not exceed 10 characters'),
  starting_balance: z
    .number()
    .min(-999999.99, 'Starting balance must be at least -$999,999.99')
    .max(999999.99, 'Starting balance must not exceed $999,999.99'),
  balance_as_of_date: z
    .string()
    .min(1, 'Balance as of date is required')
    .refine(date => !isNaN(Date.parse(date)), 'Please enter a valid date'),
});

export const updateUnitSchema = createUnitSchema.partial();

// Phone number formatting utility
export function formatPhoneNumber(value: string): string {
  // Remove all non-digit characters
  const digits = value.replace(/\D/g, '');

  // Format as (XXX) XXX-XXXX
  if (digits.length >= 6) {
    return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
  } else if (digits.length >= 3) {
    return `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
  } else if (digits.length > 0) {
    return `(${digits}`;
  }

  return digits;
}

// Validation helper functions
export function validateResidentForm(data: any) {
  try {
    return {
      success: true,
      data: createResidentSchema.parse(data),
      errors: null,
    };
  } catch (error) {
    if (error instanceof z.ZodError) {
      const errors: Record<string, string> = {};
      error.errors.forEach(err => {
        if (err.path[0]) {
          errors[err.path[0] as string] = err.message;
        }
      });
      return {
        success: false,
        data: null,
        errors,
      };
    }
    return {
      success: false,
      data: null,
      errors: { general: 'An unexpected error occurred' },
    };
  }
}

export function validateUpdateResidentForm(data: any) {
  try {
    return {
      success: true,
      data: updateResidentSchema.parse(data),
      errors: null,
    };
  } catch (error) {
    if (error instanceof z.ZodError) {
      const errors: Record<string, string> = {};
      error.errors.forEach(err => {
        if (err.path[0]) {
          errors[err.path[0] as string] = err.message;
        }
      });
      return {
        success: false,
        data: null,
        errors,
      };
    }
    return {
      success: false,
      data: null,
      errors: { general: 'An unexpected error occurred' },
    };
  }
}

// Unit validation helper functions
export function validateUnitForm(data: any) {
  try {
    return {
      success: true,
      data: createUnitSchema.parse(data),
      errors: null,
    };
  } catch (error) {
    if (error instanceof z.ZodError) {
      const errors: Record<string, string> = {};
      error.errors.forEach(err => {
        if (err.path[0]) {
          errors[err.path[0] as string] = err.message;
        }
      });
      return {
        success: false,
        data: null,
        errors,
      };
    }
    return {
      success: false,
      data: null,
      errors: { general: 'An unexpected error occurred' },
    };
  }
}

export function validateUpdateUnitForm(data: any) {
  try {
    return {
      success: true,
      data: updateUnitSchema.parse(data),
      errors: null,
    };
  } catch (error) {
    if (error instanceof z.ZodError) {
      const errors: Record<string, string> = {};
      error.errors.forEach(err => {
        if (err.path[0]) {
          errors[err.path[0] as string] = err.message;
        }
      });
      return {
        success: false,
        data: null,
        errors,
      };
    }
    return {
      success: false,
      data: null,
      errors: { general: 'An unexpected error occurred' },
    };
  }
}
