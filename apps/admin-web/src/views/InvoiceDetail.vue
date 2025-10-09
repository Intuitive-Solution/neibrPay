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
          class="flex flex-col lg:flex-row lg:items-center lg:justify-between"
        >
          <div class="mb-4 lg:mb-0">
            <h1 class="text-2xl font-bold text-gray-900">
              Invoice #{{ invoice.invoice_number }}
            </h1>
            <p class="text-gray-600 mt-1">
              {{ invoice.unit?.title }} • {{ formatDate(invoice.created_at) }}
            </p>
          </div>

          <!-- Status Badge -->
          <div class="mb-4 lg:mb-0">
            <span
              :class="getStatusBadgeClass(invoice.status)"
              class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
            >
              {{ getStatusText(invoice.status) }}
            </span>
          </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="flex flex-wrap gap-3 mt-6">
          <button
            @click="emailInvoice"
            :disabled="isEmailing"
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
            :disabled="isDeleting"
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

      <!-- Invoice Items Table -->
      <div class="bg-white rounded-lg shadow">
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Invoice Items</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Item
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Description
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Unit Cost
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Quantity
                </th>
                <th
                  class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Line Total
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="(item, index) in invoice.items"
                :key="index"
                class="hover:bg-gray-50"
              >
                <td
                  class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                >
                  {{ item.name }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ item.description || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  ${{ formatCurrency(item.unit_cost) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ item.quantity }}
                </td>
                <td
                  class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right"
                >
                  ${{ formatCurrency(item.line_total) }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Documents/Attachments Section -->
      <div
        v-if="attachments && attachments.length > 0"
        class="bg-white rounded-lg shadow"
      >
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Attachments</h3>
        </div>
        <div class="p-6">
          <div class="space-y-3">
            <div
              v-for="attachment in attachments"
              :key="attachment.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                  <svg
                    v-if="attachment.attachment_type === 'pdf'"
                    class="h-8 w-8 text-red-500"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  <svg
                    v-else-if="attachment.attachment_type === 'image'"
                    class="h-8 w-8 text-green-500"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                      clip-rule="evenodd"
                    />
                  </svg>
                  <svg
                    v-else
                    class="h-8 w-8 text-blue-500"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <div class="flex-1 min-w-0">
                  <p class="text-sm font-medium text-gray-900 truncate">
                    {{ attachment.file_name }}
                  </p>
                  <p class="text-sm text-gray-500">
                    {{ formatFileSize(attachment.file_size) }} •
                    {{ attachment.attachment_type.toUpperCase() }}
                  </p>
                </div>
              </div>
              <button
                @click="downloadAttachment"
                class="text-blue-600 hover:text-blue-900 text-sm font-medium"
              >
                Download
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Notes Sections -->
      <div v-if="hasNotes" class="bg-white rounded-lg shadow">
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Notes</h3>
        </div>
        <div class="p-6">
          <div class="space-y-6">
            <div v-if="getNoteContent('public_notes')">
              <h4 class="text-sm font-medium text-gray-900 mb-2">
                Public Notes
              </h4>
              <div
                class="bg-gray-50 p-4 rounded-lg"
                v-html="getNoteContent('public_notes')"
              ></div>
            </div>
            <div v-if="getNoteContent('private_notes')">
              <h4 class="text-sm font-medium text-gray-900 mb-2">
                Private Notes
              </h4>
              <div
                class="bg-gray-50 p-4 rounded-lg"
                v-html="getNoteContent('private_notes')"
              ></div>
            </div>
            <div v-if="getNoteContent('terms')">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Terms</h4>
              <div
                class="bg-gray-50 p-4 rounded-lg"
                v-html="getNoteContent('terms')"
              ></div>
            </div>
            <div v-if="getNoteContent('footer')">
              <h4 class="text-sm font-medium text-gray-900 mb-2">Footer</h4>
              <div
                class="bg-gray-50 p-4 rounded-lg"
                v-html="getNoteContent('footer')"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment History -->
      <div
        v-if="invoice.payments && invoice.payments.length > 0"
        class="bg-white rounded-lg shadow"
      >
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Payment History</h3>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Date
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Amount
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Method
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Reference
                </th>
                <th
                  class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                >
                  Recorded By
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="payment in invoice.payments"
                :key="payment.id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(payment.payment_date) }}
                </td>
                <td
                  class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                >
                  ${{ formatCurrency(payment.amount) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatPaymentMethod(payment.payment_method) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ payment.payment_reference || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ payment.recorded_by }}
                </td>
              </tr>
            </tbody>
          </table>
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
  useCloneInvoice,
  useEmailInvoice,
} from '../composables/useInvoices';
import { useInvoiceAttachments } from '../composables/useInvoiceAttachments';
import ConfirmDialog from '../components/ConfirmDialog.vue';

const route = useRoute();
const router = useRouter();

// Get invoice ID from route params
const invoiceId = computed(() => parseInt(route.params.id as string));

// Fetch invoice data
const { data: invoice, isLoading, error } = useInvoice(invoiceId.value);

// Fetch attachments
const { data: attachments } = useInvoiceAttachments(invoiceId);

// Mutations
const deleteInvoiceMutation = useDeleteInvoice();
const markAsPaidMutation = useMarkInvoiceAsPaid();
const cloneInvoiceMutation = useCloneInvoice();
const emailInvoiceMutation = useEmailInvoice();

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

// Computed properties
const hasNotes = computed(() => {
  if (!invoice.value?.notes) return false;
  return invoice.value.notes.some(
    (note: any) => note.content && note.content.trim() !== ''
  );
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

const formatFileSize = (bytes: number): string => {
  if (bytes === 0) return '0 Bytes';
  const k = 1024;
  const sizes = ['Bytes', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
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
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-yellow-100 text-yellow-800',
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
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

const getNoteContent = (type: string) => {
  if (!invoice.value?.notes) return '';
  const note = invoice.value.notes.find((n: any) => n.type === type);
  return note?.content || '';
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

const cloneInvoice = async () => {
  if (!invoice.value) return;

  try {
    await cloneInvoiceMutation.mutateAsync(invoice.value.id);
    showSuccess('Invoice cloned successfully!');
    router.push('/invoices');
  } catch (error: any) {
    console.error('Error cloning invoice:', error);
    showError(error.message || 'Failed to clone invoice');
  }
};

const editInvoice = () => {
  if (!invoice.value) return;
  router.push(`/invoices/${invoice.value.id}/edit`);
};

const markAsPaid = async () => {
  if (!invoice.value) return;

  try {
    await markAsPaidMutation.mutateAsync(invoice.value.id);
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

const downloadAttachment = async () => {
  try {
    // TODO: Implement download functionality
    showSuccess('Download functionality will be implemented soon');
  } catch (error) {
    console.error('Error downloading attachment:', error);
    showError('Failed to download attachment');
  }
};

const goBack = () => {
  router.push('/invoices');
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
</script>
