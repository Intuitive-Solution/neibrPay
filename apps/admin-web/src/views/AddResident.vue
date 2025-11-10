<template>
  <div class="max-w-4xl">
    <!-- Header Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between">
        <div class="mb-4 lg:mb-0">
          <div class="flex items-center gap-4 mb-2">
            <h1 class="text-2xl font-bold text-gray-900">
              {{ isEditMode ? 'Edit Resident' : 'Add New Resident' }}
            </h1>
          </div>
          <p class="text-gray-600">
            {{
              isEditMode
                ? 'Update resident information and details'
                : 'Add a new resident to your HOA community'
            }}
          </p>
        </div>
        <div class="flex items-center gap-3">
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
            Back to People
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

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Name Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="name"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Full Name <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="name"
              v-model="form.name"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.name,
              }"
              placeholder="Enter resident's full name"
            />
            <p v-if="errors.name" class="mt-2 text-sm text-red-600">
              {{ errors.name }}
            </p>
          </div>
        </div>

        <!-- Email Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="email"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Email Address <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="email"
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.email,
              }"
              placeholder="Enter email address"
            />
            <p v-if="errors.email" class="mt-2 text-sm text-red-600">
              {{ errors.email }}
            </p>
          </div>
        </div>

        <!-- Phone Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="phone"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Phone Number <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="phone"
              v-model="form.phone"
              type="tel"
              required
              @input="formatPhone"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.phone,
              }"
              placeholder="555-555-5555"
              maxlength="14"
            />
            <p v-if="errors.phone" class="mt-2 text-sm text-red-600">
              {{ errors.phone }}
            </p>
            <p class="mt-2 text-sm text-gray-600">
              Please enter a valid US phone number (10 digits). Format:
              XXX-XXX-XXXX
            </p>
          </div>
        </div>

        <!-- Role Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="role"
            class="block text-sm font-medium text-gray-700 lg:pt-3"
          >
            Role <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <select
              id="role"
              v-model="form.role"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-sm"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.role,
              }"
            >
              <option value="resident">Resident</option>
              <option value="admin">Admin</option>
            </select>
            <p v-if="errors.role" class="mt-2 text-sm text-red-600">
              {{ errors.role }}
            </p>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6">
          <!-- Spacer for large screens to align with form fields -->
          <div class="hidden lg:block"></div>

          <!-- Buttons Container -->
          <div class="lg:col-span-2 flex flex-col sm:flex-row gap-4">
            <!-- Primary Button -->
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 flex justify-center py-2 px-4 border border-transparent rounded-lg text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
            >
              <span v-if="isSubmitting" class="flex items-center">
                <svg
                  class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
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
                {{
                  isSubmitting
                    ? 'Saving...'
                    : isEditMode
                      ? 'Update Resident'
                      : 'Add Resident'
                }}
              </span>
              <span v-else>{{
                isEditMode ? 'Update Resident' : 'Add Resident'
              }}</span>
            </button>

            <!-- Cancel Button -->
            <button
              type="button"
              @click="goBack"
              class="flex-1 flex justify-center py-2 px-4 border border-gray-300 rounded-lg bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
            >
              Cancel
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- Tabs Section (only in edit mode) -->
    <div v-if="isEditMode" class="mt-8">
      <!-- Single White Container with Tabs and Content -->
      <div class="bg-white rounded-lg shadow">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8 px-6 pt-4">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              :class="[
                activeTab === tab.id
                  ? 'border-primary text-primary'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6">
          <!-- Units Tab -->
          <div v-if="activeTab === 'units'" class="space-y-4">
            <!-- Search Bar and Add Button -->
            <div class="flex items-center justify-between">
              <!-- Search Box -->
              <div class="relative flex-1 max-w-md">
                <div
                  class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                  <svg
                    class="h-5 w-5 text-gray-400"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 20 20"
                    fill="currentColor"
                  >
                    <path
                      fill-rule="evenodd"
                      d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </div>
                <input
                  v-model="searchQuery"
                  type="text"
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm"
                  placeholder="Search..."
                />
              </div>

              <!-- Add Button -->
              <div class="ml-4">
                <button
                  type="button"
                  @click="openAddUnitsModal"
                  class="ml-4 inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
                >
                  <svg
                    class="-ml-1 mr-2 h-5 w-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                    ></path>
                  </svg>
                  Add
                </button>
              </div>
            </div>

            <!-- Units Table -->
            <div class="overflow-hidden sm:rounded-md">
              <!-- Loading State -->
              <div
                v-if="isLoadingUnits"
                class="flex items-center justify-center py-8"
              >
                <svg
                  class="animate-spin h-8 w-8 text-primary"
                  xmlns="http://www.w3.org/2000/svg"
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
                <span class="ml-2 text-gray-600">Loading units...</span>
              </div>

              <!-- Table -->
              <table v-else class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-300">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Title
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Balance Amount
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Balance As Of Date
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Type
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="unit in filteredUnits" :key="unit.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                    >
                      {{ unit.title }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      ${{ unit.starting_balance }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{
                        new Date(unit.balance_as_of_date).toLocaleDateString()
                      }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      <select
                        :value="unit.pivot?.type || 'owner'"
                        @change="handleUnitTypeChange(unit.id, $event)"
                        :disabled="isUpdatingUnitType === unit.id"
                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full border-0 focus:ring-2 focus:ring-primary cursor-pointer transition-colors"
                        :class="[
                          unit.pivot?.type === 'owner'
                            ? 'bg-blue-100 text-blue-800 hover:bg-blue-200'
                            : 'bg-green-100 text-green-800 hover:bg-green-200',
                          isUpdatingUnitType === unit.id
                            ? 'opacity-50 cursor-not-allowed'
                            : '',
                        ]"
                      >
                        <option value="owner">Owner</option>
                        <option value="tenant">Tenant</option>
                      </select>
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      <button
                        @click="removeUnit(unit)"
                        class="text-red-600 hover:text-red-800"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                  <!-- Empty State -->
                  <tr v-if="filteredUnits.length === 0">
                    <td
                      colspan="5"
                      class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                      <div class="flex flex-col items-center">
                        <svg
                          class="h-12 w-12 text-gray-400 mb-2"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"
                          />
                        </svg>
                        <p class="text-gray-500">
                          No units assigned to this resident
                        </p>
                        <p class="text-gray-400 text-xs mt-1">
                          Click "Add" to assign units
                        </p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Invoices Tab -->
          <div v-if="activeTab === 'invoices'" class="space-y-4">
            <!-- Invoices Table -->
            <div class="overflow-hidden sm:rounded-md">
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
                      Description
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Amount
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="invoice in invoices" :key="invoice.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                    >
                      {{ invoice.date }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ invoice.description }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      ${{ invoice.amount }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      <span
                        :class="[
                          'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                          invoice.status === 'paid'
                            ? 'bg-green-100 text-green-800'
                            : invoice.status === 'pending'
                              ? 'bg-yellow-100 text-yellow-800'
                              : 'bg-red-100 text-red-800',
                        ]"
                      >
                        {{ invoice.status }}
                      </span>
                    </td>
                  </tr>
                  <!-- Empty State -->
                  <tr v-if="invoices.length === 0">
                    <td
                      colspan="4"
                      class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                      <div class="flex flex-col items-center">
                        <svg
                          class="h-12 w-12 text-gray-400 mb-2"
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
                        <p class="text-gray-500">No invoices found</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- History Tab -->
          <div v-if="activeTab === 'history'" class="space-y-4">
            <!-- History Table -->
            <div class="overflow-hidden sm:rounded-md">
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
                      Description
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Remark
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="history in residentHistory" :key="history.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"
                    >
                      {{ history.date }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ history.description }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ history.remark }}
                    </td>
                  </tr>
                  <!-- Empty State -->
                  <tr v-if="residentHistory.length === 0">
                    <td
                      colspan="3"
                      class="px-6 py-8 text-center text-sm text-gray-500"
                    >
                      <div class="flex flex-col items-center">
                        <svg
                          class="h-12 w-12 text-gray-400 mb-2"
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
                        <p class="text-gray-500">No history found</p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Remove Unit Confirmation Modal -->
    <div
      v-if="showRemoveUnitModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
      >
        <!-- Background overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="closeRemoveUnitModal"
        ></div>

        <!-- Modal panel -->
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <!-- Modal header -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                Remove Unit
              </h3>
              <button
                @click="closeRemoveUnitModal"
                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
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

            <!-- Warning message -->
            <div class="flex items-start mb-4">
              <div class="flex-shrink-0">
                <svg
                  class="h-6 w-6 text-red-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"
                  />
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-gray-700">
                  Are you sure you want to remove this unit from this resident?
                </p>
              </div>
            </div>

            <!-- Unit details -->
            <div v-if="unitToRemove" class="bg-gray-50 rounded-lg p-4 mb-4">
              <div class="space-y-2">
                <div>
                  <span class="font-medium text-gray-900">Unit:</span>
                  <span class="ml-2 text-gray-700">{{
                    unitToRemove.title
                  }}</span>
                </div>
                <div>
                  <span class="font-medium text-gray-900">Balance:</span>
                  <span class="ml-2 text-gray-700"
                    >${{ unitToRemove.starting_balance }}</span
                  >
                </div>
                <div>
                  <span class="font-medium text-gray-900">Balance Date:</span>
                  <span class="ml-2 text-gray-700">{{
                    new Date(
                      unitToRemove.balance_as_of_date
                    ).toLocaleDateString()
                  }}</span>
                </div>
              </div>
            </div>

            <!-- Explanation -->
            <p class="text-sm text-gray-600">
              This will remove the ownership relationship between this resident
              and the unit. The resident will no longer be associated with this
              unit.
            </p>
          </div>

          <!-- Modal footer -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="confirmRemoveUnit"
              :disabled="isRemovingUnit"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isRemovingUnit" class="flex items-center">
                <svg
                  class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
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
                Removing...
              </span>
              <span v-else>Remove Unit</span>
            </button>
            <button
              @click="closeRemoveUnitModal"
              :disabled="isRemovingUnit"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Units Modal -->
    <div
      v-if="showAddUnitsModal"
      class="fixed inset-0 z-50 overflow-y-auto"
      aria-labelledby="add-units-modal-title"
      role="dialog"
      aria-modal="true"
    >
      <div
        class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
      >
        <!-- Background overlay -->
        <div
          class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
          aria-hidden="true"
          @click="closeAddUnitsModal"
        ></div>

        <!-- Modal panel -->
        <div
          class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
        >
          <!-- Modal header -->
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="flex items-center justify-between mb-4">
              <h3
                class="text-lg font-medium text-gray-900"
                id="add-units-modal-title"
              >
                Add Units to Resident
              </h3>
              <button
                @click="closeAddUnitsModal"
                class="text-gray-400 hover:text-gray-600 focus:outline-none focus:text-gray-600"
              >
                <svg
                  class="h-6 w-6"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
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

            <!-- Instructions -->
            <p class="text-sm text-gray-600 mb-4">
              Select available units to add as owned by this resident. You can
              select multiple units.
            </p>

            <!-- Search bar -->
            <div class="relative mb-4">
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
                v-model="addUnitsSearchQuery"
                type="text"
                class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm"
                placeholder="Search units..."
              />
            </div>

            <!-- Units list -->
            <div
              class="max-h-64 overflow-y-auto border border-gray-200 rounded-md"
            >
              <!-- Loading State -->
              <div
                v-if="isLoadingAvailableUnits"
                class="flex items-center justify-center py-8"
              >
                <svg
                  class="animate-spin h-8 w-8 text-primary"
                  xmlns="http://www.w3.org/2000/svg"
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
                <span class="ml-2 text-gray-600"
                  >Loading available units...</span
                >
              </div>

              <!-- Empty State -->
              <div
                v-else-if="availableUnitsForResident.length === 0"
                class="p-4 text-center text-gray-500"
              >
                <p v-if="addUnitsSearchQuery">
                  No units found matching your search.
                </p>
                <p v-else>No available units to add.</p>
              </div>
              <div v-else class="divide-y divide-gray-200">
                <div
                  v-for="unit in availableUnitsForResident"
                  :key="unit.id"
                  class="p-4 hover:bg-gray-50"
                >
                  <div class="flex items-start">
                    <input
                      :checked="isUnitSelected(unit.id)"
                      type="checkbox"
                      class="h-4 w-4 mt-1 text-primary focus:ring-primary border-gray-300 rounded"
                      @change="toggleUnitSelection(unit.id)"
                    />
                    <div class="ml-3 flex-1">
                      <div class="text-sm font-medium text-gray-900">
                        {{ unit.title }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ unit.address }}, {{ unit.city }}, {{ unit.state }}
                        {{ unit.zip_code }}
                      </div>
                      <div class="text-xs text-gray-400 mt-1">
                        Balance: ${{ unit.starting_balance }} • Date:
                        {{
                          new Date(unit.balance_as_of_date).toLocaleDateString()
                        }}
                      </div>
                      <!-- Type selector for selected units -->
                      <div
                        v-if="isUnitSelected(unit.id)"
                        class="mt-3 flex items-center space-x-4"
                      >
                        <label class="text-sm font-medium text-gray-700"
                          >Type:</label
                        >
                        <div class="flex space-x-4">
                          <label class="flex items-center">
                            <input
                              type="radio"
                              name="unit-type-{{
                                unit.id
                              }}"
                              value="owner"
                              :checked="
                                getSelectedUnitType(unit.id) === 'owner'
                              "
                              @change="updateUnitType(unit.id, 'owner')"
                              class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                            />
                            <span class="ml-2 text-sm text-gray-700"
                              >Owner</span
                            >
                          </label>
                          <label class="flex items-center">
                            <input
                              type="radio"
                              name="unit-type-{{
                                unit.id
                              }}"
                              value="tenant"
                              :checked="
                                getSelectedUnitType(unit.id) === 'tenant'
                              "
                              @change="updateUnitType(unit.id, 'tenant')"
                              class="h-4 w-4 text-primary focus:ring-primary border-gray-300"
                            />
                            <span class="ml-2 text-sm text-gray-700"
                              >Tenant</span
                            >
                          </label>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Modal footer -->
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              @click="confirmAddUnits"
              :disabled="selectedUnits.length === 0 || isAddingUnits"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-primary text-base font-medium text-white hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="isAddingUnits" class="flex items-center">
                <svg
                  class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
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
                Adding...
              </span>
              <span v-else
                >Add {{ selectedUnits.length }} Unit{{
                  selectedUnits.length !== 1 ? 's' : ''
                }}</span
              >
            </button>
            <button
              @click="closeAddUnitsModal"
              :disabled="isAddingUnits"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
  useCreateResident,
  useUpdateResident,
  useResident,
  useResidentUnits,
  useAvailableUnitsForResident,
} from '../composables/useResidents';
import { residentsApi } from '@neibrpay/api-client';
import {
  validateResidentForm,
  validateUpdateResidentForm,
} from '@neibrpay/models';
import type {
  ResidentFormData,
  ResidentFormErrors,
  CreateResidentRequest,
} from '@neibrpay/models';

const route = useRoute();
const router = useRouter();

// Determine if we're in edit mode
const residentId = computed(() => {
  const id = route.params.id;
  return id ? parseInt(id as string) : null;
});

const isEditMode = computed(() => !!residentId.value);

// Form data
const form = ref<ResidentFormData>({
  name: '',
  email: '',
  phone: '',
  role: 'resident',
});

// Form errors
const errors = ref<ResidentFormErrors>({});

// Loading states
const isSubmitting = ref(false);

// Tab state
const activeTab = ref('units');
const searchQuery = ref('');

// Modal state
const showRemoveUnitModal = ref(false);
const unitToRemove = ref<any>(null);
const isRemovingUnit = ref(false);

// Add units modal state
const showAddUnitsModal = ref(false);
const selectedUnits = ref<Array<{ unit_id: number; type: 'owner' | 'tenant' }>>(
  []
);
const isAddingUnits = ref(false);
const addUnitsSearchQuery = ref('');

// Unit type update state
const isUpdatingUnitType = ref<number | null>(null);

// Tabs configuration
const tabs = [
  { id: 'units', name: 'Units' },
  { id: 'invoices', name: 'Invoices' },
  { id: 'history', name: 'History' },
];

// Get units for the resident from API
const {
  data: units,
  isLoading: isLoadingUnits,
  refetch: refetchUnits,
} = useResidentUnits(residentId.value!);

// Get available units for the resident from API
const {
  data: availableUnits,
  isLoading: isLoadingAvailableUnits,
  refetch: refetchAvailableUnits,
} = useAvailableUnitsForResident(residentId.value!);

const invoices = ref([
  {
    id: 1,
    date: '2024-01-15',
    description: 'Monthly HOA Fee',
    amount: '150.00',
    status: 'paid',
  },
  {
    id: 2,
    date: '2024-01-10',
    description: 'Late Fee',
    amount: '25.00',
    status: 'pending',
  },
]);

const residentHistory = ref([
  {
    id: 1,
    date: '2024-01-15',
    description: 'Account created',
    remark: 'Initial setup',
  },
  {
    id: 2,
    date: '2024-01-10',
    description: 'Profile updated',
    remark: 'Phone number changed',
  },
]);

// Computed properties
const filteredUnits = computed(() => {
  if (!units.value) return [];
  if (!searchQuery.value) return units.value;
  return units.value.filter(
    (unit: any) =>
      unit.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      unit.starting_balance.toString().includes(searchQuery.value) ||
      unit.balance_as_of_date.includes(searchQuery.value)
  );
});

// Filter available units for the add modal
const filteredAvailableUnits = computed(() => {
  if (!availableUnits.value) return [];
  if (!addUnitsSearchQuery.value) return availableUnits.value;
  return availableUnits.value.filter(
    (unit: any) =>
      unit.title
        .toLowerCase()
        .includes(addUnitsSearchQuery.value.toLowerCase()) ||
      unit.address
        .toLowerCase()
        .includes(addUnitsSearchQuery.value.toLowerCase()) ||
      unit.city.toLowerCase().includes(addUnitsSearchQuery.value.toLowerCase())
  );
});

// Use the filtered available units directly since the API already filters out assigned units
const availableUnitsForResident = computed(() => {
  return filteredAvailableUnits.value;
});

// Methods
const removeUnit = (unit: any) => {
  unitToRemove.value = unit;
  showRemoveUnitModal.value = true;
};

const closeRemoveUnitModal = () => {
  showRemoveUnitModal.value = false;
  unitToRemove.value = null;
  isRemovingUnit.value = false;
};

const confirmRemoveUnit = async () => {
  if (!unitToRemove.value || !residentId.value) return;

  isRemovingUnit.value = true;

  try {
    // Call API to remove unit from resident
    await residentsApi.removeResidentUnit(
      residentId.value,
      unitToRemove.value.id
    );

    // Close modal and refresh units
    closeRemoveUnitModal();
    refetchUnits();
  } catch (error) {
    console.error('Error removing unit:', error);
    alert('Failed to remove unit. Please try again.');
  } finally {
    isRemovingUnit.value = false;
  }
};

// Add units modal methods
const openAddUnitsModal = () => {
  selectedUnits.value = [];
  addUnitsSearchQuery.value = '';
  showAddUnitsModal.value = true;
  // Refetch available units when opening the modal
  refetchAvailableUnits();
};

const closeAddUnitsModal = () => {
  showAddUnitsModal.value = false;
  selectedUnits.value = [];
  addUnitsSearchQuery.value = '';
  isAddingUnits.value = false;
};

const toggleUnitSelection = (unitId: number) => {
  const index = selectedUnits.value.findIndex(
    (u: { unit_id: number; type: 'owner' | 'tenant' }) => u.unit_id === unitId
  );
  if (index > -1) {
    selectedUnits.value.splice(index, 1);
  } else {
    selectedUnits.value.push({ unit_id: unitId, type: 'owner' });
  }
};

const updateUnitType = (unitId: number, type: 'owner' | 'tenant') => {
  const index = selectedUnits.value.findIndex(
    (u: { unit_id: number; type: 'owner' | 'tenant' }) => u.unit_id === unitId
  );
  if (index > -1) {
    selectedUnits.value[index].type = type;
  }
};

const isUnitSelected = (unitId: number): boolean => {
  return selectedUnits.value.some(
    (u: { unit_id: number; type: 'owner' | 'tenant' }) => u.unit_id === unitId
  );
};

const getSelectedUnitType = (unitId: number): 'owner' | 'tenant' => {
  const selected = selectedUnits.value.find(
    (u: { unit_id: number; type: 'owner' | 'tenant' }) => u.unit_id === unitId
  );
  return selected?.type || 'owner';
};

const confirmAddUnits = async () => {
  if (selectedUnits.value.length === 0) return;

  isAddingUnits.value = true;

  try {
    // Call API to add units to resident
    await residentsApi.addResidentUnits(residentId.value!, selectedUnits.value);

    // Close modal and refresh units
    closeAddUnitsModal();
    refetchUnits();
    refetchAvailableUnits();
  } catch (error) {
    console.error('Error adding units:', error);
    alert('Failed to add units. Please try again.');
  } finally {
    isAddingUnits.value = false;
  }
};

const handleUnitTypeChange = (unitId: number, event: Event) => {
  const target = event.target as HTMLSelectElement;
  const type = target.value as 'owner' | 'tenant';
  updateUnitTypeForResident(unitId, type);
};

const updateUnitTypeForResident = async (
  unitId: number,
  type: 'owner' | 'tenant'
) => {
  if (!residentId.value) return;

  isUpdatingUnitType.value = unitId;

  try {
    await residentsApi.updateResidentUnitType(residentId.value, unitId, type);
    // Refresh units to get updated data
    refetchUnits();
  } catch (error) {
    console.error('Error updating unit type:', error);
    alert('Failed to update unit type. Please try again.');
    // Refresh units to revert UI state
    refetchUnits();
  } finally {
    isUpdatingUnitType.value = null;
  }
};

// Queries and mutations
const { data: resident, isLoading: isLoadingResident } = useResident(
  residentId.value!
);
const createResidentMutation = useCreateResident();
const updateResidentMutation = useUpdateResident();

// Load resident data for editing
onMounted(() => {
  if (isEditMode.value && resident.value) {
    form.value = {
      name: resident.value.name,
      email: resident.value.email,
      phone: resident.value.phone,
      role: resident.value.role || 'resident',
    };
  }

  // Refetch units when component mounts in edit mode
  if (isEditMode.value && residentId.value) {
    refetchUnits();
  }
});

// Watch for resident data changes
watch(resident, (newResident: any) => {
  if (newResident && isEditMode.value) {
    form.value = {
      name: newResident.name,
      email: newResident.email,
      phone: newResident.phone,
      role: newResident.role || 'resident',
    };
  }
});

// Watch for resident ID changes to refetch units
watch(residentId, (newId: number | null) => {
  if (newId && isEditMode.value) {
    refetchUnits();
  }
});

// Watch for tab changes to refetch units when switching to Units tab
watch(activeTab, (newTab: string) => {
  if (newTab === 'units' && isEditMode.value && residentId.value) {
    refetchUnits();
  }
});

// Phone formatting - match signup form format (XXX-XXX-XXXX)
const formatPhone = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const value = target.value;

  // Remove all non-numeric characters
  const phoneNumber = value.replace(/\D/g, '');

  // Limit to 10 digits
  const limitedPhoneNumber = phoneNumber.substring(0, 10);

  // Format as XXX-XXX-XXXX
  let formatted = '';
  if (limitedPhoneNumber.length >= 6) {
    formatted = `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3, 6)}-${limitedPhoneNumber.substring(6)}`;
  } else if (limitedPhoneNumber.length >= 3) {
    formatted = `${limitedPhoneNumber.substring(0, 3)}-${limitedPhoneNumber.substring(3)}`;
  } else {
    formatted = limitedPhoneNumber;
  }

  form.value.phone = formatted;
  target.value = formatted;
};

// Form submission
const handleSubmit = async () => {
  // Clear previous errors
  errors.value = {};

  // Validate form
  const validation = isEditMode.value
    ? validateUpdateResidentForm(form.value)
    : validateResidentForm(form.value);

  if (!validation.success) {
    errors.value = validation.errors || {};
    return;
  }

  isSubmitting.value = true;

  try {
    if (isEditMode.value && residentId.value) {
      await updateResidentMutation.mutateAsync({
        id: residentId.value,
        data: validation.data as any,
      });
    } else {
      await createResidentMutation.mutateAsync(
        validation.data! as CreateResidentRequest
      );
    }

    // Success - redirect back to people page
    router.push('/people');
  } catch (error: any) {
    // Handle API errors
    if (error.errors) {
      errors.value = error.errors;
    } else {
      errors.value.general = error.message || 'An unexpected error occurred';
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Navigation
const goBack = () => {
  router.push('/people');
};

// Show loading state while fetching resident data
if (isEditMode.value && isLoadingResident.value) {
  // You could show a loading spinner here
}
</script>
