import { z } from 'zod';

// Invoice Item Schema
export const InvoiceItemSchema = z.object({
  name: z.string().min(1, 'Item name is required').max(255),
  description: z.string().optional(),
  unit_cost: z.number().min(0, 'Unit cost must be non-negative'),
  quantity: z.number().min(0.01, 'Quantity must be at least 0.01'),
  line_total: z.number().min(0, 'Line total must be non-negative'),
  sort_order: z.number().optional(),
  category: z.string().optional(),
  taxable: z.boolean().optional(),
  custom_fields: z.record(z.any()).optional(),
});

// Invoice Notes Schema
export const InvoiceNotesSchema = z.object({
  public_notes: z.string().optional(),
  private_notes: z.string().optional(),
  terms: z.string().optional(),
  footer: z.string().optional(),
});

// Create Invoice Request Schema
export const CreateInvoiceRequestSchema = z.object({
  unit_ids: z
    .array(z.number().int().positive())
    .min(1, 'At least one unit must be selected'),
  frequency: z.enum(['one-time', 'monthly', 'weekly', 'quarterly', 'yearly']),
  start_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format'),
  remaining_cycles: z.string().optional(),
  due_date: z.enum([
    'use_payment_terms',
    'net_15',
    'net_30',
    'net_45',
    'net_60',
    'due_on_receipt',
  ]),
  early_payment_discount_enabled: z.boolean().optional(),
  early_payment_discount_amount: z.number().min(0).optional(),
  early_payment_discount_type: z.enum(['amount', 'percentage']).optional(),
  early_payment_discount_by_date: z
    .string()
    .regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format')
    .optional(),
  late_fee_enabled: z.boolean().optional(),
  late_fee_amount: z.number().min(0).optional(),
  late_fee_type: z.enum(['amount', 'percentage']).optional(),
  late_fee_applies_on_date: z
    .string()
    .regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format')
    .optional(),
  items: z.array(InvoiceItemSchema).min(1, 'At least one item is required'),
  tax_rate: z.number().min(0).max(100).optional(),
  notes: InvoiceNotesSchema.optional(),
  po_number: z.string().optional(),
});

// Update Invoice Request Schema
export const UpdateInvoiceRequestSchema = z.object({
  unit_id: z.number().int().positive(),
  frequency: z.enum(['one-time', 'monthly', 'weekly', 'quarterly', 'yearly']),
  start_date: z.string().regex(/^\d{4}-\d{2}-\d{2}$/),
  remaining_cycles: z.string().optional(),
  due_date: z.enum([
    'use_payment_terms',
    'net_15',
    'net_30',
    'net_45',
    'net_60',
    'due_on_receipt',
  ]),
  po_number: z.string().optional(),
  early_payment_discount_enabled: z.boolean().optional(),
  early_payment_discount_amount: z.number().min(0).optional(),
  early_payment_discount_type: z.enum(['amount', 'percentage']).optional(),
  early_payment_discount_by_date: z
    .string()
    .regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format')
    .optional(),
  late_fee_enabled: z.boolean().optional(),
  late_fee_amount: z.number().min(0).optional(),
  late_fee_type: z.enum(['amount', 'percentage']).optional(),
  late_fee_applies_on_date: z
    .string()
    .regex(/^\d{4}-\d{2}-\d{2}$/, 'Invalid date format')
    .optional(),
  items: z.array(InvoiceItemSchema).min(1).optional(),
  tax_rate: z.number().min(0).max(100).optional(),
  notes: InvoiceNotesSchema.optional(),
});

// Invoice PDF Schema
export const InvoicePdfSchema = z.object({
  id: z.number().int().positive(),
  invoice_unit_id: z.number().int().positive(),
  version: z.number().int().positive(),
  file_name: z.string(),
  file_path: z.string(),
  file_size: z.number().int(),
  is_latest: z.boolean(),
  generated_by: z.number().int().positive(),
  created_at: z.string(),
  updated_at: z.string(),
  // Relationships
  generator: z
    .object({
      id: z.number().int().positive(),
      name: z.string(),
      email: z.string(),
    })
    .optional(),
});

// Invoice Unit Schema
export const InvoiceUnitSchema = z.object({
  id: z.number().int().positive(),
  tenant_id: z.number().int().positive(),
  unit_id: z.number().int().positive(),
  invoice_number: z.string(),
  po_number: z.string().nullable(),
  frequency: z.enum(['one-time', 'monthly', 'weekly', 'quarterly', 'yearly']),
  start_date: z.string(),
  remaining_cycles: z.string().nullable(),
  due_date: z.enum([
    'use_payment_terms',
    'net_15',
    'net_30',
    'net_45',
    'net_60',
    'due_on_receipt',
  ]),
  early_payment_discount_enabled: z.boolean().optional(),
  early_payment_discount_amount: z.number().optional(),
  early_payment_discount_type: z.enum(['amount', 'percentage']).optional(),
  early_payment_discount_by_date: z.string().optional(),
  late_fee_enabled: z.boolean().optional(),
  late_fee_amount: z.number().optional(),
  late_fee_type: z.enum(['amount', 'percentage']).optional(),
  late_fee_applies_on_date: z.string().optional(),
  items: z.array(InvoiceItemSchema),
  subtotal: z.number(),
  tax_rate: z.number(),
  tax_amount: z.number(),
  total: z.number(),
  balance_due: z.number(),
  status: z.enum([
    'draft',
    'sent',
    'paid',
    'partial',
    'overdue',
    'cancelled',
    'in_review',
    'payment_rejected',
  ]),
  parent_invoice_id: z.number().int().positive().nullable(),
  created_by: z.number().int().positive(),
  created_at: z.string(),
  updated_at: z.string(),
  deleted_at: z.string().nullable(),
  // Relationships
  unit: z
    .object({
      id: z.number().int().positive(),
      title: z.string(),
      address: z.string(),
      city: z.string(),
      state: z.string(),
      zip_code: z.string(),
      owners: z
        .array(
          z.object({
            id: z.number().int().positive(),
            name: z.string(),
            email: z.string(),
          })
        )
        .optional(),
    })
    .optional(),
  creator: z
    .object({
      id: z.number().int().positive(),
      name: z.string(),
      email: z.string(),
    })
    .optional(),
  notes: z
    .array(
      z.object({
        id: z.number().int().positive(),
        type: z.enum(['public_notes', 'private_notes', 'terms', 'footer']),
        content: z.string(),
      })
    )
    .optional(),
  payments: z
    .array(
      z.object({
        id: z.number().int().positive(),
        amount: z.number(),
        payment_method: z.enum([
          'cash',
          'check',
          'credit_card',
          'bank_transfer',
          'other',
        ]),
        payment_reference: z.string().nullable(),
        notes: z.string().nullable(),
        payment_date: z.string(),
        recorded_by: z.number().int().positive(),
        created_at: z.string(),
        updated_at: z.string(),
      })
    )
    .optional(),
  schedule: z
    .object({
      id: z.number().int().positive(),
      next_due_date: z.string(),
      remaining_cycles: z.number().int().nullable(),
      is_active: z.boolean(),
      last_generated_at: z.string().nullable(),
      created_at: z.string(),
      updated_at: z.string(),
    })
    .optional(),
  attachments: z
    .array(
      z.object({
        id: z.number().int().positive(),
        file_name: z.string(),
        file_path: z.string(),
        file_hash: z.string(),
        file_size: z.number().int(),
        mime_type: z.string(),
        attachment_type: z.enum(['pdf', 'image', 'document', 'other']),
        uploaded_by: z.number().int().positive(),
        created_at: z.string(),
        updated_at: z.string(),
      })
    )
    .optional(),
});

// TypeScript Types
export type InvoiceItem = z.infer<typeof InvoiceItemSchema>;
export type InvoiceNotes = z.infer<typeof InvoiceNotesSchema>;
export type CreateInvoiceRequest = z.infer<typeof CreateInvoiceRequestSchema>;
export type UpdateInvoiceRequest = z.infer<typeof UpdateInvoiceRequestSchema>;
export type InvoicePdf = z.infer<typeof InvoicePdfSchema>;
export type InvoiceUnit = z.infer<typeof InvoiceUnitSchema>;

// Validation functions
export const validateCreateInvoiceRequest = (
  data: unknown
): CreateInvoiceRequest => {
  return CreateInvoiceRequestSchema.parse(data);
};

export const validateUpdateInvoiceRequest = (
  data: unknown
): UpdateInvoiceRequest => {
  return UpdateInvoiceRequestSchema.parse(data);
};

export const validateInvoiceUnit = (data: unknown): InvoiceUnit => {
  return InvoiceUnitSchema.parse(data);
};
