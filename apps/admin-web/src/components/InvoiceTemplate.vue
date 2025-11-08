<template>
  <div id="invoice-preview" class="invoice-template">
    <!-- Header Section -->
    <div class="invoice-header">
      <!-- PAID Stamp -->
      <div v-if="paymentInfo" class="paid-stamp">
        <div class="paid-stamp-content">PAID</div>
      </div>

      <div class="company-info">
        <h1 class="company-name">NeibrPay HOA</h1>
        <div class="company-details">
          <p>123 HOA Management Street</p>
          <p>Property City, PC 12345</p>
          <p>Phone: (555) 123-4567</p>
          <p>Email: info@neibrpay.com</p>
        </div>
      </div>
      <div class="invoice-meta">
        <h2 class="invoice-title">INVOICE</h2>
        <div class="invoice-details">
          <p><strong>Invoice #:</strong> {{ invoiceNumber || 'TBD' }}</p>
          <p><strong>Date:</strong> {{ formatDate(startDate) }}</p>
          <p><strong>Due Date:</strong> {{ formatDate(dueDate) }}</p>
          <p v-if="poNumber"><strong>PO #:</strong> {{ poNumber }}</p>
        </div>
      </div>
    </div>

    <!-- Bill To Section -->
    <div class="bill-to-section">
      <h3 class="section-title">Bill To:</h3>
      <div class="bill-to-content">
        <div v-for="unitId in unitIds" :key="unitId" class="unit-info">
          <div class="unit-header">
            <h4>{{ getUnitTitle(unitId) }}</h4>
          </div>
          <div class="unit-details">
            <p>{{ getUnitAddress(unitId) }}</p>
            <p>{{ getUnitResident(unitId) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Items Table -->
    <div class="items-section">
      <table class="items-table">
        <thead>
          <tr>
            <th class="item-name">Item</th>
            <th class="item-description">Description</th>
            <th class="item-cost">Unit Cost</th>
            <th class="item-quantity">Qty</th>
            <th class="item-total">Total</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in invoiceItems" :key="index">
            <td class="item-name">{{ item.name }}</td>
            <td class="item-description">{{ item.description }}</td>
            <td class="item-cost">${{ formatCurrency(item.unitCost) }}</td>
            <td class="item-quantity">{{ item.quantity }}</td>
            <td class="item-total">${{ formatCurrency(item.lineTotal) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Totals Section -->
    <div class="totals-section">
      <div class="totals-container">
        <div class="total-row">
          <span class="total-label">Subtotal:</span>
          <span class="total-value">${{ formatCurrency(subtotal) }}</span>
        </div>

        <div v-if="discountAmount > 0" class="total-row">
          <span class="total-label"
            >Discount ({{
              discountType === 'percentage' ? discountAmount + '%' : 'Amount'
            }}):</span
          >
          <span class="total-value"
            >-${{ formatCurrency(calculatedDiscount) }}</span
          >
        </div>

        <div v-if="taxRate > 0" class="total-row">
          <span class="total-label">Tax ({{ taxRate }}%):</span>
          <span class="total-value">${{ formatCurrency(taxAmount) }}</span>
        </div>

        <div class="total-row final-total">
          <span class="total-label">Total:</span>
          <span class="total-value">${{ formatCurrency(total) }}</span>
        </div>

        <div v-if="balanceDue > 0" class="total-row balance-due">
          <span class="total-label">Balance Due:</span>
          <span class="total-value">${{ formatCurrency(balanceDue) }}</span>
        </div>
      </div>
    </div>

    <!-- Payment Details Section -->
    <div v-if="paymentInfo" class="payment-details-section">
      <h3 class="section-title">Payment Details:</h3>
      <div class="payment-details-content">
        <p>
          <strong>Payment Date:</strong>
          {{ formatDate(paymentInfo.payment_date) }}
        </p>
        <p>
          <strong>Payment Method:</strong>
          {{ formatPaymentMethod(paymentInfo.payment_method) }}
        </p>
        <p v-if="paymentInfo.payment_reference">
          <strong>Reference:</strong> {{ paymentInfo.payment_reference }}
        </p>
      </div>
    </div>

    <!-- Notes Section -->
    <div v-if="publicNotes" class="notes-section">
      <h3 class="section-title">Notes:</h3>
      <div class="notes-content" v-html="publicNotes"></div>
    </div>

    <!-- Terms Section -->
    <div v-if="terms" class="terms-section">
      <h3 class="section-title">Terms & Conditions:</h3>
      <div class="terms-content" v-html="terms"></div>
    </div>

    <!-- Footer Section -->
    <div v-if="footer" class="footer-section">
      <div class="footer-content" v-html="footer"></div>
    </div>

    <!-- Payment Information -->
    <div class="payment-section">
      <h3 class="section-title">Payment Information</h3>
      <div class="payment-details">
        <p>
          <strong>Payment Methods:</strong> Check, Bank Transfer, Online Payment
        </p>
        <p><strong>Make checks payable to:</strong> NeibrPay HOA</p>
        <p>
          <strong>For questions about this invoice, contact:</strong> (555)
          123-4567
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { UnitWithResident } from '@neibrpay/models';

// Props
interface Props {
  form: {
    unit_ids: number[];
    invoice_number: string;
    po_number: string;
    start_date: string;
    due_date?: string;
    discount_amount: string | number;
    discount_type: string;
  };
  invoiceItems: Array<{
    name: string;
    description: string;
    unitCost: number;
    quantity: number;
    lineTotal: number;
  }>;
  subtotal: number;
  taxRate: number;
  taxAmount: number;
  total: number;
  balanceDue: number;
  tabContent: {
    'public-notes': string;
    'private-notes': string;
    terms: string;
    footer: string;
  };
  units?: UnitWithResident[];
  paymentInfo?: {
    payment_date: string;
    payment_method: string;
    payment_reference?: string;
  };
}

const props = defineProps<Props>();

// Computed properties
const invoiceNumber = computed(() => props.form.invoice_number);
const poNumber = computed(() => props.form.po_number);
const startDate = computed(() => props.form.start_date);
const dueDate = computed(() => props.form.due_date || props.form.start_date);
const unitIds = computed(() => props.form.unit_ids);
const discountAmount = computed(
  () => parseFloat(props.form.discount_amount.toString()) || 0
);
const discountType = computed(() => props.form.discount_type);

const calculatedDiscount = computed(() => {
  if (discountType.value === 'percentage') {
    return (props.subtotal * discountAmount.value) / 100;
  }
  return discountAmount.value;
});

const publicNotes = computed(() => props.tabContent['public-notes']);
const terms = computed(() => props.tabContent.terms);
const footer = computed(() => props.tabContent.footer);

// Helper methods
const formatDate = (dateString: string) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
  });
};

const formatPaymentMethod = (method: string) => {
  const methodMap: Record<string, string> = {
    cash: 'Cash',
    check: 'Check',
    credit_card: 'Credit Card',
    bank_transfer: 'Bank Transfer',
    other: 'Other',
  };
  return methodMap[method] || method;
};

const formatCurrency = (amount: number | string | null | undefined): string => {
  if (amount === null || amount === undefined) return '0.00';
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  if (isNaN(numAmount)) return '0.00';
  return numAmount.toFixed(2);
};

const getUnitTitle = (unitId: number) => {
  const unit = props.units?.find((u: UnitWithResident) => u.id === unitId);
  return unit ? unit.title : `Unit ${unitId}`;
};

const getUnitAddress = (unitId: number) => {
  const unit = props.units?.find((u: UnitWithResident) => u.id === unitId);
  return unit ? `${unit.address}, ${unit.city}` : '';
};

const getUnitResident = (unitId: number) => {
  const unit = props.units?.find((u: UnitWithResident) => u.id === unitId);
  return unit ? unit.resident_name : '';
};
</script>

<style scoped>
.invoice-template {
  width: 180mm; /* A4 width with margins */
  max-width: 180mm;
  min-height: 297mm; /* A4 height */
  padding: 10mm 8mm 10mm 10mm;
  font-family: 'Arial', sans-serif;
  background: white;
  color: #333;
  line-height: 1.3;
  overflow: hidden;
  box-sizing: border-box;
}

/* Header Section */
.invoice-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 25px;
  border-bottom: 3px solid #2563eb;
  padding-bottom: 15px;
  position: relative;
}

.paid-stamp {
  position: absolute;
  top: 10px;
  right: 15mm;
  z-index: 10;
}

.paid-stamp-content {
  background: #10b981;
  color: white;
  padding: 4px 8px;
  border-radius: 3px;
  font-weight: bold;
  font-size: 12px;
  text-align: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  transform: rotate(-15deg);
}

.company-info {
  flex: 1.2;
}

.company-name {
  font-size: 18px;
  font-weight: bold;
  color: #2563eb;
  margin: 0 0 4px 0;
}

.company-details p {
  margin: 1px 0;
  font-size: 9px;
  color: #666;
}

.invoice-meta {
  text-align: right;
  flex: 1;
  padding-right: 5mm;
}

.invoice-title {
  font-size: 18px;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 6px 0;
}

.invoice-details p {
  margin: 1px 0;
  font-size: 9px;
}

/* Bill To Section */
.bill-to-section {
  margin-bottom: 30px;
}

.section-title {
  font-size: 12px;
  font-weight: bold;
  color: #1f2937;
  margin: 0 0 6px 0;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 3px;
}

.bill-to-content {
  display: flex;
  flex-wrap: wrap;
  gap: 20px;
}

.unit-info {
  flex: 1;
  min-width: 200px;
  max-width: 100%;
}

.unit-header h4 {
  font-size: 16px;
  font-weight: bold;
  margin: 0 0 5px 0;
  color: #1f2937;
}

.unit-details p {
  margin: 2px 0;
  font-size: 14px;
  color: #666;
}

/* Items Table */
.items-section {
  margin-bottom: 30px;
}

.items-table {
  width: 100%;
  border-collapse: collapse;
  margin: 0;
  table-layout: fixed;
}

.items-table th {
  background-color: #f8fafc;
  color: #1f2937;
  font-weight: bold;
  padding: 6px 4px;
  text-align: left;
  border: 1px solid #e5e7eb;
  font-size: 10px;
}

.items-table td {
  padding: 6px 4px;
  border: 1px solid #e5e7eb;
  font-size: 10px;
  vertical-align: top;
  word-wrap: break-word;
}

.item-name {
  width: 18%;
  font-weight: 500;
}

.item-description {
  width: 32%;
}

.item-cost {
  width: 15%;
  text-align: right;
}

.item-quantity {
  width: 10%;
  text-align: right;
}

.item-total {
  width: 15%;
  text-align: right;
  font-weight: 500;
}

/* Totals Section */
.totals-section {
  margin-bottom: 25px;
}

.totals-container {
  max-width: 220px;
  margin-left: auto;
  margin-right: 5mm;
}

.total-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 6px 0;
  border-bottom: 1px solid #f3f4f6;
}

.total-label {
  font-size: 10px;
  color: #6b7280;
}

.total-value {
  font-size: 10px;
  font-weight: 500;
  color: #1f2937;
}

.final-total {
  border-top: 2px solid #1f2937;
  border-bottom: 2px solid #1f2937;
  font-weight: bold;
  font-size: 12px;
  margin-top: 6px;
  padding: 8px 0;
}

.final-total .total-label,
.final-total .total-value {
  font-size: 12px;
  font-weight: bold;
}

.balance-due {
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 4px;
  padding: 10px 15px;
  margin-top: 10px;
}

.balance-due .total-label,
.balance-due .total-value {
  color: #dc2626;
  font-weight: bold;
}

/* Payment Details Section */
.payment-details-section {
  margin-bottom: 20px;
  padding: 15px;
  margin-right: 5mm;
  background-color: #f0fdf4;
  border: 2px solid #10b981;
  border-radius: 6px;
}

.payment-details-content p {
  margin: 4px 0;
  font-size: 10px;
  color: #1f2937;
}

/* Notes and Terms Sections */
.notes-section,
.terms-section {
  margin-bottom: 25px;
}

.notes-content,
.terms-content {
  font-size: 14px;
  line-height: 1.6;
  color: #4b5563;
  margin-top: 10px;
}

/* Footer Section */
.footer-section {
  margin-bottom: 25px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.footer-content {
  font-size: 12px;
  color: #6b7280;
  text-align: center;
}

/* Payment Section */
.payment-section {
  margin-top: 30px;
  padding-top: 20px;
  border-top: 1px solid #e5e7eb;
}

.payment-details p {
  margin: 5px 0;
  font-size: 14px;
  color: #4b5563;
}

/* Print-specific styles */
@media print {
  .invoice-template {
    width: 100%;
    min-height: auto;
    padding: 0;
    margin: 0;
  }

  .invoice-header {
    page-break-inside: avoid;
  }

  .items-table {
    page-break-inside: avoid;
  }

  .totals-section {
    page-break-inside: avoid;
  }
}
</style>
