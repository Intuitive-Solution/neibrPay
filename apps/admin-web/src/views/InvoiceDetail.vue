<template>
  <div class="max-w-7xl">
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
      <button @click="router.push('/invoices')" class="btn-primary">
        Back to HOA Dues
      </button>
    </div>

    <!-- Invoice Details Content -->
    <div v-else-if="invoice" class="space-y-6">
      <!-- Header Section -->
      <div class="card mb-6">
        <div
          class="flex flex-col lg:flex-row lg:items-start lg:justify-between"
        >
          <div class="mb-4 lg:mb-0">
            <div class="flex items-center gap-4 mb-2">
              <h1 class="text-2xl font-bold text-gray-900">
                #{{ invoice.invoice_number }}
              </h1>
              <!-- Enhanced Status Badge -->
              <span
                :class="
                  getStatusBadgeClass(
                    invoice.status,
                    invoice.deleted_at || undefined
                  )
                "
                class="badge text-base"
              >
                <svg
                  v-if="invoice.deleted_at"
                  class="w-5 h-5 mr-2"
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
                <svg
                  v-else-if="invoice.status === 'paid'"
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
                {{
                  getStatusText(invoice.status, invoice.deleted_at || undefined)
                }}
              </span>
            </div>
            <p class="text-gray-600">View HOA due details and information</p>
          </div>
          <div class="flex items-center gap-3">
            <button @click="goBack" class="btn-outline">
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
              Back to HOA Dues
            </button>
          </div>
        </div>

        <!-- Quick Action Buttons (Hidden for residents) -->
        <div
          v-if="!isResident"
          class="flex flex-wrap gap-3 mt-6 pt-6 border-t border-gray-200"
        >
          <!-- Actions for deleted invoices -->
          <template v-if="invoice.deleted_at">
            <button
              @click="restoreInvoice"
              :disabled="isRestoring"
              class="btn-primary"
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
                  d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                />
              </svg>
              {{ isRestoring ? 'Restoring...' : 'Restore' }}
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
          </template>

          <!-- Actions for active invoices -->
          <template v-else>
            <button
              @click="emailInvoice"
              :disabled="isEmailing || invoice.status === 'paid'"
              class="btn-primary"
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

            <!-- Review Payment button for admins when invoice is in_review -->
            <button
              v-if="
                isAdmin && invoice.status === 'in_review' && paymentInReview
              "
              @click="reviewPayment(paymentInReview)"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
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
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"
                />
              </svg>
              Review Payment
            </button>
            <!-- View Payment button for admins when invoice is paid -->
            <button
              v-if="invoice.status === 'paid' && approvedPayments.length > 0"
              @click="viewPayment(approvedPayments[0])"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
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
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
                />
              </svg>
              View Payment
            </button>
            <!-- Record Payment button - hidden for admins when invoice is in_review -->
            <button
              v-else-if="
                invoice.status !== 'paid' &&
                !(isAdmin && invoice.status === 'in_review')
              "
              @click="showPaymentModal = true"
              class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200"
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
                  d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                />
              </svg>
              Record Payment
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
          </template>
        </div>

        <!-- View Payment Button for Residents (Paid Invoices) -->
        <div
          v-if="
            isResident &&
            invoice.status === 'paid' &&
            !invoice.deleted_at &&
            approvedPayments.length > 0
          "
          class="flex flex-wrap gap-3 mt-6 pt-6 border-t border-gray-200"
        >
          <button
            @click="viewPayment(approvedPayments[0])"
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
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
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
              />
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
              />
            </svg>
            View Payment
          </button>
        </div>
      </div>

      <!-- Payment Status Banner -->
      <div
        v-if="
          invoice.status !== 'paid' &&
          invoice.status !== 'cancelled' &&
          !invoice.deleted_at
        "
        :class="[
          'mb-6 p-4 rounded-lg border-l-4',
          invoice.status === 'overdue'
            ? 'bg-red-50 border-red-500'
            : invoice.status === 'partial'
              ? 'bg-yellow-50 border-yellow-500'
              : 'bg-blue-50 border-blue-500',
        ]"
      >
        <div class="flex items-center">
          <svg
            v-if="invoice.status === 'overdue'"
            class="w-5 h-5 mr-3 text-red-600"
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
            v-else-if="invoice.status === 'partial'"
            class="w-5 h-5 mr-3 text-yellow-600"
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
          <svg
            v-else
            class="w-5 h-5 mr-3 text-blue-600"
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
          <div class="flex-1">
            <p
              :class="[
                'font-medium text-base',
                invoice.status === 'overdue'
                  ? 'text-red-900'
                  : invoice.status === 'partial'
                    ? 'text-yellow-900'
                    : 'text-blue-900',
              ]"
            >
              {{
                invoice.status === 'overdue'
                  ? 'Overdue'
                  : invoice.status === 'partial'
                    ? 'Partially Paid'
                    : 'Payment Due'
              }}
            </p>
            <p
              :class="[
                'text-sm mt-1',
                invoice.status === 'overdue'
                  ? 'text-red-700'
                  : invoice.status === 'partial'
                    ? 'text-yellow-700'
                    : 'text-blue-700',
              ]"
            >
              Balance Due:
              <span class="font-bold"
                >${{ formatCurrency(invoice.balance_due) }}</span
              >
              <span v-if="getDueDateDisplay()" class="ml-2">
                • {{ getDueDateDisplay() }}
              </span>
            </p>

            <!-- Early Payment Discount Details -->
            <div
              v-if="invoice.early_payment_discount_enabled"
              :class="[
                'mt-3 pt-3 border-t',
                invoice.status === 'overdue'
                  ? 'border-red-200'
                  : invoice.status === 'partial'
                    ? 'border-yellow-200'
                    : 'border-blue-200',
              ]"
            >
              <div class="flex items-start">
                <svg
                  class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0"
                  :class="[
                    invoice.status === 'overdue'
                      ? 'text-green-600'
                      : invoice.status === 'partial'
                        ? 'text-green-600'
                        : 'text-green-600',
                  ]"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
                <div class="flex-1">
                  <p
                    :class="[
                      'text-sm font-medium',
                      invoice.status === 'overdue'
                        ? 'text-green-800'
                        : invoice.status === 'partial'
                          ? 'text-green-800'
                          : 'text-green-800',
                    ]"
                  >
                    Early Payment Discount Available
                  </p>
                  <p
                    :class="[
                      'text-xs mt-1',
                      invoice.status === 'overdue'
                        ? 'text-green-700'
                        : invoice.status === 'partial'
                          ? 'text-green-700'
                          : 'text-green-700',
                    ]"
                  >
                    Save
                    <span class="font-semibold"
                      >${{
                        formatCurrency(
                          invoice.early_payment_discount_amount || 0
                        )
                      }}</span
                    >
                    <span
                      v-if="
                        invoice.early_payment_discount_type === 'percentage'
                      "
                      class="ml-1"
                    >
                      ({{ invoice.early_payment_discount_amount }}%)
                    </span>
                    if paid by
                    <span class="font-semibold">
                      {{
                        formatDate(invoice.early_payment_discount_by_date || '')
                      }}
                    </span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Late Fee Details -->
            <div
              v-if="invoice.late_fee_enabled"
              :class="[
                'mt-3 pt-3 border-t',
                invoice.status === 'overdue'
                  ? 'border-red-200'
                  : invoice.status === 'partial'
                    ? 'border-yellow-200'
                    : 'border-blue-200',
              ]"
            >
              <div class="flex items-start">
                <svg
                  class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0"
                  :class="[
                    invoice.status === 'overdue'
                      ? 'text-red-600'
                      : invoice.status === 'partial'
                        ? 'text-yellow-600'
                        : 'text-orange-600',
                  ]"
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
                <div class="flex-1">
                  <p
                    :class="[
                      'text-sm font-medium',
                      invoice.status === 'overdue'
                        ? 'text-red-800'
                        : invoice.status === 'partial'
                          ? 'text-yellow-800'
                          : 'text-orange-800',
                    ]"
                  >
                    Late Fee Applies
                  </p>
                  <p
                    :class="[
                      'text-xs mt-1',
                      invoice.status === 'overdue'
                        ? 'text-red-700'
                        : invoice.status === 'partial'
                          ? 'text-yellow-700'
                          : 'text-orange-700',
                    ]"
                  >
                    Late fee of
                    <span class="font-semibold"
                      >${{ formatCurrency(invoice.late_fee_amount || 0) }}</span
                    >
                    <span
                      v-if="invoice.late_fee_type === 'percentage'"
                      class="ml-1"
                    >
                      ({{ invoice.late_fee_amount }}%)
                    </span>
                    will be applied after
                    <span class="font-semibold">
                      {{ formatDate(invoice.late_fee_applies_on_date || '') }}
                    </span>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Payment Action Buttons (for residents) -->
      <div
        v-if="
          isResident &&
          invoice.status !== 'paid' &&
          invoice.status !== 'cancelled' &&
          !invoice.deleted_at &&
          invoice.balance_due > 0
        "
        class="mb-6 flex flex-wrap gap-3"
      >
        <!-- Pay Now button - show for residents when invoice is payment_rejected, hide for in_review -->
        <button
          v-if="
            (isStripeConfigured &&
              invoice?.balance_due > 0 &&
              invoice.status !== 'in_review' &&
              invoice.status !== 'payment_rejected') ||
            (isResident && invoice.status === 'payment_rejected')
          "
          @click="showStripePaymentModal = true"
          class="inline-flex items-center px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors duration-200 font-medium"
        >
          <svg
            class="w-5 h-5 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
            />
          </svg>
          Pay Now
        </button>
        <!-- Resubmit button for residents when invoice is payment_rejected -->
        <button
          v-if="
            isResident &&
            invoice.status === 'payment_rejected' &&
            paymentInReviewOrRejected
          "
          @click="
            selectedPaymentForReview = paymentInReviewOrRejected;
            showPaymentModal = true;
          "
          class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
        >
          <svg
            class="w-5 h-5 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
            />
          </svg>
          Resubmit Payment
        </button>
        <!-- View Payment button for residents when invoice is in_review (not payment_rejected) -->
        <button
          v-if="
            isResident &&
            invoice.status === 'in_review' &&
            paymentInReviewOrRejected
          "
          @click="viewPayment(paymentInReviewOrRejected)"
          class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium"
        >
          <svg
            class="w-5 h-5 mr-2"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
            />
          </svg>
          View Payment
        </button>
        <!-- Mark as Paid button - hidden for residents when invoice is in_review or payment_rejected -->
        <button
          v-else-if="
            !(
              isResident &&
              (invoice.status === 'in_review' ||
                invoice.status === 'payment_rejected')
            )
          "
          @click="showPaymentModal = true"
          class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors duration-200 font-medium"
        >
          <svg
            class="w-5 h-5 mr-2"
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
          Mark as Paid
        </button>
      </div>

      <!-- Line Items Section -->
      <div v-if="invoice.items && invoice.items.length > 0" class="card mb-6">
        <div class="bg-gray-100 px-6 py-3 rounded-t-lg">
          <h3 class="text-lg font-medium text-gray-900">Charges</h3>
        </div>
        <div class="p-6">
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
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Unit Cost
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Qty
                  </th>
                  <th
                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                  >
                    Total
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="(item, index) in invoice.items"
                  :key="index"
                  class="hover:bg-gray-50 transition-colors duration-150"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ item.name }}
                    </div>
                    <div
                      v-if="item.category"
                      class="text-xs text-gray-500 mt-1"
                    >
                      {{ item.category }}
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <div class="text-sm text-gray-600 max-w-md">
                      {{ item.description || '-' }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-gray-900">
                      ${{ formatCurrency(item.unit_cost) }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm text-gray-900">{{ item.quantity }}</div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm font-medium text-gray-900">
                      ${{ formatCurrency(item.line_total) }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Main Content Grid -->
      <div class="bg-white p-4 md:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
          <!-- Left Column - Invoice Information -->
          <div class="card-modern">
            <!-- Card Header with Icon -->
            <div class="card-header-modern">
              <div class="card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                  />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="section-title-modern">Invoice Details</h3>
                <p class="section-subtitle-modern">
                  Invoice information and payment terms
                </p>
              </div>
            </div>
            <div class="space-y-6">
              <!-- Basic Information Section -->
              <div>
                <h4
                  class="text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200"
                >
                  Basic Information
                </h4>
                <div class="grid grid-cols-1 gap-3">
                  <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"
                      >Invoice Number</label
                    >
                    <p class="text-sm font-medium text-gray-900">
                      {{ invoice.invoice_number }}
                    </p>
                  </div>
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-500 mb-1"
                        >Invoice Date</label
                      >
                      <p class="text-sm text-gray-900">
                        {{ formatDate(invoice.created_at) }}
                      </p>
                    </div>
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-500 mb-1"
                        >Start Date</label
                      >
                      <p class="text-sm text-gray-900">
                        {{ formatDate(invoice.start_date) }}
                      </p>
                    </div>
                  </div>
                  <div v-if="invoice.po_number">
                    <label class="block text-xs font-medium text-gray-500 mb-1"
                      >PO Number</label
                    >
                    <p class="text-sm text-gray-900">
                      {{ invoice.po_number }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Scheduling Section -->
              <div>
                <h4
                  class="text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200"
                >
                  Scheduling
                </h4>
                <div class="grid grid-cols-1 gap-3">
                  <div class="grid grid-cols-2 gap-3">
                    <div>
                      <label
                        class="block text-xs font-medium text-gray-500 mb-1"
                        >Frequency</label
                      >
                      <p class="text-sm text-gray-900">
                        {{ formatFrequency(invoice.frequency) }}
                      </p>
                    </div>
                    <div v-if="invoice.remaining_cycles">
                      <label
                        class="block text-xs font-medium text-gray-500 mb-1"
                        >Remaining Cycles</label
                      >
                      <p class="text-sm text-gray-900">
                        {{ invoice.remaining_cycles }}
                      </p>
                    </div>
                  </div>
                  <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1"
                      >Due Date</label
                    >
                    <p class="text-sm text-gray-900">
                      {{ getDueDate() }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- Payment Terms Section -->
              <div>
                <h4
                  class="text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-200"
                >
                  Payment Terms
                </h4>
                <div class="space-y-4">
                  <!-- Early Payment Discount -->
                  <div>
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-xs font-medium text-gray-700">
                        Early Payment Discount
                      </label>
                      <span
                        :class="[
                          'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                          invoice.early_payment_discount_enabled
                            ? 'bg-green-100 text-green-800'
                            : 'bg-gray-100 text-gray-600',
                        ]"
                      >
                        {{
                          invoice.early_payment_discount_enabled
                            ? 'Enabled'
                            : 'Disabled'
                        }}
                      </span>
                    </div>
                    <div
                      v-if="invoice.early_payment_discount_enabled"
                      class="mt-2 pl-3 border-l-2 border-green-200 space-y-2"
                    >
                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <span class="text-xs text-gray-500">Amount:</span>
                          <p class="text-sm font-medium text-gray-900">
                            ${{
                              formatCurrency(
                                invoice.early_payment_discount_amount || 0
                              )
                            }}
                            <span class="text-xs text-gray-500 ml-1"
                              >({{
                                invoice.early_payment_discount_type ===
                                'percentage'
                                  ? '%'
                                  : 'Fixed'
                              }})</span
                            >
                          </p>
                        </div>
                        <div>
                          <span class="text-xs text-gray-500">By Date:</span>
                          <p class="text-sm text-gray-900">
                            {{
                              formatDate(
                                invoice.early_payment_discount_by_date || ''
                              )
                            }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Late Fee -->
                  <div>
                    <div class="flex items-center justify-between mb-2">
                      <label class="text-xs font-medium text-gray-700">
                        Late Fee
                      </label>
                      <span
                        :class="[
                          'inline-flex items-center px-2 py-0.5 rounded text-xs font-medium',
                          invoice.late_fee_enabled
                            ? 'bg-red-100 text-red-800'
                            : 'bg-gray-100 text-gray-600',
                        ]"
                      >
                        {{ invoice.late_fee_enabled ? 'Enabled' : 'Disabled' }}
                      </span>
                    </div>
                    <div
                      v-if="invoice.late_fee_enabled"
                      class="mt-2 pl-3 border-l-2 border-red-200 space-y-2"
                    >
                      <div class="grid grid-cols-2 gap-2">
                        <div>
                          <span class="text-xs text-gray-500">Amount:</span>
                          <p class="text-sm font-medium text-gray-900">
                            ${{ formatCurrency(invoice.late_fee_amount || 0) }}
                            <span class="text-xs text-gray-500 ml-1"
                              >({{
                                invoice.late_fee_type === 'percentage'
                                  ? '%'
                                  : 'Fixed'
                              }})</span
                            >
                          </p>
                        </div>
                        <div>
                          <span class="text-xs text-gray-500">Applies On:</span>
                          <p class="text-sm text-gray-900">
                            {{
                              formatDate(invoice.late_fee_applies_on_date || '')
                            }}
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Middle Column - Unit & Owner Information -->
          <div class="card-modern">
            <!-- Card Header with Icon -->
            <div class="card-header-modern">
              <div class="card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                  />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="section-title-modern">Unit & Owner</h3>
                <p class="section-subtitle-modern">
                  Property and resident information
                </p>
              </div>
            </div>
            <div class="space-y-4">
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
              <div
                v-if="invoice.unit?.owners && invoice.unit.owners.length > 0"
              >
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
          <div class="card-modern">
            <!-- Card Header with Icon -->
            <div class="card-header-modern">
              <div class="card-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="section-title-modern">Financial Summary</h3>
                <p class="section-subtitle-modern">
                  Invoice totals and payment status
                </p>
              </div>
            </div>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Subtotal</span>
                <span class="text-sm font-medium text-gray-900"
                  >${{ formatCurrency(invoice.subtotal) }}</span
                >
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

              <!-- Amount Paid (if payments exist) -->
              <div
                v-if="payments && payments.length > 0"
                class="border-t border-gray-200 pt-3"
              >
                <div class="flex justify-between">
                  <span class="text-sm text-gray-600">Amount Paid</span>
                  <span class="text-sm font-medium text-green-600"
                    >${{ formatCurrency(amountPaid) }}</span
                  >
                </div>
              </div>

              <!-- Balance Due with Color Coding -->
              <div class="border-t border-gray-200 pt-3">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-gray-900"
                    >Balance Due</span
                  >
                  <span
                    :class="[
                      'text-sm font-bold',
                      getBalanceDueClass(invoice.balance_due, invoice.status),
                    ]"
                    >${{ formatCurrency(invoice.balance_due) }}</span
                  >
                </div>
                <!-- Payment Progress Bar -->
                <div
                  v-if="invoice.total > 0 && invoice.status !== 'paid'"
                  class="mt-3"
                >
                  <div class="flex justify-between text-xs text-gray-500 mb-1">
                    <span>Payment Progress</span>
                    <span>{{ paymentProgress }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      :class="[
                        'h-2 rounded-full transition-all duration-300',
                        invoice.status === 'overdue'
                          ? 'bg-red-500'
                          : invoice.status === 'partial'
                            ? 'bg-yellow-500'
                            : 'bg-primary',
                      ]"
                      :style="{ width: `${paymentProgress}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Documents and Activity Tabs Section -->
        <div class="card">
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
                @click="activeTab = 'payments'"
                :class="[
                  activeTab === 'payments'
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                  'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
                ]"
              >
                Payments
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
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                      >
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

            <!-- Payments Tab -->
            <div v-if="activeTab === 'payments'">
              <div v-if="isLoadingPayments" class="text-center py-8">
                <div
                  class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"
                ></div>
                <p class="mt-2 text-sm text-gray-600">Loading payments...</p>
              </div>

              <div
                v-else-if="paymentsError"
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
                  Error Loading Payments
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                  Failed to load payment history
                </p>
              </div>

              <div
                v-else-if="!payments || payments.length === 0"
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
                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"
                  />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">
                  No Payments Yet
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                  No payments have been recorded for this invoice yet.
                </p>
              </div>

              <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                      >
                        Payment Date
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
                        Status
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
                      <th
                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                      >
                        Actions
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                      v-for="payment in payments"
                      :key="payment.id"
                      class="hover:bg-gray-50"
                    >
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                      >
                        {{ formatDate(payment.payment_date) }}
                      </td>
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                      >
                        ${{ formatCurrency(payment.amount) }}
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          :class="
                            getPaymentMethodBadgeClass(payment.payment_method)
                          "
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        >
                          {{ formatPaymentMethod(payment.payment_method) }}
                        </span>
                      </td>
                      <td class="px-6 py-4 whitespace-nowrap">
                        <span
                          :class="getPaymentStatusBadgeClass(payment.status)"
                          class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        >
                          {{ getPaymentStatusText(payment.status) }}
                        </span>
                      </td>
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                      >
                        {{ payment.payment_reference || '-' }}
                      </td>
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                      >
                        {{ payment.recorder?.name || 'Unknown' }}
                      </td>
                      <td
                        class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                      >
                        <div class="flex space-x-2">
                          <!-- Resubmit button for residents on rejected payments -->
                          <button
                            v-if="!isAdmin && payment.status === 'rejected'"
                            @click="
                              selectedPaymentForReview = payment;
                              showPaymentModal = true;
                            "
                            class="text-green-600 hover:text-green-900"
                          >
                            Resubmit
                          </button>

                          <!-- Review button for admins on in-review payments -->
                          <button
                            v-if="isAdmin && payment.status === 'in_review'"
                            @click="reviewPayment(payment)"
                            class="text-blue-600 hover:text-blue-900"
                          >
                            Review
                          </button>

                          <!-- View button for all other cases -->
                          <button
                            v-if="
                              !(isAdmin && payment.status === 'in_review') &&
                              !(!isAdmin && payment.status === 'rejected')
                            "
                            @click="viewPayment(payment)"
                            class="text-primary hover:text-primary-600"
                          >
                            View
                          </button>

                          <!-- Delete button -->
                          <button
                            @click="deletePayment(payment)"
                            :disabled="
                              deletingPaymentId === payment.id ||
                              payment.status === 'approved'
                            "
                            class="text-red-600 hover:text-red-900 disabled:opacity-50 disabled:cursor-not-allowed"
                          >
                            {{
                              deletingPaymentId === payment.id
                                ? 'Deleting...'
                                : 'Delete'
                            }}
                          </button>
                        </div>
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
        <div class="card">
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
              <p class="mt-2 text-sm text-gray-600">
                Loading PDF information...
              </p>
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

    <!-- Payment Entry Modal -->
    <PaymentEntryModal
      :is-open="showPaymentModal"
      :invoice="invoice || null"
      :existing-payment="selectedPaymentForReview"
      @close="showPaymentModal = false"
      @success="handlePaymentSuccess"
    />

    <!-- Payment Update Modal -->
    <PaymentUpdateModal
      :is-open="showPaymentUpdateModal"
      :payment="selectedPayment"
      @close="showPaymentUpdateModal = false"
      @success="handlePaymentUpdateSuccess"
    />

    <!-- Payment Review Modal (Admin) -->
    <PaymentReviewModal
      :is-open="showPaymentReviewModal"
      :payment="selectedPaymentForReview"
      :invoice="invoice"
      @close="showPaymentReviewModal = false"
      @approved="handlePaymentApproved"
      @rejected="handlePaymentRejected"
    />

    <!-- Payment View Modal -->
    <PaymentViewModal
      :is-open="showPaymentViewModal"
      :payment="selectedPaymentForView"
      :invoice="invoice"
      @close="showPaymentViewModal = false"
    />

    <!-- Stripe Payment Modal -->
    <StripePaymentModal
      :is-open="showStripePaymentModal"
      :invoice="invoice || null"
      @close="showStripePaymentModal = false"
      @success="handleStripePaymentSuccess"
      @error="handleStripePaymentError"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  useInvoice,
  useDeleteInvoice,
  useEmailInvoice,
  useRestoreInvoice,
  useCloneInvoice,
} from '../composables/useInvoices';
import { useLatestInvoicePdf } from '../composables/useInvoicePdf';
import {
  useInvoiceAttachments,
  useDownloadInvoiceAttachment,
} from '../composables/useInvoiceAttachments';
import { usePayments, useDeletePayment } from '../composables/usePayments';
import { invoicesApi, useSettings } from '@neibrpay/api-client';
import { useAuthStore } from '../stores/auth';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import PaymentEntryModal from '../components/PaymentEntryModal.vue';
import PaymentUpdateModal from '../components/PaymentUpdateModal.vue';
import PaymentReviewModal from '../components/PaymentReviewModal.vue';
import PaymentViewModal from '../components/PaymentViewModal.vue';
import StripePaymentModal from '../components/StripePaymentModal.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Role check
const isResident = computed(() => authStore.isResident);
const isAdmin = computed(() => authStore.isAdmin);

// Get invoice ID from route params
const invoiceId = computed(() => parseInt(route.params.id as string));

// Fetch invoice data
const {
  data: invoice,
  isLoading,
  error,
  refetch: refetchInvoice,
} = useInvoice(invoiceId.value);

// Fetch PDF data
const {
  data: latestPdf,
  isLoading: isLoadingPdf,
  refetch: refetchLatestPdf,
} = useLatestInvoicePdf(invoiceId.value);

// Fetch invoice attachments
const {
  data: attachments,
  isLoading: isLoadingAttachments,
  error: attachmentsError,
} = useInvoiceAttachments(invoiceId.value);

// Fetch payments for this invoice
const {
  data: payments,
  isLoading: isLoadingPayments,
  error: paymentsError,
} = usePayments({ invoice_id: invoiceId.value });

// Fetch tenant settings to check Stripe status
const { data: settingsData } = useSettings();

// Mutations
const deleteInvoiceMutation = useDeleteInvoice();
const emailInvoiceMutation = useEmailInvoice();
const restoreInvoiceMutation = useRestoreInvoice();
const cloneInvoiceMutation = useCloneInvoice();
const downloadAttachmentMutation = useDownloadInvoiceAttachment();
const deletePaymentMutation = useDeletePayment();

// Loading states
const isDeleting = computed(() => deleteInvoiceMutation.isPending.value);
const isEmailing = computed(() => emailInvoiceMutation.isPending.value);
const isRestoring = computed(() => restoreInvoiceMutation.isPending.value);
const showDeleteModal = ref(false);
const showPaymentModal = ref(false);
const showStripePaymentModal = ref(false);
const showPaymentUpdateModal = ref(false);
const showPaymentReviewModal = ref(false);
const showPaymentViewModal = ref(false);
const selectedPayment = ref<any>(null);
const selectedPaymentForReview = ref<any>(null);
const selectedPaymentForView = ref<any>(null);
const deletingPaymentId = ref<number | null>(null);

// Check if Stripe is configured for the tenant
const isStripeConfigured = computed(() => {
  return !!(
    settingsData.value?.tenant?.settings?.stripe_connect_id &&
    settingsData.value?.tenant?.settings?.charges_enabled
  );
});

// Success/Error messages
const successMessage = ref('');
const errorMessage = ref('');
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const pdfLoadError = ref(false);
const pdfRefreshKey = ref(0);
const pdfSignedUrl = ref('');
const isLoadingSignedPdfUrl = ref(false);

// Tab state
const activeTab = ref('documents');

// Handle Stripe payment redirects
onMounted(() => {
  const paymentStatus = route.query.payment as string;
  const sessionId = route.query.session_id as string;

  if (paymentStatus === 'success' && sessionId) {
    showSuccess('Payment processing! Your payment is being confirmed...');
    // Refetch invoice and payments to get updated status
    refetchInvoice();
    // Remove query params from URL
    router.replace({ query: {} });
  } else if (paymentStatus === 'cancelled') {
    showError('Payment was cancelled. You can try again when ready.');
    // Remove query params from URL
    router.replace({ query: {} });
  }
});

// Watch for invoice updates after Stripe payment
watch(
  () => invoice.value?.balance_due,
  (newBalance, oldBalance) => {
    if (
      oldBalance !== undefined &&
      newBalance !== undefined &&
      newBalance < oldBalance
    ) {
      // Balance decreased, payment likely processed
      refetchInvoice();
    }
  }
);

watch(
  () => invoice.value?.updated_at,
  (newValue, oldValue) => {
    if (newValue && newValue !== oldValue) {
      void refetchLatestPdf();
      pdfRefreshKey.value = Date.now();
    }
  }
);

const fetchSignedPdfUrl = async () => {
  if (!invoiceId.value || !latestPdf.value) {
    pdfSignedUrl.value = '';
    return;
  }

  isLoadingSignedPdfUrl.value = true;
  try {
    const response = await invoicesApi.getInvoicePdfSignedUrl(invoiceId.value);
    pdfSignedUrl.value = response.file_url ?? '';
  } catch (error) {
    console.error('Failed to fetch signed PDF URL:', error);
    pdfSignedUrl.value = '';
  } finally {
    isLoadingSignedPdfUrl.value = false;
  }
};

watch(
  [latestPdf, pdfRefreshKey],
  () => {
    if (latestPdf.value) {
      void fetchSignedPdfUrl();
    } else {
      pdfSignedUrl.value = '';
    }
  },
  { immediate: true }
);

const pdfViewerUrl = computed(() => {
  if (!latestPdf.value?.file_path || !pdfSignedUrl.value) return '';
  // Use short-lived signed URL generated by backend.
  // Do not append query params, as that invalidates the signature.
  return pdfSignedUrl.value;
});

// Computed properties for financial summary
const amountPaid = computed(() => {
  // Use payments from invoice relationship if available, otherwise use payments query
  const paymentsList = invoice.value?.payments || payments.value;

  if (
    !paymentsList ||
    !Array.isArray(paymentsList) ||
    paymentsList.length === 0
  ) {
    return 0;
  }

  // Filter out temporary Stripe payments AND only include approved payments
  // Only count payments with status = 'approved' (exclude in_review, rejected, pending)
  const confirmedPayments = paymentsList.filter((payment: any) => {
    // Only count approved payments
    if (payment?.status !== 'approved') {
      return false;
    }
    // Filter out temporary Stripe payments (stripe_card/stripe_ach with null payment_intent_id)
    const isStripePayment =
      payment?.payment_method === 'stripe_card' ||
      payment?.payment_method === 'stripe_ach';
    const hasPaymentIntent = payment?.stripe_payment_intent_id != null;
    // Include payment if it's not a Stripe payment OR if it has a payment_intent_id (confirmed)
    return !isStripePayment || hasPaymentIntent;
  });

  const total = confirmedPayments.reduce((sum: number, payment: any) => {
    // Handle both number and string amounts
    let amount = payment?.amount;

    // Handle null/undefined
    if (amount == null) {
      return sum;
    }

    // Convert string to number if needed
    if (typeof amount === 'string') {
      amount = parseFloat(amount);
    }

    // Validate it's a valid number
    if (typeof amount !== 'number' || isNaN(amount) || amount < 0) {
      console.warn('Invalid payment amount:', payment);
      return sum;
    }

    return sum + amount;
  }, 0);

  return total;
});

const paymentProgress = computed(() => {
  if (!invoice.value || !invoice.value.total) return 0;

  const paid = amountPaid.value || 0;
  const total = invoice.value.total || 0;

  // Handle edge cases
  if (total === 0 || isNaN(total) || isNaN(paid)) return 0;
  if (paid < 0) return 0;
  if (paid >= total) return 100;

  const percentage = (paid / total) * 100;
  return Math.min(Math.round(percentage), 100);
});

// Find payment in review or rejected for residents to view
const paymentInReviewOrRejected = computed(() => {
  if (!isResident.value) return null;
  const paymentsList = invoice.value?.payments || payments.value;
  if (!paymentsList || !Array.isArray(paymentsList)) return null;

  // Find payment with status 'in_review' or 'rejected'
  return (
    paymentsList.find(
      (payment: any) =>
        payment?.status === 'in_review' || payment?.status === 'rejected'
    ) || null
  );
});

// Find payment in review for admins to review
const paymentInReview = computed(() => {
  if (!isAdmin.value) return null;
  const paymentsList = invoice.value?.payments || payments.value;
  if (!paymentsList || !Array.isArray(paymentsList)) return null;

  // Find payment with status 'in_review'
  return (
    paymentsList.find((payment: any) => payment?.status === 'in_review') || null
  );
});

// Find approved payment(s) for paid invoices (for admins and residents to view)
const approvedPayments = computed(() => {
  const paymentsList = invoice.value?.payments || payments.value;
  if (!paymentsList || !Array.isArray(paymentsList)) return [];

  // Find all approved payments, sorted by date (most recent first)
  return paymentsList
    .filter((payment: any) => payment?.status === 'approved')
    .sort((a: any, b: any) => {
      const dateA = new Date(a.payment_date || a.created_at || 0).getTime();
      const dateB = new Date(b.payment_date || b.created_at || 0).getTime();
      return dateB - dateA;
    });
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

const getStatusText = (status: string, deletedAt?: string) => {
  if (deletedAt) {
    return 'Deleted';
  }
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    sent: 'Sent',
    paid: 'Paid',
    partial: 'Partial',
    overdue: 'Overdue',
    cancelled: 'Cancelled',
    in_review: 'In Review',
    payment_rejected: 'Payment Rejected',
  };
  return statusMap[status] || 'Unknown';
};

const getStatusBadgeClass = (status: string, deletedAt?: string) => {
  if (deletedAt) {
    return 'badge-overdue'; // Use red styling for deleted
  }
  const statusClasses: Record<string, string> = {
    draft: 'badge-draft',
    sent: 'badge-sent',
    paid: 'badge-paid',
    partial: 'badge-partial',
    overdue: 'badge-overdue',
    cancelled: 'badge-partial',
    in_review: 'badge-sent', // Use blue styling (same as sent)
    payment_rejected: 'badge-overdue', // Use red styling (same as overdue)
  };
  return statusClasses[status] || 'badge-draft';
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

const getDueDateDisplay = () => {
  if (!invoice.value) return '';

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
      return '';
  }

  const today = new Date();
  today.setHours(0, 0, 0, 0);
  dueDate.setHours(0, 0, 0, 0);

  const diffTime = dueDate.getTime() - today.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  if (diffDays < 0) {
    const daysOverdue = Math.abs(diffDays);
    return `${daysOverdue} ${daysOverdue === 1 ? 'day' : 'days'} overdue`;
  } else if (diffDays === 0) {
    return 'Due today';
  } else {
    return `Due in ${diffDays} ${diffDays === 1 ? 'day' : 'days'}`;
  }
};

const getBalanceDueClass = (balanceDue: number, status: string) => {
  if (balanceDue === 0 || status === 'paid') {
    return 'text-green-600';
  }
  if (status === 'overdue') {
    return 'text-red-600';
  }
  if (status === 'partial') {
    return 'text-yellow-600';
  }
  return 'text-gray-900';
};

// Action handlers
const emailInvoice = async () => {
  if (!invoice.value) return;

  try {
    await emailInvoiceMutation.mutateAsync({
      id: invoice.value.id,
      email: invoice.value.unit?.owners?.[0]?.email,
    });
    // Refresh invoice data to show updated status
    await refetchInvoice();
    showSuccess('Invoice email sent successfully!');
  } catch (error: any) {
    console.error('Error sending email:', error);
    showError(error.message || 'Failed to send email');
  }
};

const restoreInvoice = async () => {
  if (!invoice.value) return;

  try {
    await restoreInvoiceMutation.mutateAsync(invoice.value.id);
    showSuccess('Invoice restored successfully!');
    router.push('/invoices');
  } catch (error: any) {
    console.error('Error restoring invoice:', error);
    showError(error.message || 'Failed to restore invoice');
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

  // Get today's date in YYYY-MM-DD format
  const today = new Date().toISOString().split('T')[0];

  // Store cloned data in sessionStorage for persistence across navigation
  const clonedData = {
    frequency: invoice.value.frequency,
    remaining_cycles: invoice.value.remaining_cycles,
    start_date: today, // Set start date to today
    due_date: invoice.value.due_date,
    items: invoice.value.items,
    tax_rate: invoice.value.tax_rate,
    early_payment_discount_enabled:
      invoice.value.early_payment_discount_enabled,
    early_payment_discount_amount: invoice.value.early_payment_discount_amount,
    early_payment_discount_type: invoice.value.early_payment_discount_type,
    early_payment_discount_by_date:
      invoice.value.early_payment_discount_by_date,
    late_fee_enabled: invoice.value.late_fee_enabled,
    late_fee_amount: invoice.value.late_fee_amount,
    late_fee_type: invoice.value.late_fee_type,
    late_fee_applies_on_date: invoice.value.late_fee_applies_on_date,
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

const handlePaymentSuccess = () => {
  showSuccess('Payment recorded successfully!');
  // Force refresh of PDF viewer by updating the refresh key
  pdfRefreshKey.value = Date.now();
};

const handlePaymentUpdateSuccess = () => {
  showSuccess('Payment updated successfully!');
  // Force refresh of PDF viewer by updating the refresh key
  pdfRefreshKey.value = Date.now();
};

const handleStripePaymentSuccess = (sessionId: string) => {
  // Payment redirect will happen, this is just for logging
  console.log('Stripe checkout session created:', sessionId);
  // Modal will close automatically when redirecting to Stripe
};

const handleStripePaymentError = (error: string) => {
  showError(error || 'Failed to create payment session');
  // Keep modal open on error so user can try again
};

const formatPaymentMethod = (method: string) => {
  const methodMap: Record<string, string> = {
    cash: 'Cash',
    check: 'Check',
    credit_card: 'Credit Card',
    bank_transfer: 'Bank Transfer',
    stripe_card: 'Stripe (Card)',
    stripe_ach: 'Stripe (ACH)',
    other: 'Other',
  };
  return methodMap[method] || method;
};

const getPaymentMethodBadgeClass = (method: string) => {
  const methodClasses: Record<string, string> = {
    cash: 'bg-green-100 text-green-800',
    check: 'bg-blue-100 text-blue-800',
    credit_card: 'bg-purple-100 text-purple-800',
    bank_transfer: 'bg-indigo-100 text-indigo-800',
    stripe_card: 'bg-indigo-100 text-indigo-800',
    stripe_ach: 'bg-indigo-100 text-indigo-800',
    other: 'bg-gray-100 text-gray-800',
  };
  return methodClasses[method] || 'bg-gray-100 text-gray-800';
};

const getPaymentStatusBadgeClass = (status: string | undefined | null) => {
  if (!status) return 'bg-green-100 text-green-800'; // Default to approved for backward compatibility

  const statusClasses: Record<string, string> = {
    pending: 'bg-gray-100 text-gray-800',
    in_review: 'bg-blue-100 text-blue-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };

  // Convert to lowercase in case of case mismatch
  const normalizedStatus = String(status).toLowerCase();
  return statusClasses[normalizedStatus] || 'bg-green-100 text-green-800';
};

const getPaymentStatusText = (status: string | undefined | null) => {
  if (!status) return 'Approved'; // Default to approved for backward compatibility

  const statusMap: Record<string, string> = {
    pending: 'Pending',
    in_review: 'In Review',
    approved: 'Approved',
    rejected: 'Rejected',
  };

  // Convert to lowercase in case of case mismatch
  const normalizedStatus = String(status).toLowerCase();
  return statusMap[normalizedStatus] || 'Approved';
};

const viewPayment = (payment: any) => {
  selectedPaymentForView.value = payment;
  showPaymentViewModal.value = true;
};

const reviewPayment = (payment: any) => {
  selectedPaymentForReview.value = payment;
  showPaymentReviewModal.value = true;
};

const handlePaymentApproved = () => {
  showSuccess('Payment approved successfully!');
  pdfRefreshKey.value = Date.now();
};

const handlePaymentRejected = () => {
  showSuccess('Payment rejected successfully!');
  pdfRefreshKey.value = Date.now();
};

const deletePayment = async (payment: any) => {
  if (
    !confirm(
      `Are you sure you want to delete this payment of $${formatCurrency(payment.amount)}?`
    )
  ) {
    return;
  }

  deletingPaymentId.value = payment.id;

  try {
    await deletePaymentMutation.mutateAsync(payment.id);
    showSuccess('Payment deleted successfully!');
    // Force refresh of PDF viewer by updating the refresh key
    pdfRefreshKey.value = Date.now();
  } catch (error: any) {
    console.error('Error deleting payment:', error);
    showError(error.message || 'Failed to delete payment');
  } finally {
    deletingPaymentId.value = null;
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
