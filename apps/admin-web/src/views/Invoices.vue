<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Open Invoices Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-primary': activeFilter === 'open',
        }"
        @click="filterByStatus('open')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-blue-100 rounded-lg">
            <svg
              class="w-6 h-6 text-blue-600"
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
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Open Invoices</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ openInvoicesCount }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              ${{ formatCurrency(openInvoicesAmount) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Overdue Invoices Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-red-500': activeFilter === 'overdue',
        }"
        @click="filterByStatus('overdue')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-red-100 rounded-lg">
            <svg
              class="w-6 h-6 text-red-600"
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
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Overdue Invoices</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ overdueInvoicesCount }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              ${{ formatCurrency(overdueInvoicesAmount) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Paid Invoices Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-green-500': activeFilter === 'paid',
        }"
        @click="filterByStatus('paid')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-green-100 rounded-lg">
            <svg
              class="w-6 h-6 text-green-600"
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
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Paid Invoices</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ paidInvoicesCount }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              ${{ formatCurrency(paidInvoicesAmount) }}
            </p>
          </div>
        </div>
      </div>

      <!-- All Invoices Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-gray-500': activeFilter === 'all',
        }"
        @click="filterByStatus('all')"
      >
        <div class="flex items-center">
          <div class="p-3 bg-gray-100 rounded-lg">
            <svg
              class="w-6 h-6 text-gray-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">All Invoices</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ allInvoicesCount }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              ${{ formatCurrency(allInvoicesAmount) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Invoice Directory Section -->
    <div class="bg-white rounded-lg shadow-sm">
      <!-- Header Section -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
        >
          <!-- Search Filter (Left) -->
          <div class="flex-1 max-w-md">
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </div>
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search invoices..."
                class="input-field pl-10 w-full"
              />
            </div>
          </div>

          <!-- Header Controls (Right) -->
          <div class="flex items-center space-x-3">
            <!-- Refresh Button -->
            <button
              @click="refetch"
              :disabled="isLoading"
              class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 transition-colors duration-200"
            >
              <svg
                class="h-4 w-4"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
                :class="{ 'animate-spin': isLoading }"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
            </button>

            <!-- New Invoice Button (Desktop) -->
            <router-link to="/invoices/create" class="hidden md:inline-flex">
              <button class="btn-primary btn-sm">
                <svg
                  class="w-4 h-4"
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
              </button>
            </router-link>
          </div>
        </div>
      </div>

      <!-- Table Section -->
      <div class="overflow-hidden">
        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center py-12">
          <div class="flex items-center space-x-2">
            <svg
              class="animate-spin h-5 w-5 text-primary"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            <span class="text-sm text-gray-600">Loading invoices...</span>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="flex items-center justify-center py-12">
          <div class="text-center">
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
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              Error loading invoices
            </h3>
            <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
            <div class="mt-4">
              <button
                @click="refetch"
                class="text-sm text-primary hover:text-primary-600"
              >
                Try again
              </button>
            </div>
          </div>
        </div>

        <!-- Empty State -->
        <div
          v-else-if="!filteredInvoices || filteredInvoices.length === 0"
          class="flex items-center justify-center py-12"
        >
          <div class="text-center">
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
              No invoices found
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                searchQuery
                  ? 'Try adjusting your search query.'
                  : 'Get started by creating your first invoice.'
              }}
            </p>
            <div v-if="!searchQuery" class="mt-4">
              <router-link to="/invoices/create">
                <button
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                  <svg
                    class="-ml-1 mr-2 h-4 w-4"
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
                  Create Invoice
                </button>
              </router-link>
            </div>
          </div>
        </div>

        <!-- Table with Data -->
        <table v-else class="w-full divide-y divide-gray-200">
          <thead class="bg-gray-100 border-b border-gray-200">
            <tr>
              <th
                @click="sortBy('invoice')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>INVOICE</span>
                  <svg
                    v-if="sortColumn === 'invoice'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('unit')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>UNIT</span>
                  <svg
                    v-if="sortColumn === 'unit'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('amount')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden xl:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>AMOUNT</span>
                  <svg
                    v-if="sortColumn === 'amount'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('dueDate')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>DUE DATE</span>
                  <svg
                    v-if="sortColumn === 'dueDate'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                @click="sortBy('status')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>STATUS</span>
                  <svg
                    v-if="sortColumn === 'status'"
                    class="w-4 h-4"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      v-if="sortDirection === 'asc'"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M5 15l7-7 7 7"
                    />
                    <path
                      v-else
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M19 9l-7 7-7-7"
                    />
                  </svg>
                </div>
              </th>
              <th
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider"
              ></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr
              v-for="invoice in filteredInvoices"
              :key="invoice.id"
              class="table-row-hover"
            >
              <!-- Invoice Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div
                        class="h-10 w-10 rounded-full bg-gray-100 flex items-center justify-center"
                      >
                        <svg
                          class="h-5 w-5 text-gray-600"
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
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        {{ invoice.invoice_number || `#${invoice.id}` }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ formatDate(invoice.created_at) }}
                      </div>
                      <!-- Mobile-only additional info -->
                      <div class="sm:hidden mt-1">
                        <div class="text-xs text-gray-500">
                          {{ invoice.unit?.title || 'N/A' }} • ${{
                            formatCurrency(invoice.total)
                          }}
                        </div>
                        <div class="mt-1">
                          <span
                            :class="getStatusBadgeClass(invoice.status)"
                            class="badge text-xs"
                          >
                            {{ getStatusText(invoice.status) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Unit Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <div class="text-sm text-gray-900">
                  {{ invoice.unit?.title || 'N/A' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ invoice.unit?.address || '' }}
                </div>
              </td>

              <!-- Amount Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden xl:table-cell">
                <div class="text-sm font-medium text-gray-900">
                  ${{ formatCurrency(invoice.total) }}
                </div>
                <div class="text-sm text-gray-500">
                  Balance: ${{ formatCurrency(invoice.balance_due) }}
                </div>
              </td>

              <!-- Due Date Column -->
              <td
                class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden lg:table-cell"
              >
                {{ formatDate(invoice.start_date) }}
              </td>

              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <span
                  :class="getStatusBadgeClass(invoice.status)"
                  class="badge"
                >
                  {{ getStatusText(invoice.status) }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end relative">
                  <!-- Enhanced Kebab Menu - More Visible -->
                  <DropdownMenu
                    trigger-class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 shadow-sm hover:shadow-md transition-all duration-200"
                  >
                    <template #default="{ close }">
                      <button
                        @click="
                          () => {
                            viewInvoice(invoice.id);
                            close();
                          }
                        "
                        class="dropdown-item"
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
                        View Preview
                      </button>
                      <button
                        @click="
                          () => {
                            editInvoice(invoice.id);
                            close();
                          }
                        "
                        class="dropdown-item"
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
                        @click="
                          () => {
                            recordPayment(invoice.id);
                            close();
                          }
                        "
                        class="dropdown-item"
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
                        @click="
                          () => {
                            // Add duplicate functionality here
                            console.log('Duplicate invoice:', invoice.id);
                            close();
                          }
                        "
                        class="dropdown-item"
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
                        Duplicate
                      </button>
                      <div class="border-t border-gray-200 my-1"></div>
                      <button
                        @click="
                          () => {
                            deleteInvoice(invoice);
                            close();
                          }
                        "
                        :disabled="deletingInvoiceId === invoice.id"
                        class="dropdown-item dropdown-item-danger"
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
                        {{
                          deletingInvoiceId === invoice.id
                            ? 'Deleting...'
                            : 'Delete'
                        }}
                      </button>
                    </template>
                  </DropdownMenu>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :is-open="showDeleteModal"
      title="Delete Invoice"
      :message="`Are you sure you want to delete ${invoiceToDelete?.invoice_number || 'this invoice'}?`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletingInvoiceId === invoiceToDelete?.id"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Mobile Fixed Bottom Button -->
    <div
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/invoices/create" class="block">
        <button class="btn-primary w-full">
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
              d="M12 6v6m0 0v6m0-6h6m-6 0H6"
            />
          </svg>
          New Invoice
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useInvoices, useDeleteInvoice } from '../composables/useInvoices';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import DropdownMenu from '../components/DropdownMenu.vue';

const router = useRouter();

// Local state
const searchQuery = ref('');
const deletingInvoiceId = ref<number | null>(null);
const showDeleteModal = ref(false);
const invoiceToDelete = ref<any>(null);
const activeFilter = ref<'open' | 'overdue' | 'paid' | 'all' | null>('open'); // Default to 'open' filter
const sortColumn = ref<
  'invoice' | 'unit' | 'amount' | 'dueDate' | 'status' | null
>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

// Queries and mutations
const {
  data: invoices,
  isLoading,
  error,
  refetch: refetchInvoices,
} = useInvoices(); // Fetch active invoices only

const deleteInvoiceMutation = useDeleteInvoice();

// Helper function to check if invoice is overdue
const isInvoiceOverdue = (invoice: any): boolean => {
  if (
    !invoice.start_date ||
    invoice.status === 'paid' ||
    invoice.status === 'cancelled'
  ) {
    return false;
  }
  const dueDate = new Date(invoice.start_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return dueDate < today;
};

// Helper function to check if invoice is open
const isInvoiceOpen = (invoice: any): boolean => {
  return invoice.status !== 'paid' && invoice.status !== 'cancelled';
};

// Computed properties for summary cards
const openInvoicesCount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value.filter(
    (invoice: any) => !invoice.deleted_at && isInvoiceOpen(invoice)
  ).length;
});

const openInvoicesAmount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value
    .filter((invoice: any) => !invoice.deleted_at && isInvoiceOpen(invoice))
    .reduce((sum: number, invoice: any) => sum + (invoice.balance_due || 0), 0);
});

const overdueInvoicesCount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value.filter(
    (invoice: any) => !invoice.deleted_at && isInvoiceOverdue(invoice)
  ).length;
});

const overdueInvoicesAmount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value
    .filter((invoice: any) => !invoice.deleted_at && isInvoiceOverdue(invoice))
    .reduce((sum: number, invoice: any) => sum + (invoice.balance_due || 0), 0);
});

const paidInvoicesCount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value.filter(
    (invoice: any) => !invoice.deleted_at && invoice.status === 'paid'
  ).length;
});

const paidInvoicesAmount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value
    .filter((invoice: any) => !invoice.deleted_at && invoice.status === 'paid')
    .reduce((sum: number, invoice: any) => sum + (invoice.total || 0), 0);
});

const allInvoicesCount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value.filter((invoice: any) => !invoice.deleted_at).length;
});

const allInvoicesAmount = computed(() => {
  if (!invoices.value) return 0;
  return invoices.value
    .filter((invoice: any) => !invoice.deleted_at)
    .reduce((sum: number, invoice: any) => sum + (invoice.total || 0), 0);
});

// Computed properties - filter invoices based on activeFilter and search
const filteredInvoices = computed(() => {
  if (!invoices.value) return [];

  let filtered = invoices.value.filter((invoice: any) => {
    // Apply active filter from summary cards
    if (activeFilter.value === 'open' && !isInvoiceOpen(invoice)) {
      return false;
    }

    if (activeFilter.value === 'overdue' && !isInvoiceOverdue(invoice)) {
      return false;
    }

    if (activeFilter.value === 'paid' && invoice.status !== 'paid') {
      return false;
    }

    // 'all' filter shows all invoices (no additional filtering needed)
    // If activeFilter is 'all', we don't filter by status

    // Apply search filter
    if (searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim();
      const invoiceNumber = (
        invoice.invoice_number || `#${invoice.id}`
      ).toLowerCase();
      const unitTitle = (invoice.unit?.title || '').toLowerCase();
      const unitAddress = (invoice.unit?.address || '').toLowerCase();
      const status = (invoice.status || '').toLowerCase();
      const total = String(invoice.total || '');

      return (
        invoiceNumber.includes(query) ||
        unitTitle.includes(query) ||
        unitAddress.includes(query) ||
        status.includes(query) ||
        total.includes(query)
      );
    }

    return true;
  });

  // Apply sorting
  if (sortColumn.value) {
    filtered = [...filtered].sort((a: any, b: any) => {
      let aValue: any;
      let bValue: any;

      switch (sortColumn.value) {
        case 'invoice':
          aValue = a.invoice_number || `#${a.id}`;
          bValue = b.invoice_number || `#${b.id}`;
          break;
        case 'unit':
          aValue = a.unit?.title || '';
          bValue = b.unit?.title || '';
          break;
        case 'amount':
          aValue = a.total || 0;
          bValue = b.total || 0;
          break;
        case 'dueDate':
          aValue = a.start_date ? new Date(a.start_date).getTime() : 0;
          bValue = b.start_date ? new Date(b.start_date).getTime() : 0;
          break;
        case 'status':
          aValue = a.status || '';
          bValue = b.status || '';
          break;
        default:
          return 0;
      }

      if (sortDirection.value === 'asc') {
        return aValue > bValue ? 1 : aValue < bValue ? -1 : 0;
      } else {
        return aValue < bValue ? 1 : aValue > bValue ? -1 : 0;
      }
    });
  }

  return filtered;
});

// Methods
const refetch = () => {
  refetchInvoices();
};

const filterByStatus = (status: 'open' | 'overdue' | 'paid' | 'all') => {
  // Toggle filter: if already active, clear it; otherwise, set it
  if (activeFilter.value === status) {
    activeFilter.value = null;
  } else {
    activeFilter.value = status;
  }
};

const sortBy = (
  column: 'invoice' | 'unit' | 'amount' | 'dueDate' | 'status'
) => {
  if (sortColumn.value === column) {
    // Toggle direction if clicking the same column
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column and default to ascending
    sortColumn.value = column;
    sortDirection.value = 'asc';
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

const getStatusText = (status: string) => {
  const statusMap: Record<string, string> = {
    draft: 'Draft',
    sent: 'Sent',
    paid: 'Paid',
    partial: 'Partial',
    overdue: 'Overdue',
    cancelled: 'Cancelled',
  };
  return statusMap[status] || 'Unknown';
};

const getStatusBadgeClass = (status: string) => {
  const statusClasses: Record<string, string> = {
    draft: 'badge-draft',
    sent: 'badge-sent',
    paid: 'badge-paid',
    partial: 'badge-partial',
    overdue: 'badge-overdue',
    cancelled: 'badge-partial',
  };
  return statusClasses[status] || 'badge-draft';
};

const viewInvoice = (invoiceId: number) => {
  router.push(`/invoices/${invoiceId}`);
};

const editInvoice = (invoiceId: number) => {
  router.push(`/invoices/${invoiceId}/edit`);
};

const recordPayment = (invoiceId: number) => {
  router.push(`/invoices/${invoiceId}/payment`);
};

const deleteInvoice = (invoice: any) => {
  // Store the invoice to delete and show the modal
  invoiceToDelete.value = invoice;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!invoiceToDelete.value) return;

  const invoiceId = invoiceToDelete.value.id;
  deletingInvoiceId.value = invoiceId;

  try {
    console.log('Starting delete for invoice:', invoiceId);
    const result = await deleteInvoiceMutation.mutateAsync(invoiceId);
    console.log('Delete result:', result);
    // Show success message
    console.log('Invoice deleted successfully');
    // Close modal
    showDeleteModal.value = false;
    invoiceToDelete.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error deleting invoice:', error);

    // Check if it's an authentication error
    if (error.message && error.message.includes('Invoice not found')) {
      // This might be an authentication issue
      alert('Authentication error. Please refresh the page and try again.');
    } else {
      // Show error message to user
      alert(`Failed to delete invoice: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    deletingInvoiceId.value = null;
    deleteInvoiceMutation.reset();
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  invoiceToDelete.value = null;
};

// Reset mutation states on component mount to clear any stuck states
onMounted(() => {
  deleteInvoiceMutation.reset();
});

// Global reset function for debugging (can be called from browser console)
(window as any).resetInvoiceMutations = () => {
  deleteInvoiceMutation.reset();
  deletingInvoiceId.value = null;
  console.log('Invoice mutations and local state reset');
};
</script>
