<template>
  <div class="max-w-6xl mx-auto">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
        <div class="mb-4 lg:mb-0">
          <div class="flex items-center gap-4 mb-2">
            <h1 class="text-2xl font-bold text-gray-900">Expense Details</h1>
            <span
              :class="getExpenseStatusBadgeClass(expense?.status || 'unpaid')"
              class="inline-flex px-3 py-1 text-sm font-semibold rounded-full"
            >
              {{ getExpenseStatusDisplayName(expense?.status || 'unpaid') }}
            </span>
          </div>
          <p class="text-gray-600">
            Invoice #{{ expense?.invoice_number }} - {{ expense?.vendor?.name }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="editExpense"
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
                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"
              />
            </svg>
            Edit
          </button>
          <button
            @click="deleteExpense"
            class="inline-flex items-center px-4 py-2 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition-colors duration-200"
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
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
              />
            </svg>
            Delete
          </button>
          <button
            @click="goBack"
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

    <!-- Loading State -->
    <div v-if="isLoading" class="flex justify-center py-12">
      <div
        class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
      ></div>
    </div>

    <!-- Error State -->
    <div
      v-else-if="error"
      class="bg-red-50 border border-red-200 rounded-md p-4"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-red-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">
            Error loading expense
          </h3>
          <div class="mt-2 text-sm text-red-700">
            {{ error }}
          </div>
        </div>
      </div>
    </div>

    <!-- Expense Details -->
    <div v-else-if="expense" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Basic Information Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Basic Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Invoice Number</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ expense.invoice_number }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Invoice Date</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ formatDate(expense.invoice_date) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Due Date</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ formatDate(expense.invoice_due_date) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Category</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ getExpenseCategoryDisplayName(expense.category) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Invoice Amount</label
              >
              <p class="mt-1 text-sm font-medium text-gray-900">
                ${{ formatCurrency(expense.invoice_amount) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Balance Due</label
              >
              <p class="mt-1 text-sm font-medium text-gray-900">
                ${{
                  formatCurrency(expense.invoice_amount - expense.paid_amount)
                }}
              </p>
            </div>
          </div>
          <div v-if="expense.note" class="mt-6">
            <label class="block text-sm font-medium text-gray-500">Note</label>
            <p class="mt-1 text-sm text-gray-900">{{ expense.note }}</p>
          </div>
        </div>

        <!-- Payment Information Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Payment Information
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Status</label
              >
              <span
                :class="getExpenseStatusBadgeClass(expense.status)"
                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full mt-1"
              >
                {{ getExpenseStatusDisplayName(expense.status) }}
              </span>
            </div>
            <div v-if="expense.payment_method">
              <label class="block text-sm font-medium text-gray-500"
                >Payment Method</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ getPaymentMethodDisplayName(expense.payment_method) }}
              </p>
            </div>
            <div v-if="expense.paid_amount > 0">
              <label class="block text-sm font-medium text-gray-500"
                >Paid Amount</label
              >
              <p class="mt-1 text-sm font-medium text-gray-900">
                ${{ formatCurrency(expense.paid_amount) }}
              </p>
            </div>
            <div v-if="expense.paid_date">
              <label class="block text-sm font-medium text-gray-500"
                >Paid Date</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ formatDate(expense.paid_date) }}
              </p>
            </div>
          </div>
          <div v-if="expense.payment_details" class="mt-6">
            <label class="block text-sm font-medium text-gray-500"
              >Payment Details</label
            >
            <p class="mt-1 text-sm text-gray-900">
              {{ expense.payment_details }}
            </p>
          </div>
        </div>

        <!-- Documents Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Documents</h3>
            <button
              @click="showUploadModal = true"
              class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
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
                  d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                />
              </svg>
              Upload Document
            </button>
          </div>

          <!-- Loading State -->
          <div v-if="isLoadingAttachments" class="flex justify-center py-8">
            <div
              class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"
            ></div>
          </div>

          <!-- Empty State -->
          <div
            v-else-if="!attachments || attachments.length === 0"
            class="text-center py-8"
          >
            <svg
              class="mx-auto h-12 w-12 text-gray-400"
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">No documents</h3>
            <p class="mt-1 text-sm text-gray-500">
              Get started by uploading a document.
            </p>
          </div>

          <!-- Documents List -->
          <div v-else class="space-y-3">
            <div
              v-for="attachment in attachments"
              :key="attachment.id"
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
                    {{ attachment.file_name }}
                  </p>
                  <p class="text-xs text-gray-500">
                    {{ formatFileSize(attachment.file_size) }} •
                    {{ formatDate(attachment.created_at) }}
                  </p>
                </div>
              </div>
              <div class="flex items-center space-x-2">
                <button
                  @click="downloadAttachment(attachment)"
                  class="text-primary hover:text-primary-dark"
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
                      d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                  </svg>
                </button>
                <button
                  @click="deleteAttachment(attachment)"
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
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sidebar -->
      <div class="space-y-6">
        <!-- Vendor Information Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Vendor Information
          </h3>
          <div v-if="expense.vendor" class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Name</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ expense.vendor.name }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Category</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ getVendorCategoryDisplayName(expense.vendor.category) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Contact</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ expense.vendor.contact_name }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Email</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ expense.vendor.contact_email }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Phone</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ expense.vendor.contact_phone }}
              </p>
            </div>
          </div>
          <div v-else class="text-sm text-gray-500">
            No vendor information available
          </div>
        </div>

        <!-- Expense Summary Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Expense Summary
          </h3>
          <div class="space-y-3">
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">Invoice Amount</span>
              <span class="text-sm font-medium text-gray-900"
                >${{ formatCurrency(expense.invoice_amount) }}</span
              >
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-500">Paid Amount</span>
              <span class="text-sm font-medium text-gray-900"
                >${{ formatCurrency(expense.paid_amount) }}</span
              >
            </div>
            <div class="border-t pt-3">
              <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-900"
                  >Balance Due</span
                >
                <span class="text-sm font-medium text-gray-900"
                  >${{
                    formatCurrency(expense.invoice_amount - expense.paid_amount)
                  }}</span
                >
              </div>
            </div>
          </div>
        </div>

        <!-- Created Information Card -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Created Information
          </h3>
          <div class="space-y-3">
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Created By</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ expense.creator?.name || 'Unknown' }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500"
                >Created Date</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ formatDate(expense.created_at) }}
              </p>
            </div>
            <div v-if="expense.updated_at !== expense.created_at">
              <label class="block text-sm font-medium text-gray-500"
                >Last Updated</label
              >
              <p class="mt-1 text-sm text-gray-900">
                {{ formatDate(expense.updated_at) }}
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Upload Modal -->
    <div
      v-if="showUploadModal"
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
      @click="showUploadModal = false"
    >
      <div
        class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white"
        @click.stop
      >
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Upload Document
          </h3>
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
                  for="file-upload-modal"
                  class="relative cursor-pointer bg-white rounded-md font-medium text-primary hover:text-primary-dark focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-primary"
                >
                  <span>Upload files</span>
                  <input
                    id="file-upload-modal"
                    ref="fileInputModal"
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
          <div class="mt-4 flex justify-end space-x-3">
            <button
              @click="showUploadModal = false"
              class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
            >
              Cancel
            </button>
            <button
              @click="uploadFiles"
              :disabled="selectedFiles.length === 0 || isUploading"
              class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-dark disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ isUploading ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showDeleteModal"
      title="Delete Expense"
      :message="`Are you sure you want to delete ${expense?.invoice_number || 'this expense'}? This action can be undone by restoring the expense.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="isDeleting"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import {
  useExpense,
  useDeleteExpense,
  useExpenseAttachments,
  useUploadAttachment,
  useDeleteAttachment,
} from '../composables/useExpenses';
import { expensesApi } from '@neibrpay/api-client';
import {
  getExpenseCategoryDisplayName,
  getExpenseStatusDisplayName,
  getExpenseStatusBadgeClass,
  getPaymentMethodDisplayName,
  getVendorCategoryDisplayName,
} from '@neibrpay/models';
import ConfirmDialog from '../components/ConfirmDialog.vue';

const router = useRouter();
const route = useRoute();

// Get expense ID from route
const expenseId = computed(() => Number(route.params.id));

// Local state
const showUploadModal = ref(false);
const showDeleteModal = ref(false);
const selectedFiles = ref<File[]>([]);
const isUploading = ref(false);
const isDeleting = ref(false);
const fileInputModal = ref<HTMLInputElement>();

// Queries
const { data: expense, isLoading, error } = useExpense(expenseId.value);
const { data: attachments, isLoading: isLoadingAttachments } =
  useExpenseAttachments(expenseId.value);

// Mutations
const deleteExpenseMutation = useDeleteExpense();
const uploadAttachmentMutation = useUploadAttachment();
const deleteAttachmentMutation = useDeleteAttachment();

// Methods
const goBack = () => {
  router.push('/expenses');
};

const editExpense = () => {
  router.push(`/expenses/${expenseId.value}/edit`);
};

const deleteExpense = () => {
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!expense.value) return;

  isDeleting.value = true;
  try {
    await deleteExpenseMutation.mutateAsync(expenseId.value);
    router.push('/expenses');
  } catch (error) {
    console.error('Error deleting expense:', error);
    alert('Failed to delete expense');
  } finally {
    isDeleting.value = false;
    showDeleteModal.value = false;
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
};

const handleFileSelect = (event: Event) => {
  const target = event.target as HTMLInputElement;
  if (target.files) {
    selectedFiles.value = Array.from(target.files);
  }
};

const uploadFiles = async () => {
  if (selectedFiles.value.length === 0) return;

  isUploading.value = true;
  try {
    for (const file of selectedFiles.value) {
      await uploadAttachmentMutation.mutateAsync({
        expenseId: expenseId.value,
        file,
      });
    }
    showUploadModal.value = false;
    selectedFiles.value = [];
    if (fileInputModal.value) {
      fileInputModal.value.value = '';
    }
  } catch (error) {
    console.error('Error uploading files:', error);
    alert('Failed to upload files');
  } finally {
    isUploading.value = false;
  }
};

const downloadAttachment = async (attachment: any) => {
  try {
    const blob = await expensesApi.downloadAttachment(
      expenseId.value,
      attachment.id
    );
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = attachment.file_name;
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);
  } catch (error) {
    console.error('Error downloading attachment:', error);
    alert('Failed to download file');
  }
};

const deleteAttachment = async (attachment: any) => {
  if (confirm('Are you sure you want to delete this document?')) {
    try {
      await deleteAttachmentMutation.mutateAsync({
        expenseId: expenseId.value,
        attachmentId: attachment.id,
      });
    } catch (error) {
      console.error('Error deleting attachment:', error);
      alert('Failed to delete document');
    }
  }
};

const formatDate = (dateString: string) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatCurrency = (amount: number | string) => {
  if (amount === null || amount === undefined) return '0.00';
  const numAmount = typeof amount === 'string' ? parseFloat(amount) : amount;
  return numAmount.toFixed(2);
};

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};
</script>
