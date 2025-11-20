<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <!-- Unpaid Expenses Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-red-500': activeFilter === 'unpaid',
        }"
        @click="filterByStatus('unpaid')"
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
                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Unpaid Expenses</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ unpaidExpensesCount }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              ${{ formatCurrency(unpaidExpensesAmount) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Paid Expenses Card -->
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
            <h3 class="text-sm font-medium text-gray-600">Paid Expenses</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ paidExpensesCount }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              ${{ formatCurrency(paidExpensesAmount) }}
            </p>
          </div>
        </div>
      </div>

      <!-- All Expenses Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-blue-500': activeFilter === 'all',
        }"
        @click="filterByStatus('all')"
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
            <h3 class="text-sm font-medium text-gray-600">All Expenses</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ allExpensesCount }}
            </p>
            <p class="text-sm text-gray-500 mt-1">
              ${{ formatCurrency(allExpensesAmount) }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Expenses Directory Section -->
    <div class="card-modern bg-white rounded-lg shadow-sm">
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
                placeholder="Search expenses..."
                class="input-field pl-10 w-full"
                @input="debouncedSearch"
              />
            </div>
          </div>

          <!-- Header Controls (Right) -->
          <div class="flex items-center space-x-3">
            <!-- Category Filter -->
            <div class="flex items-center">
              <select
                v-model="categoryFilter"
                class="input-field text-sm"
                @change="applyFilters"
              >
                <option value="">All Categories</option>
                <option
                  v-for="option in categoryOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
            </div>

            <!-- Show Deleted Checkbox - Hidden for residents -->
            <div v-if="!isResident" class="flex items-center">
              <input
                id="show-deleted"
                v-model="includeDeleted"
                type="checkbox"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                @change="applyFilters"
              />
              <label for="show-deleted" class="ml-2 text-sm text-gray-700">
                Include Deleted
              </label>
            </div>

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

            <!-- New Expense Button (Desktop) - Hidden for residents -->
            <router-link
              v-if="!isResident"
              to="/expenses/create"
              class="hidden md:inline-flex"
            >
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
            <span class="text-sm text-gray-600">Loading expenses...</span>
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
              Error loading expenses
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
          v-else-if="!filteredExpenses || filteredExpenses.length === 0"
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
              No expenses found
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                searchQuery
                  ? 'Try adjusting your search query.'
                  : isResident
                    ? 'No expenses found.'
                    : 'Get started by adding your first expense.'
              }}
            </p>
            <div v-if="!searchQuery && !isResident" class="mt-4">
              <router-link to="/expenses/create">
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
                  Add Expense
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
                @click="sortBy('invoice_number')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>INVOICE</span>
                  <svg
                    v-if="sortColumn === 'invoice_number'"
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
                @click="sortBy('vendor')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>VENDOR</span>
                  <svg
                    v-if="sortColumn === 'vendor'"
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
                @click="sortBy('invoice_amount')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>AMOUNT</span>
                  <svg
                    v-if="sortColumn === 'invoice_amount'"
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
                @click="sortBy('invoice_due_date')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>DUE DATE</span>
                  <svg
                    v-if="sortColumn === 'invoice_due_date'"
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
              v-for="expense in filteredExpenses"
              :key="expense.id"
              class="table-row-hover"
            >
              <!-- Invoice Column -->
              <td
                @click.stop="!expense.deleted_at && viewExpense(expense.id)"
                :class="[
                  'px-6 py-4 whitespace-nowrap',
                  !expense.deleted_at
                    ? 'cursor-pointer hover:bg-gray-50 transition-colors'
                    : '',
                ]"
              >
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div
                        :class="[
                          'h-10 w-10 rounded-full flex items-center justify-center',
                          expense.deleted_at ? 'bg-red-100' : 'bg-gray-100',
                        ]"
                      >
                        <svg
                          :class="[
                            'h-5 w-5',
                            expense.deleted_at
                              ? 'text-red-600'
                              : 'text-gray-600',
                          ]"
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
                      <div class="flex items-center space-x-2">
                        <div
                          :class="[
                            'text-sm font-medium',
                            expense.deleted_at
                              ? 'text-red-600 line-through'
                              : 'text-gray-900',
                          ]"
                        >
                          {{ expense.invoice_number }}
                        </div>
                        <span
                          v-if="expense.deleted_at"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"
                        >
                          Deleted
                        </span>
                      </div>
                      <!-- Mobile-only additional info -->
                      <div class="sm:hidden mt-1">
                        <div
                          :class="[
                            'text-xs',
                            expense.deleted_at
                              ? 'text-red-400'
                              : 'text-gray-500',
                          ]"
                        >
                          {{ expense.vendor?.name || 'N/A' }} • ${{
                            formatCurrency(expense.invoice_amount)
                          }}
                        </div>
                        <div class="mt-1">
                          <span
                            v-if="expense.deleted_at"
                            class="badge badge-overdue text-xs"
                          >
                            Deleted
                          </span>
                          <span
                            v-else
                            :class="getExpenseStatusBadgeClass(expense.status)"
                            class="badge text-xs"
                          >
                            {{ getExpenseStatusDisplayName(expense.status) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Vendor Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <div class="flex flex-col">
                  <div
                    :class="[
                      'text-sm font-medium',
                      expense.deleted_at
                        ? 'text-red-600 line-through'
                        : 'text-gray-900',
                    ]"
                  >
                    {{ expense.vendor?.name || 'N/A' }}
                  </div>
                  <div
                    :class="[
                      'text-sm',
                      expense.deleted_at ? 'text-red-400' : 'text-gray-500',
                    ]"
                  >
                    {{ getExpenseCategoryDisplayName(expense.category) }}
                  </div>
                </div>
              </td>

              <!-- Amount Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                <div class="flex flex-col">
                  <div
                    :class="[
                      'text-sm font-medium',
                      expense.deleted_at
                        ? 'text-red-600 line-through'
                        : 'text-gray-900',
                    ]"
                  >
                    ${{ formatCurrency(expense.invoice_amount) }}
                  </div>
                  <div
                    :class="[
                      'text-sm',
                      expense.deleted_at ? 'text-red-400' : 'text-gray-500',
                    ]"
                  >
                    Balance: ${{
                      formatCurrency(
                        expense.invoice_amount - expense.paid_amount
                      )
                    }}
                  </div>
                </div>
              </td>

              <!-- Due Date Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                <div
                  :class="[
                    'text-sm',
                    expense.deleted_at
                      ? 'text-red-600 line-through'
                      : 'text-gray-900',
                  ]"
                >
                  {{ formatDate(expense.invoice_due_date) }}
                </div>
              </td>

              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <span v-if="expense.deleted_at" class="badge badge-overdue">
                  Deleted
                </span>
                <span
                  v-else
                  :class="getExpenseStatusBadgeClass(expense.status)"
                  class="badge"
                >
                  {{ getExpenseStatusDisplayName(expense.status) }}
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
                      <!-- Actions for deleted expenses - Hidden for residents -->
                      <template v-if="expense.deleted_at && !isResident">
                        <button
                          @click="
                            () => {
                              showRestoreConfirmation(expense);
                              close();
                            }
                          "
                          :disabled="restoringExpenseId === expense.id"
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
                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                            />
                          </svg>
                          {{
                            restoringExpenseId === expense.id
                              ? 'Restoring...'
                              : 'Restore'
                          }}
                        </button>
                      </template>

                      <!-- Actions for active expenses -->
                      <template v-else>
                        <button
                          @click="
                            () => {
                              viewExpense(expense.id);
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
                          View
                        </button>
                        <!-- Edit and Delete - Hidden for residents -->
                        <template v-if="!isResident">
                          <button
                            @click="
                              () => {
                                editExpense(expense.id);
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
                          <div class="border-t border-gray-200 my-1"></div>
                          <button
                            @click="
                              () => {
                                deleteExpense(expense);
                                close();
                              }
                            "
                            :disabled="deletingExpenseId === expense.id"
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
                              deletingExpenseId === expense.id
                                ? 'Deleting...'
                                : 'Delete'
                            }}
                          </button>
                        </template>
                      </template>
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
      title="Delete Expense"
      :message="`Are you sure you want to delete ${expenseToDelete?.invoice_number || 'this expense'}? This action can be undone by restoring the expense.`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletingExpenseId === expenseToDelete?.id"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Restore Confirmation Modal -->
    <ConfirmDialog
      :is-open="showRestoreModal"
      title="Restore Expense"
      :message="`Are you sure you want to restore ${expenseToRestore?.invoice_number || 'this expense'}? This will make the expense active again.`"
      confirm-text="Restore"
      cancel-text="Cancel"
      type="info"
      :is-loading="restoringExpenseId === expenseToRestore?.id"
      @confirm="confirmRestore"
      @cancel="cancelRestore"
    />

    <!-- Mobile Fixed Bottom Button - Hidden for residents -->
    <div
      v-if="!isResident"
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/expenses/create" class="block">
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
          Add Expense
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import {
  useExpenses,
  useDeleteExpense,
  useRestoreExpense,
} from '../composables/useExpenses';
import { useAuthStore } from '../stores/auth';
import {
  getExpenseCategoryDisplayName,
  getExpenseCategoryOptions,
  getExpenseStatusDisplayName,
  getExpenseStatusBadgeClass,
} from '@neibrpay/models';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import DropdownMenu from '../components/DropdownMenu.vue';

const router = useRouter();
const authStore = useAuthStore();

// Role check
const isResident = computed(() => authStore.isResident);

// Local state
const searchQuery = ref('');
const categoryFilter = ref('');
const includeDeleted = ref(false);
const deletingExpenseId = ref<number | null>(null);
const restoringExpenseId = ref<number | null>(null);
const showDeleteModal = ref(false);
const showRestoreModal = ref(false);
const expenseToDelete = ref<any>(null);
const expenseToRestore = ref<any>(null);
const activeFilter = ref<'unpaid' | 'paid' | 'all' | null>('unpaid'); // Default to 'unpaid' filter
const sortColumn = ref<
  | 'invoice_number'
  | 'vendor'
  | 'invoice_amount'
  | 'invoice_due_date'
  | 'status'
  | null
>(null);
const sortDirection = ref<'asc' | 'desc'>('asc');

// Queries and mutations
const {
  data: expenses,
  isLoading,
  error,
  refetch: refetchExpenses,
} = useExpenses({ include_deleted: true }); // Always fetch all expenses including deleted ones

const deleteExpenseMutation = useDeleteExpense();
const restoreExpenseMutation = useRestoreExpense();

// Category options
const categoryOptions = getExpenseCategoryOptions();

// Helper function to check if expense is unpaid
const isExpenseUnpaid = (expense: any): boolean => {
  return expense.status === 'unpaid' || expense.status === 'pending';
};

// Helper function to check if expense is paid
const isExpensePaid = (expense: any): boolean => {
  return expense.status === 'paid';
};

// Computed properties for summary cards
const unpaidExpensesCount = computed(() => {
  if (!expenses.value) return 0;
  return expenses.value.filter(
    (expense: any) => isExpenseUnpaid(expense) && !expense.deleted_at
  ).length;
});

const unpaidExpensesAmount = computed(() => {
  if (!expenses.value) return 0;
  return expenses.value
    .filter((expense: any) => isExpenseUnpaid(expense) && !expense.deleted_at)
    .reduce(
      (total: number, expense: any) =>
        total + (expense.invoice_amount - expense.paid_amount),
      0
    );
});

const paidExpensesCount = computed(() => {
  if (!expenses.value) return 0;
  return expenses.value.filter(
    (expense: any) => isExpensePaid(expense) && !expense.deleted_at
  ).length;
});

const paidExpensesAmount = computed(() => {
  if (!expenses.value) return 0;
  return expenses.value
    .filter((expense: any) => isExpensePaid(expense) && !expense.deleted_at)
    .reduce((total: number, expense: any) => total + expense.invoice_amount, 0);
});

const allExpensesCount = computed(() => {
  if (!expenses.value) return 0;
  return expenses.value.filter((expense: any) => !expense.deleted_at).length;
});

const allExpensesAmount = computed(() => {
  if (!expenses.value) return 0;
  return expenses.value
    .filter((expense: any) => !expense.deleted_at)
    .reduce((total: number, expense: any) => total + expense.invoice_amount, 0);
});

// Computed properties - filter expenses based on activeFilter and search
const filteredExpenses = computed(() => {
  if (!expenses.value) return [];

  let filtered = expenses.value.filter((expense: any) => {
    // For members: hide deleted expenses
    if (isResident.value && expense.deleted_at) {
      return false;
    }

    // Apply active filter from summary cards
    if (activeFilter.value === 'unpaid' && !isExpenseUnpaid(expense)) {
      return false;
    }
    if (activeFilter.value === 'paid' && !isExpensePaid(expense)) {
      return false;
    }

    // 'all' filter shows all expenses (no additional filtering needed)
    // If activeFilter is 'all', we don't filter by status

    // Apply category filter
    if (categoryFilter.value && expense.category !== categoryFilter.value) {
      return false;
    }

    // Apply search filter
    if (searchQuery.value && searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim();
      const invoiceNumber = (expense.invoice_number || '').toLowerCase();
      const category = getExpenseCategoryDisplayName(
        expense.category
      ).toLowerCase();

      return invoiceNumber.includes(query) || category.includes(query);
    }

    // Filter out deleted expenses unless includeDeleted is true (admin only)
    if (expense.deleted_at && !includeDeleted.value) {
      return false;
    }

    return true;
  });

  // Apply sorting
  if (sortColumn.value) {
    filtered = [...filtered].sort((a: any, b: any) => {
      let aValue: any;
      let bValue: any;

      switch (sortColumn.value) {
        case 'invoice_number':
          aValue = a.invoice_number || '';
          bValue = b.invoice_number || '';
          break;
        case 'vendor':
          aValue = a.vendor?.name || '';
          bValue = b.vendor?.name || '';
          break;
        case 'invoice_amount':
          aValue = a.invoice_amount || 0;
          bValue = b.invoice_amount || 0;
          break;
        case 'invoice_due_date':
          aValue = new Date(a.invoice_due_date || '');
          bValue = new Date(b.invoice_due_date || '');
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

// Debounced search
let searchTimeout: NodeJS.Timeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

// Methods
const refetch = () => {
  refetchExpenses();
};

const filterByStatus = (status: 'unpaid' | 'paid' | 'all') => {
  // Toggle filter: if already active, clear it; otherwise, set it
  if (activeFilter.value === status) {
    activeFilter.value = null;
  } else {
    activeFilter.value = status;
  }
};

const sortBy = (
  column:
    | 'invoice_number'
    | 'vendor'
    | 'invoice_amount'
    | 'invoice_due_date'
    | 'status'
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

const applyFilters = () => {
  // The query will automatically refetch when filters change
  // No manual refetch needed
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

const viewExpense = (expenseId: number) => {
  router.push(`/expenses/${expenseId}`);
};

const editExpense = (expenseId: number) => {
  router.push(`/expenses/${expenseId}/edit`);
};

const deleteExpense = (expense: any) => {
  // Store the expense to delete and show the modal
  expenseToDelete.value = expense;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!expenseToDelete.value) return;

  const expenseId = expenseToDelete.value.id;
  deletingExpenseId.value = expenseId;

  try {
    console.log('Starting delete for expense:', expenseId);
    const result = await deleteExpenseMutation.mutateAsync(expenseId);
    console.log('Delete result:', result);
    // Show success message
    console.log('Expense deleted successfully');
    // Close modal
    showDeleteModal.value = false;
    expenseToDelete.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error deleting expense:', error);

    // Check if it's an authentication error
    if (error.message && error.message.includes('Expense not found')) {
      // This might be an authentication issue
      alert('Authentication error. Please refresh the page and try again.');
    } else {
      // Show error message to user
      alert(`Failed to delete expense: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    deletingExpenseId.value = null;
    deleteExpenseMutation.reset();
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  expenseToDelete.value = null;
};

const showRestoreConfirmation = (expense: any) => {
  expenseToRestore.value = expense;
  showRestoreModal.value = true;
};

const confirmRestore = async () => {
  if (!expenseToRestore.value) return;

  const expenseId = expenseToRestore.value.id;
  restoringExpenseId.value = expenseId;

  try {
    console.log('Starting restore for expense:', expenseId);
    const result = await restoreExpenseMutation.mutateAsync(expenseId);
    console.log('Restore result:', result);
    // Show success message
    console.log('Expense restored successfully');
    // Close modal
    showRestoreModal.value = false;
    expenseToRestore.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error restoring expense:', error);

    // Check if it's an authentication error or expense not found
    if (error.message && error.message.includes('Expense not found')) {
      // This might be an authentication issue or the expense was already restored
      alert(
        'Expense not found. It may have already been restored or deleted permanently.'
      );
    } else {
      // Show error message to user
      alert(`Failed to restore expense: ${error.message || 'Unknown error'}`);
    }
  } finally {
    // Always reset the loading state
    restoringExpenseId.value = null;
    restoreExpenseMutation.reset();
  }
};

const cancelRestore = () => {
  showRestoreModal.value = false;
  expenseToRestore.value = null;
};

// Watch for changes in include_deleted to update filter
watch(
  () => includeDeleted.value,
  (newValue: boolean) => {
    // When showing deleted expenses, automatically set filter to 'all'
    if (newValue) {
      activeFilter.value = 'all';
    }
  }
);

// Reset mutation states on component mount to clear any stuck states
onMounted(() => {
  deleteExpenseMutation.reset();
  restoreExpenseMutation.reset();
});

// Global reset function for debugging (can be called from browser console)
(window as any).resetExpenseMutations = () => {
  deleteExpenseMutation.reset();
  restoreExpenseMutation.reset();
  deletingExpenseId.value = null;
  restoringExpenseId.value = null;
  console.log('Expense mutations and local state reset');
};
</script>
