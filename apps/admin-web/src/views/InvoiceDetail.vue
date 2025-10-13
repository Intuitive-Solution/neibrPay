<template>
  <div class="max-w-7xl mx-auto bg-white p-6">
    <!-- Success Message -->
    <div
      v-if="showSuccessMessage"
      class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg"
    >
      <div class="flex">
        <div class="flex-shrink-0">
          <svg
            class="h-5 w-5 text-green-400"
            viewBox="0 0 20 20"
            fill="currentColor"
          >
            <path
              fill-rule="evenodd"
              d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
              clip-rule="evenodd"
            />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-green-800">{{ successMessage }}</p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button
              @click="showSuccessMessage = false"
              class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600"
            >
              <span class="sr-only">Dismiss</span>
              <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <div
      v-if="showErrorMessage"
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
          <p class="text-sm text-red-800">{{ errorMessage }}</p>
        </div>
        <div class="ml-auto pl-3">
          <div class="-mx-1.5 -my-1.5">
            <button
              @click="showErrorMessage = false"
              class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600"
            >
              <span class="sr-only">Dismiss</span>
              <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                <path
                  fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-12">
      <div
        class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"
      ></div>
      <span class="ml-3 text-gray-600">Loading invoice...</span>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <div class="text-red-600 mb-4">
        <svg
          class="mx-auto h-12 w-12"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
          />
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">
        Error Loading Invoice
      </h3>
      <p class="text-gray-600 mb-4">
        {{ error.message || 'Failed to load invoice details' }}
      </p>
      <button
        @click="router.push('/invoices')"
        class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-600"
      >
        Back to Invoices
      </button>
    </div>

    <!-- Invoice Details Content -->
    <div v-else-if="invoice" class="space-y-6">
      <!-- Header Section -->
      <div class="bg-white rounded-lg shadow p-6">
        <div
          class="flex flex-col lg:flex-row lg:items-start lg:justify-between"
        >
          <div class="mb-4 lg:mb-0">
            <div class="flex items-center gap-4 mb-2">
              <h1 class="text-2xl font-bold text-gray-900">
                Invoice #{{ invoice.invoice_number }}
              </h1>
              <!-- Enhanced Status Badge -->
              <span
                :class="getStatusBadgeClass(invoice.status)"
                class="inline-flex items-center px-4 py-2 rounded-lg text-base font-semibold shadow-sm"
              >
                <svg
                  v-if="invoice.status === 'paid'"
                  class="w-5 h-5 mr-2"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd"
                  />
                </svg>
                <svg
                  v-else-if="invoice.status === 'sent'"
                  class="w-5 h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                  />
                </svg>
                <svg
                  v-else-if="invoice.status === 'overdue'"
                  class="w-5 h-5 mr-2"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                  />
                </svg>
                <svg
                  v-else-if="invoice.status === 'cancelled'"
                  class="w-5 h-5 mr-2"
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
                <svg
                  v-else
                  class="w-5 h-5 mr-2"
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
                {{ getStatusText(invoice.status) }}
              </span>
            </div>
            <p class="text-gray-600">
              {{ invoice.unit?.title }} • {{ formatDate(invoice.created_at) }}
            </p>
          </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap gap-3 mt-6">
          <button
            @click="emailInvoice"
            :disabled="isEmailing || invoice.status === 'paid'"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
            {{ isEmailing ? 'Sending...' : 'Email Invoice' }}
          </button>

          <button
            @click="cloneInvoice"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors duration-200"
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
                d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
              />
            </svg>
            Clone
          </button>

          <button
            @click="editInvoice"
            :disabled="invoice.status === 'paid'"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
            v-if="invoice.status !== 'paid'"
            @click="markAsPaid"
            :disabled="isMarkingPaid"
            class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            {{ isMarkingPaid ? 'Marking...' : 'Mark Paid' }}
          </button>

          <button
            @click="deleteInvoice"
            :disabled="isDeleting || invoice.status === 'paid'"
            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
            {{ isDeleting ? 'Deleting...' : 'Delete' }}
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
            Cancel
          </button>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Invoice Information -->
        <div class="bg-white rounded-lg shadow">
          <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
            <h3 class="text-lg font-medium text-gray-900">Invoice Details</h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700"
                >Invoice Number</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ invoice.invoice_number }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700"
                >Invoice Date</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ formatDate(invoice.created_at) }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700"
                >Due Date</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ getDueDate() }}
              </p>
            </div>
            <div v-if="invoice.po_number">
              <label class="block text-sm font-medium text-gray-700"
                >PO Number</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ invoice.po_number }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700"
                >Frequency</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ formatFrequency(invoice.frequency) }}
              </p>
            </div>
            <div v-if="invoice.remaining_cycles">
              <label class="block text-sm font-medium text-gray-700"
                >Remaining Cycles</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ invoice.remaining_cycles }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700"
                >Start Date</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ formatDate(invoice.start_date) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Middle Column - Unit & Owner Information -->
        <div class="bg-white rounded-lg shadow">
          <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
            <h3 class="text-lg font-medium text-gray-900">Unit & Owner</h3>
          </div>
          <div class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700"
                >Unit</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ invoice.unit?.title }}
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700"
                >Address</label
              >
              <p
                class="mt-1 text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded"
              >
                {{ invoice.unit?.address }}<br />
                {{ invoice.unit?.city }}, {{ invoice.unit?.state }}
                {{ invoice.unit?.zip_code }}
              </p>
            </div>
            <div v-if="invoice.unit?.owners && invoice.unit.owners.length > 0">
              <label class="block text-sm font-medium text-gray-700"
                >Owner(s)</label
              >
              <div class="mt-1 space-y-2">
                <div
                  v-for="owner in invoice.unit.owners"
                  :key="owner.id"
                  class="bg-gray-50 px-3 py-2 rounded"
                >
                  <p class="text-sm text-gray-900 font-medium">
                    {{ owner.name }}
                  </p>
                  <p class="text-sm text-gray-600">{{ owner.email }}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Financial Summary -->
        <div class="bg-white rounded-lg shadow">
          <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
            <h3 class="text-lg font-medium text-gray-900">Financial Summary</h3>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Subtotal</span>
              <span class="text-sm font-medium text-gray-900"
                >${{ formatCurrency(invoice.subtotal) }}</span
              >
            </div>
            <div
              v-if="invoice.discount_amount > 0"
              class="flex justify-between"
            >
              <span class="text-sm text-gray-600">Discount</span>
              <span class="text-sm font-medium text-gray-900">
                ${{ formatCurrency(invoice.discount_amount) }}
                <span class="text-xs text-gray-500"
                  >({{ invoice.discount_type }})</span
                >
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600"
                >Tax ({{ invoice.tax_rate }}%)</span
              >
              <span class="text-sm font-medium text-gray-900"
                >${{ formatCurrency(invoice.tax_amount) }}</span
              >
            </div>
            <div class="border-t border-gray-200 pt-3">
              <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-900">Total</span>
                <span class="text-sm font-bold text-gray-900"
                  >${{ formatCurrency(invoice.total) }}</span
                >
              </div>
            </div>
            <div class="flex justify-between">
              <span class="text-sm text-gray-600">Paid to Date</span>
              <span class="text-sm font-medium text-gray-900"
                >${{ formatCurrency(invoice.paid_to_date) }}</span
              >
            </div>
            <div class="border-t border-gray-200 pt-3">
              <div class="flex justify-between">
                <span class="text-sm font-medium text-gray-900"
                  >Balance Due</span
                >
                <span class="text-sm font-bold text-gray-900"
                  >${{ formatCurrency(invoice.balance_due) }}</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Documents and Activity Tabs Section -->
      <div class="bg-white rounded-lg shadow">
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Invoice Details</h3>
          </div>
        </div>

        <!-- Tab Navigation -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
            <button
              @click="activeTab = 'documents'"
              :class="[
                activeTab === 'documents'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
              ]"
            >
              Documents
            </button>
            <button
              @click="activeTab = 'activity'"
              :class="[
                activeTab === 'activity'
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
              ]"
            >
              Activity
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Documents Tab -->
          <div v-if="activeTab === 'documents'">
            <div v-if="isLoadingAttachments" class="text-center py-8">
              <div
                class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"
              ></div>
              <p class="mt-2 text-sm text-gray-600">Loading documents...</p>
            </div>

            <div
              v-else-if="attachmentsError"
              class="text-center py-8 text-red-500"
            >
              <svg
                class="mx-auto h-12 w-12 text-red-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">
                Error Loading Documents
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                Failed to load invoice documents
              </p>
            </div>

            <div
              v-else-if="!attachments || attachments.length === 0"
              class="text-center py-8 text-gray-500"
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
              <h3 class="mt-2 text-sm font-medium text-gray-900">
                No Documents
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                No documents have been uploaded for this invoice yet.
              </p>
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Name
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Type
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Size
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Upload Date
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Uploaded By
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="attachment in attachments"
                    :key="attachment.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="flex items-center">
                        <span class="text-lg mr-3">{{
                          getFileIcon(attachment.attachment_type)
                        }}</span>
                        <div class="text-sm font-medium text-gray-900">
                          {{ attachment.file_name }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm text-gray-900 capitalize">
                        {{ attachment.attachment_type }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ attachment.mime_type }}
                      </div>
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                    >
                      {{ formatFileSize(attachment.file_size) }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                    >
                      {{ formatDate(attachment.created_at) }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                    >
                      {{
                        attachment.uploader?.name ||
                        attachment.uploader?.email ||
                        'Unknown'
                      }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <button
                        @click="handleDownload(attachment)"
                        :disabled="downloadAttachmentMutation.isPending.value"
                        class="inline-flex items-center px-3 py-1 border border-transparent text-sm leading-4 font-medium rounded-md text-green-700 bg-green-100 hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
                      >
                        <svg
                          class="w-4 h-4 mr-1"
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
                        {{
                          downloadAttachmentMutation.isPending.value
                            ? 'Downloading...'
                            : 'Download'
                        }}
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Activity Tab -->
          <div
            v-if="activeTab === 'activity'"
            class="text-center py-12 text-gray-500"
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
                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              Activity Tracking Coming Soon
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              Invoice activity and history will be displayed here.
            </p>
          </div>
        </div>
      </div>

      <!-- Invoice PDF Section -->
      <div class="bg-white rounded-lg shadow">
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Invoice PDF</h3>
          </div>
        </div>
        <div class="p-6">
          <div v-if="latestPdf" class="space-y-4">
            <!-- Embedded PDF Viewer -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
              <iframe
                :key="pdfRefreshKey"
                :src="pdfViewerUrl"
                class="w-full h-96"
                frameborder="0"
                title="Invoice PDF Viewer"
                @load="onPdfLoad"
                @error="onPdfError"
              ></iframe>
            </div>

            <!-- Fallback message if PDF doesn't load -->
            <div v-if="pdfLoadError" class="text-center py-8 text-gray-500">
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
                  d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                />
              </svg>
              <h3 class="mt-2 text-sm font-medium text-gray-900">
                PDF Preview Unavailable
              </h3>
              <p class="mt-1 text-sm text-gray-500">
                Unable to display PDF preview. Use the download button to view
                the PDF.
              </p>
            </div>
          </div>
          <div v-else-if="isLoadingPdf" class="text-center py-8">
            <div
              class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"
            ></div>
            <p class="mt-2 text-sm text-gray-600">Loading PDF information...</p>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
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
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              No PDF Available
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              No PDF has been generated for this invoice yet.
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showDeleteModal"
      title="Delete Invoice"
      :message="`Are you sure you want to delete invoice #${invoice?.invoice_number}? This action can be undone by restoring the invoice.`"
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
import { useRoute, useRouter } from 'vue-router';
import {
  useInvoice,
  useDeleteInvoice,
  useMarkInvoiceAsPaid,
  useEmailInvoice,
} from '../composables/useInvoices';
import { useLatestInvoicePdf } from '../composables/useInvoicePdf';
import {
  useInvoiceAttachments,
  useDownloadInvoiceAttachment,
} from '../composables/useInvoiceAttachments';
import ConfirmDialog from '../components/ConfirmDialog.vue';

const route = useRoute();
const router = useRouter();

// Get invoice ID from route params
const invoiceId = computed(() => parseInt(route.params.id as string));

// Fetch invoice data
const { data: invoice, isLoading, error } = useInvoice(invoiceId.value);

// Fetch PDF data
const { data: latestPdf, isLoading: isLoadingPdf } = useLatestInvoicePdf(
  invoiceId.value
);

// Fetch invoice attachments
const {
  data: attachments,
  isLoading: isLoadingAttachments,
  error: attachmentsError,
} = useInvoiceAttachments(invoiceId.value);

// Mutations
const deleteInvoiceMutation = useDeleteInvoice();
const markAsPaidMutation = useMarkInvoiceAsPaid();
const emailInvoiceMutation = useEmailInvoice();
const downloadAttachmentMutation = useDownloadInvoiceAttachment();

// Loading states
const isDeleting = computed(() => deleteInvoiceMutation.isPending.value);
const isMarkingPaid = computed(() => markAsPaidMutation.isPending.value);
const isEmailing = computed(() => emailInvoiceMutation.isPending.value);
const showDeleteModal = ref(false);

// Success/Error messages
const successMessage = ref('');
const errorMessage = ref('');
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const pdfLoadError = ref(false);
const pdfRefreshKey = ref(0);

// Tab state
const activeTab = ref('documents');

const pdfViewerUrl = computed(() => {
  if (!latestPdf.value?.file_path) return '';
  // Use direct storage path to the PDF file from Laravel backend
  const baseUrl =
    (import.meta as any).env?.VITE_API_BASE_URL || 'http://localhost:8000/api';
  const backendUrl = baseUrl.replace('/api', '');
  // Add refresh key to force iframe reload when PDF is updated
  return `${backendUrl}/storage/${latestPdf.value.file_path}?t=${pdfRefreshKey.value}`;
});

// Methods
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

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    sent: 'Sent',
    paid: 'Paid',
    overdue: 'Overdue',
    cancelled: 'Cancelled',
  };
  return statusMap[status] || 'Unknown';
};

const getStatusBadgeClass = (status: string) => {
  const statusClasses: Record<string, string> = {
    draft: 'bg-gray-100 text-gray-800 border border-gray-200',
    sent: 'bg-blue-100 text-blue-800 border border-blue-200',
    paid: 'bg-green-100 text-green-800 border border-green-200',
    overdue: 'bg-red-100 text-red-800 border border-red-200',
    cancelled: 'bg-yellow-100 text-yellow-800 border border-yellow-200',
  };
  return (
    statusClasses[status] || 'bg-gray-100 text-gray-800 border border-gray-200'
  );
};

const formatFrequency = (frequency: string) => {
  const frequencyMap: Record<string, string> = {
    'one-time': 'One Time',
    monthly: 'Monthly',
    weekly: 'Weekly',
    quarterly: 'Quarterly',
    yearly: 'Yearly',
  };
  return frequencyMap[frequency] || frequency;
};

const getDueDate = () => {
  if (!invoice.value) return 'N/A';

  // Calculate due date based on due_date setting
  const startDate = new Date(invoice.value.start_date);
  let dueDate = new Date(startDate);

  switch (invoice.value.due_date) {
    case 'net_15':
      dueDate.setDate(startDate.getDate() + 15);
      break;
    case 'net_30':
      dueDate.setDate(startDate.getDate() + 30);
      break;
    case 'net_45':
      dueDate.setDate(startDate.getDate() + 45);
      break;
    case 'net_60':
      dueDate.setDate(startDate.getDate() + 60);
      break;
    case 'due_on_receipt':
      return 'Due on Receipt';
    case 'use_payment_terms':
    default:
      return 'Use Payment Terms';
  }

  return formatDate(dueDate.toISOString());
};

// Action handlers
const emailInvoice = async () => {
  if (!invoice.value) return;

  try {
    await emailInvoiceMutation.mutateAsync({
      id: invoice.value.id,
      email: invoice.value.unit?.owners?.[0]?.email,
    });
    showSuccess('Invoice email sent successfully!');
  } catch (error: any) {
    console.error('Error sending email:', error);
    showError(error.message || 'Failed to send email');
  }
};

const cloneInvoice = () => {
  if (!invoice.value) return;

  // Extract notes by type
  const publicNotes =
    invoice.value.notes?.find((n: any) => n.type === 'public_notes')?.content ||
    '';
  const terms =
    invoice.value.notes?.find((n: any) => n.type === 'terms')?.content || '';
  const footer =
    invoice.value.notes?.find((n: any) => n.type === 'footer')?.content || '';

  // Store cloned data in sessionStorage for persistence across navigation
  const clonedData = {
    frequency: invoice.value.frequency,
    remaining_cycles: invoice.value.remaining_cycles,
    start_date: invoice.value.start_date,
    due_date: invoice.value.due_date,
    items: invoice.value.items,
    tax_rate: invoice.value.tax_rate,
    public_notes: publicNotes,
    terms: terms,
    footer: footer,
  };

  sessionStorage.setItem('clonedInvoice', JSON.stringify(clonedData));

  // Navigate to create invoice
  router.push('/invoices/create');
};

const editInvoice = () => {
  if (!invoice.value) return;
  router.push(`/invoices/${invoice.value.id}/edit`);
};

const markAsPaid = async () => {
  if (!invoice.value) return;

  try {
    await markAsPaidMutation.mutateAsync(invoice.value.id);

    // Force refresh of PDF viewer by updating the refresh key
    pdfRefreshKey.value = Date.now();

    showSuccess('Invoice marked as paid successfully!');
  } catch (error: any) {
    console.error('Error marking as paid:', error);
    showError(error.message || 'Failed to mark as paid');
  }
};

const deleteInvoice = () => {
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!invoice.value) return;

  try {
    await deleteInvoiceMutation.mutateAsync(invoice.value.id);
    showSuccess('Invoice deleted successfully!');
    router.push('/invoices');
  } catch (error: any) {
    console.error('Error deleting invoice:', error);
    showError(error.message || 'Failed to delete invoice');
  } finally {
    showDeleteModal.value = false;
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
};

const goBack = () => {
  router.push('/invoices');
};

// PDF iframe event handlers
const onPdfLoad = () => {
  pdfLoadError.value = false;
};

const onPdfError = () => {
  pdfLoadError.value = true;
};

// Helper functions for showing messages
const showSuccess = (message: string) => {
  successMessage.value = message;
  showSuccessMessage.value = true;
  setTimeout(() => {
    showSuccessMessage.value = false;
  }, 5000);
};

const showError = (message: string) => {
  errorMessage.value = message;
  showErrorMessage.value = true;
  setTimeout(() => {
    showErrorMessage.value = false;
  }, 5000);
};

// Helper functions for file handling
const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
};

const getFileIcon = (attachmentType: string): string => {
  const iconMap: Record<string, string> = {
    pdf: '📄',
    image: '🖼️',
    document: '📝',
    other: '📎',
  };
  return iconMap[attachmentType] || '📎';
};

const handleDownload = async (attachment: any) => {
  try {
    await downloadAttachmentMutation.mutateAsync({
      invoiceId: invoiceId.value,
      attachmentId: attachment.id,
      fileName: attachment.file_name,
    });
  } catch (error: any) {
    console.error('Error downloading attachment:', error);
    showError(error.message || 'Failed to download file');
  }
};
</script>
