<template>
  <div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
        <div class="mb-4 lg:mb-0">
          <div class="flex items-center gap-4 mb-2">
            <h1 class="text-2xl font-bold text-gray-900">
              {{ isEditMode ? 'Edit Expense' : 'Add New Expense' }}
            </h1>
          </div>
          <p class="text-gray-600">
            {{
              isEditMode
                ? 'Update expense information and details'
                : 'Create a new expense for vendor invoice tracking'
            }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="handleCancel"
            class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors duration-200"
          >
            <svg
              class="w-4 h-4 mr-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M10 19l-7-7m0 0l7-7m-7 7h18"
              />
            </svg>
            Back to Expenses
          </button>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="errors.general"
      class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-800">{{ errors.general }}</p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="bg-white p-6 rounded-lg shadow">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Basic Information Section -->
        <div class="border-b border-gray-200 pb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Basic Information
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vendor Selection -->
            <div>
              <label
                for="vendor_id"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Vendor <span class="text-red-500">*</span>
              </label>
              <select
                id="vendor_id"
                v-model="form.vendor_id"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.vendor_id }"
                required
              >
                <option value="">Select a vendor</option>
                <option
                  v-for="vendor in vendors"
                  :key="vendor.id"
                  :value="vendor.id"
                >
                  {{ vendor.name }}
                </option>
              </select>
              <p v-if="errors.vendor_id" class="mt-1 text-sm text-red-600">
                {{ errors.vendor_id }}
              </p>
            </div>

            <!-- Invoice Number -->
            <div>
              <label
                for="invoice_number"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Invoice Number <span class="text-red-500">*</span>
              </label>
              <input
                id="invoice_number"
                v-model="form.invoice_number"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.invoice_number }"
                placeholder="Enter invoice number"
                required
              />
              <p v-if="errors.invoice_number" class="mt-1 text-sm text-red-600">
                {{ errors.invoice_number }}
              </p>
            </div>

            <!-- Invoice Date -->
            <div>
              <label
                for="invoice_date"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Invoice Date <span class="text-red-500">*</span>
              </label>
              <input
                id="invoice_date"
                v-model="form.invoice_date"
                type="date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.invoice_date }"
                required
              />
              <p v-if="errors.invoice_date" class="mt-1 text-sm text-red-600">
                {{ errors.invoice_date }}
              </p>
            </div>

            <!-- Invoice Due Date -->
            <div>
              <label
                for="invoice_due_date"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Invoice Due Date <span class="text-red-500">*</span>
              </label>
              <input
                id="invoice_due_date"
                v-model="form.invoice_due_date"
                type="date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.invoice_due_date }"
                required
              />
              <p
                v-if="errors.invoice_due_date"
                class="mt-1 text-sm text-red-600"
              >
                {{ errors.invoice_due_date }}
              </p>
            </div>

            <!-- Invoice Amount -->
            <div>
              <label
                for="invoice_amount"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Invoice Amount <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  id="invoice_amount"
                  v-model="form.invoice_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  class="mt-1 block w-full pl-7 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                  :class="{ 'border-red-300': errors.invoice_amount }"
                  placeholder="0.00"
                  required
                />
              </div>
              <p v-if="errors.invoice_amount" class="mt-1 text-sm text-red-600">
                {{ errors.invoice_amount }}
              </p>
            </div>

            <!-- Category -->
            <div>
              <label
                for="category"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Category <span class="text-red-500">*</span>
              </label>
              <select
                id="category"
                v-model="form.category"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.category }"
                required
              >
                <option value="">Select a category</option>
                <option
                  v-for="option in categoryOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <p v-if="errors.category" class="mt-1 text-sm text-red-600">
                {{ errors.category }}
              </p>
            </div>
          </div>

          <!-- Note -->
          <div class="mt-6">
            <label
              for="note"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Note
            </label>
            <textarea
              id="note"
              v-model="form.note"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              placeholder="Additional notes about this expense"
            />
          </div>
        </div>

        <!-- Payment Information Section -->
        <div class="border-b border-gray-200 pb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Payment Information
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Status -->
            <div>
              <label
                for="status"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Status <span class="text-red-500">*</span>
              </label>
              <select
                id="status"
                v-model="form.status"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.status }"
                required
              >
                <option value="">Select status</option>
                <option
                  v-for="option in statusOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <p v-if="errors.status" class="mt-1 text-sm text-red-600">
                {{ errors.status }}
              </p>
            </div>

            <!-- Payment Method (shown when status is partial or paid) -->
            <div v-if="form.status === 'partial' || form.status === 'paid'">
              <label
                for="payment_method"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Payment Method <span class="text-red-500">*</span>
              </label>
              <select
                id="payment_method"
                v-model="form.payment_method"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.payment_method }"
                required
              >
                <option value="">Select payment method</option>
                <option
                  v-for="option in paymentMethodOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <p v-if="errors.payment_method" class="mt-1 text-sm text-red-600">
                {{ errors.payment_method }}
              </p>
            </div>

            <!-- Paid Amount (shown when status is partial) -->
            <div v-if="form.status === 'partial'">
              <label
                for="paid_amount"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Paid Amount <span class="text-red-500">*</span>
              </label>
              <div class="relative">
                <div
                  class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  id="paid_amount"
                  v-model="form.paid_amount"
                  type="number"
                  step="0.01"
                  min="0"
                  :max="form.invoice_amount"
                  class="mt-1 block w-full pl-7 border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                  :class="{ 'border-red-300': errors.paid_amount }"
                  placeholder="0.00"
                  required
                />
              </div>
              <p v-if="errors.paid_amount" class="mt-1 text-sm text-red-600">
                {{ errors.paid_amount }}
              </p>
            </div>

            <!-- Paid Date (shown when status is partial or paid) -->
            <div v-if="form.status === 'partial' || form.status === 'paid'">
              <label
                for="paid_date"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Paid Date <span class="text-red-500">*</span>
              </label>
              <input
                id="paid_date"
                v-model="form.paid_date"
                type="date"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
                :class="{ 'border-red-300': errors.paid_date }"
                required
              />
              <p v-if="errors.paid_date" class="mt-1 text-sm text-red-600">
                {{ errors.paid_date }}
              </p>
            </div>
          </div>

          <!-- Payment Details (shown when status is partial or paid) -->
          <div
            v-if="form.status === 'partial' || form.status === 'paid'"
            class="mt-6"
          >
            <label
              for="payment_details"
              class="block text-sm font-medium text-gray-700 mb-2"
            >
              Payment Details
            </label>
            <textarea
              id="payment_details"
              v-model="form.payment_details"
              rows="3"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary sm:text-sm"
              placeholder="Additional payment details (check number, transaction ID, etc.)"
            />
          </div>
        </div>

        <!-- Document Upload Section -->
        <div class="border-b border-gray-200 pb-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Documents</h3>

          <!-- File Upload -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Upload Documents
            </label>
            <div
              class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors"
            >
              <div class="space-y-1 text-center">
                <svg
                  class="mx-auto h-12 w-12 text-gray-400"
                  stroke="currentColor"
                  fill="none"
                  viewBox="0 0 48 48"
                >
                  <path
                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </svg>
                <div class="flex text-sm text-gray-600">
                  <label
                    for="file-upload"
                    class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary"
                  >
                    <span>Upload files</span>
                    <input
                      id="file-upload"
                      ref="fileInput"
                      type="file"
                      multiple
                      accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif"
                      class="sr-only"
                      @change="handleFileSelect"
                    />
                  </label>
                  <p class="pl-1">or drag and drop</p>
                </div>
                <p class="text-xs text-gray-500">
                  PDF, DOC, XLS, JPG, PNG up to 10MB each
                </p>
              </div>
            </div>
          </div>

          <!-- Uploaded Files List -->
          <div v-if="uploadedFiles.length > 0" class="space-y-2">
            <h4 class="text-sm font-medium text-gray-700">Uploaded Files</h4>
            <div
              v-for="(file, index) in uploadedFiles"
              :key="index"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center">
                <svg
                  class="h-5 w-5 text-gray-400 mr-3"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
                <div>
                  <p class="text-sm font-medium text-gray-900">
                    {{ file.name }}
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ formatFileSize(file.size) }}
                  </p>
                </div>
              </div>
              <button
                type="button"
                @click="removeFile(index)"
                class="text-red-600 hover:text-red-900"
              >
                <svg
                  class="h-4 w-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3">
          <button
            type="button"
            @click="handleCancel"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{
              isSubmitting
                ? 'Saving...'
                : isEditMode
                  ? 'Update Expense'
                  : 'Create Expense'
            }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useQuery } from '@tanstack/vue-query';
import {
  useExpense,
  useCreateExpense,
  useUpdateExpense,
  useUploadAttachment,
} from '../composables/useExpenses';
import { vendorsApi, queryKeys } from '@neibrpay/api-client';
import {
  getExpenseCategoryOptions,
  getExpenseStatusOptions,
  getPaymentMethodOptions,
  type CreateExpenseDto,
  type UpdateExpenseDto,
  type ExpenseStatus,
} from '@neibrpay/models';

const router = useRouter();
const route = useRoute();

// Check if we're in edit mode
const isEditMode = computed(() => !!route.params.id);
const expenseId = computed(() =>
  isEditMode.value ? Number(route.params.id) : null
);

// Form data
const form = ref<CreateExpenseDto & { paid_amount?: number }>({
  vendor_id: 0,
  invoice_number: '',
  invoice_date: '',
  invoice_due_date: '',
  invoice_amount: 0,
  category: 'other',
  note: '',
  status: 'unpaid',
  payment_details: '',
  payment_method: undefined,
  paid_amount: 0,
  paid_date: '',
});

// File upload
const uploadedFiles = ref<File[]>([]);
const fileInput = ref<HTMLInputElement>();

// Form state
const isSubmitting = ref(false);
const errors = ref<Record<string, string>>({});

// Get vendors for dropdown
const { data: vendorsData } = useQuery({
  queryKey: queryKeys.vendors.list({}),
  queryFn: () => vendorsApi.list({}),
  select: (data: any) => data.data,
});

const vendors = computed(() => vendorsData.value || []);

// Get expense data if in edit mode
const { data: expenseData } = useExpense(expenseId.value || 0);

// Options for dropdowns
const categoryOptions = getExpenseCategoryOptions();
const statusOptions = getExpenseStatusOptions();
const paymentMethodOptions = getPaymentMethodOptions();

// Mutations
const createExpenseMutation = useCreateExpense();
const updateExpenseMutation = useUpdateExpense();
const uploadAttachmentMutation = useUploadAttachment();

// Format date for HTML date input (YYYY-MM-DD)
const formatDateForInput = (dateString: string | null | undefined): string => {
  if (!dateString) return '';

  // If it's already in YYYY-MM-DD format, return as is
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    return dateString;
  }

  // Try to parse and format the date
  try {
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return '';

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
  } catch (error) {
    console.error('Error formatting date:', error);
    return '';
  }
};

// Load expense data in edit mode
watch(
  expenseData,
  (expense: any) => {
    if (expense && isEditMode.value) {
      form.value = {
        vendor_id: expense.vendor_id,
        invoice_number: expense.invoice_number,
        invoice_date: formatDateForInput(expense.invoice_date),
        invoice_due_date: formatDateForInput(expense.invoice_due_date),
        invoice_amount: expense.invoice_amount,
        category: expense.category,
        note: expense.note || '',
        status: expense.status,
        payment_details: expense.payment_details || '',
        payment_method: expense.payment_method,
        paid_amount: expense.paid_amount,
        paid_date: formatDateForInput(expense.paid_date) || '',
      };
    }
  },
  { immediate: true }
);

// Watch status changes to auto-fill paid amount
watch(
  () => form.value.status,
  (newStatus: ExpenseStatus) => {
    if (newStatus === 'paid') {
      form.value.paid_amount = form.value.invoice_amount;
    } else if (newStatus === 'unpaid') {
      form.value.paid_amount = 0;
      form.value.payment_method = undefined;
      form.value.paid_date = '';
      form.value.payment_details = '';
    }
  }
);

// File handling
const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    const newFiles = Array.from(target.files);
    uploadedFiles.value.push(...newFiles);
  }
  // Clear the input
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};

const removeFile = (index: number) => {
  uploadedFiles.value.splice(index, 1);
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

// Form validation
const validateForm = (): boolean => {
  errors.value = {};

  if (!form.value.vendor_id) {
    errors.value.vendor_id = 'Vendor is required';
  }

  if (!form.value.invoice_number.trim()) {
    errors.value.invoice_number = 'Invoice number is required';
  }

  if (!form.value.invoice_date) {
    errors.value.invoice_date = 'Invoice date is required';
  }

  if (!form.value.invoice_due_date) {
    errors.value.invoice_due_date = 'Invoice due date is required';
  }

  if (
    form.value.invoice_due_date &&
    form.value.invoice_date &&
    new Date(form.value.invoice_due_date) < new Date(form.value.invoice_date)
  ) {
    errors.value.invoice_due_date = 'Due date must be after invoice date';
  }

  if (!form.value.invoice_amount || form.value.invoice_amount <= 0) {
    errors.value.invoice_amount = 'Invoice amount must be greater than 0';
  }

  if (!form.value.category) {
    errors.value.category = 'Category is required';
  }

  if (!form.value.status) {
    errors.value.status = 'Status is required';
  }

  if (form.value.status === 'partial') {
    if (!form.value.paid_amount || form.value.paid_amount <= 0) {
      errors.value.paid_amount = 'Paid amount is required for partial payments';
    } else if (form.value.paid_amount >= form.value.invoice_amount) {
      errors.value.paid_amount = 'Paid amount must be less than invoice amount';
    }
  }

  if (form.value.status === 'partial' || form.value.status === 'paid') {
    if (!form.value.payment_method) {
      errors.value.payment_method = 'Payment method is required';
    }
    if (!form.value.paid_date) {
      errors.value.paid_date = 'Paid date is required';
    }
  }

  return Object.keys(errors.value).length === 0;
};

// Form submission
const handleSubmit = async () => {
  if (!validateForm()) {
    return;
  }

  isSubmitting.value = true;
  errors.value = {};

  try {
    // Prepare form data
    const formData = { ...form.value };

    // Remove undefined values
    Object.keys(formData).forEach(key => {
      if (formData[key as keyof typeof formData] === undefined) {
        delete formData[key as keyof typeof formData];
      }
    });

    let expense;
    if (isEditMode.value) {
      expense = await updateExpenseMutation.mutateAsync({
        id: expenseId.value!,
        data: formData as UpdateExpenseDto,
      });
    } else {
      expense = await createExpenseMutation.mutateAsync(
        formData as CreateExpenseDto
      );
    }

    // Upload files if any
    if (uploadedFiles.value.length > 0 && expense) {
      for (const file of uploadedFiles.value) {
        try {
          await uploadAttachmentMutation.mutateAsync({
            expenseId: expense.id,
            file,
          });
        } catch (error) {
          console.error('Failed to upload file:', file.name, error);
          // Continue with other files even if one fails
        }
      }
    }

    // Redirect to expenses list
    router.push('/expenses');
  } catch (error: any) {
    console.error('Error saving expense:', error);
    errors.value.general = error.message || 'Failed to save expense';
  } finally {
    isSubmitting.value = false;
  }
};

const handleCancel = () => {
  router.push('/expenses');
};

// Set default dates
onMounted(() => {
  if (!isEditMode.value) {
    const today = new Date().toISOString().split('T')[0];
    form.value.invoice_date = today;
    form.value.invoice_due_date = today;
  }
});
</script>
