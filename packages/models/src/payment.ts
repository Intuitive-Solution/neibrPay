import { z } from 'zod';
import { InvoiceUnitSchema } from './invoice';

// Payment Method Schema
export const PaymentMethodSchema = z.enum([
  'cash',
  'check',
  'credit_card',
  'bank_transfer',
  'stripe_card',
  'stripe_ach',
  'other',
]);

// Create Payment Request Schema
export const CreatePaymentRequestSchema = z.object({
  amount: z.number().positive('Amount must be greater than 0'),
  payment_method: PaymentMethodSchema,
  payment_reference: z.string().optional(),
  notes: z.string().optional(),
  payment_date: z
    .string()
    .regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format (YYYY-MM-DD)'),
});

// Update Payment Request Schema
export const UpdatePaymentRequestSchema = z.object({
  amount: z.number().positive('Amount must be greater than 0').optional(),
  payment_method: PaymentMethodSchema.optional(),
  payment_reference: z.string().optional(),
  notes: z.string().optional(),
  payment_date: z
    .string()
    .regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format (YYYY-MM-DD)')
    .optional(),
});

// Payment Schema
export const PaymentSchema = z.object({
  id: z.number().int().positive(),
  invoice_unit_id: z.number().int().positive(),
  amount: z.number().positive(),
  payment_method: PaymentMethodSchema,
  payment_reference: z.string().nullable(),
  notes: z.string().nullable(),
  payment_date: z.string(),
  recorded_by: z.number().int().positive(),
  created_at: z.string(),
  updated_at: z.string(),
  // Stripe fields
  stripe_checkout_session_id: z.string().nullable().optional(),
  stripe_payment_intent_id: z.string().nullable().optional(),
  stripe_payment_method: z.enum(['card', 'ach_debit']).nullable().optional(),
  // Relationships
  invoiceUnit: InvoiceUnitSchema.optional(),
  recorder: z
    .object({
      id: z.number().int().positive(),
      name: z.string(),
      email: z.string(),
    })
    .optional(),
});

// Payment List Response Schema
export const PaymentListResponseSchema = z.object({
  data: z.array(PaymentSchema),
  meta: z.object({
    total: z.number().int(),
    filters: z
      .object({
        invoice_id: z.number().int().positive().optional(),
        payment_method: PaymentMethodSchema.optional(),
        start_date: z.string().optional(),
        end_date: z.string().optional(),
      })
      .optional(),
  }),
});

// TypeScript Types
export type PaymentMethodType = z.infer<typeof PaymentMethodSchema>;

// Payment Filters Type
export interface PaymentFilters {
  invoice_id?: number;
  start_date?: string;
  end_date?: string;
  payment_method?: PaymentMethodType;
}
export type CreatePaymentRequest = z.infer<typeof CreatePaymentRequestSchema>;
export type UpdatePaymentRequest = z.infer<typeof UpdatePaymentRequestSchema>;
export type Payment = z.infer<typeof PaymentSchema>;
export type PaymentListResponse = z.infer<typeof PaymentListResponseSchema>;

// Validation functions
export const validateCreatePaymentRequest = (
  data: unknown
): CreatePaymentRequest => {
  return CreatePaymentRequestSchema.parse(data);
};

export const validateUpdatePaymentRequest = (
  data: unknown
): UpdatePaymentRequest => {
  return UpdatePaymentRequestSchema.parse(data);
};

export const validatePayment = (data: unknown): Payment => {
  return PaymentSchema.parse(data);
};

export const validatePaymentListResponse = (
  data: unknown
): PaymentListResponse => {
  return PaymentListResponseSchema.parse(data);
};
