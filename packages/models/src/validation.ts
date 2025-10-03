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
});

export const updateResidentSchema = createResidentSchema.partial();

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
