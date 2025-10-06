<template>
  <div class="max-w-6xl">
    <!-- Edit Mode: Form + Tabs Interface -->
    <div v-if="isEditMode" class="space-y-6">
      <!-- Edit Form -->
      <div class="bg-white shadow rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Unit Information</h3>

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
        <form @submit.prevent="handleSubmit" class="space-y-6">
          <!-- Title Field -->
          <div
            class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start"
          >
            <label
              for="title"
              class="block text-sm font-medium text-text-primary lg:pt-3"
            >
              Title <span class="text-red-500">*</span>
            </label>
            <div class="lg:col-span-2">
              <input
                id="title"
                v-model="form.title"
                type="text"
                required
                class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.title,
                }"
                placeholder="Enter unit title (e.g., Unit 101, Apartment A)"
              />
              <p v-if="errors.title" class="mt-2 text-sm text-red-600">
                {{ errors.title }}
              </p>
            </div>
          </div>

          <!-- Address Field -->
          <div
            class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start"
          >
            <label
              for="address"
              class="block text-sm font-medium text-text-primary lg:pt-3"
            >
              Address <span class="text-red-500">*</span>
            </label>
            <div class="lg:col-span-2">
              <input
                id="address"
                v-model="form.address"
                type="text"
                required
                class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.address,
                }"
                placeholder="Enter full address"
              />
              <p v-if="errors.address" class="mt-2 text-sm text-red-600">
                {{ errors.address }}
              </p>
            </div>
          </div>

          <!-- City Field -->
          <div
            class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start"
          >
            <label
              for="city"
              class="block text-sm font-medium text-text-primary lg:pt-3"
            >
              City <span class="text-red-500">*</span>
            </label>
            <div class="lg:col-span-2">
              <input
                id="city"
                v-model="form.city"
                type="text"
                required
                class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.city,
                }"
                placeholder="Enter city"
              />
              <p v-if="errors.city" class="mt-2 text-sm text-red-600">
                {{ errors.city }}
              </p>
            </div>
          </div>

          <!-- State and ZIP Code Fields -->
          <div
            class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start"
          >
            <label
              for="state"
              class="block text-sm font-medium text-text-primary lg:pt-3"
            >
              State <span class="text-red-500">*</span>
            </label>
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <select
                  id="state"
                  v-model="form.state"
                  required
                  class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.state,
                  }"
                >
                  <option value="">Select State</option>
                  <option
                    v-for="state in US_STATES"
                    :key="state.value"
                    :value="state.value"
                  >
                    {{ state.label }}
                  </option>
                </select>
                <p v-if="errors.state" class="mt-2 text-sm text-red-600">
                  {{ errors.state }}
                </p>
              </div>
              <div>
                <input
                  id="zip_code"
                  v-model="form.zip_code"
                  type="text"
                  required
                  @input="formatZipCode"
                  class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.zip_code,
                  }"
                  placeholder="12345"
                  maxlength="10"
                />
                <p v-if="errors.zip_code" class="mt-2 text-sm text-red-600">
                  {{ errors.zip_code }}
                </p>
              </div>
            </div>
          </div>

          <!-- Starting Balance Field -->
          <div
            class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start"
          >
            <label
              for="starting_balance"
              class="block text-sm font-medium text-text-primary lg:pt-3"
            >
              Starting Balance <span class="text-red-500">*</span>
            </label>
            <div class="lg:col-span-2">
              <div class="relative">
                <div
                  class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
                >
                  <span class="text-gray-500 sm:text-sm">$</span>
                </div>
                <input
                  id="starting_balance"
                  v-model="form.starting_balance"
                  type="number"
                  step="0.01"
                  min="-999999.99"
                  max="999999.99"
                  required
                  class="w-full pl-6 pr-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                  :class="{
                    'border-red-300 focus:ring-red-500 focus:border-red-500':
                      errors.starting_balance,
                  }"
                  placeholder="0.00"
                />
              </div>
              <p
                v-if="errors.starting_balance"
                class="mt-2 text-sm text-red-600"
              >
                {{ errors.starting_balance }}
              </p>
              <p class="mt-2 text-sm text-gray-600">
                Enter the starting balance for this unit (can be negative)
              </p>
            </div>
          </div>

          <!-- Balance As Of Date Field -->
          <div
            class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start"
          >
            <label
              for="balance_as_of_date"
              class="block text-sm font-medium text-text-primary lg:pt-3"
            >
              Balance As Of Date <span class="text-red-500">*</span>
            </label>
            <div class="lg:col-span-2">
              <input
                id="balance_as_of_date"
                v-model="form.balance_as_of_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.balance_as_of_date,
                }"
              />
              <p
                v-if="errors.balance_as_of_date"
                class="mt-2 text-sm text-red-600"
              >
                {{ errors.balance_as_of_date }}
              </p>
              <p class="mt-2 text-sm text-gray-600">
                The date when the starting balance was recorded
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
                class="flex-1 flex justify-center py-2 px-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                  {{ isSubmitting ? 'Saving...' : 'Update Unit' }}
                </span>
                <span v-else>Update Unit</span>
              </button>

              <!-- Cancel Button -->
              <button
                type="button"
                @click="goBack"
                class="flex-1 flex justify-center py-2 px-3 border border-neutral-300 rounded-lg shadow-sm bg-white text-sm font-medium text-text-primary hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
              >
                Cancel
              </button>
            </div>
          </div>
        </form>
      </div>

      <!-- Tabs Section with Distinct Background -->
      <div class="bg-white rounded-lg p-6">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 mb-6">
          <nav class="-mb-px flex space-x-8">
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
        <div>
          <!-- Owner Tab -->
          <div v-if="activeTab === 'owner'" class="space-y-4">
            <!-- Search Bar and Add Button -->
            <div class="flex items-center justify-between">
              <!-- Search Box -->
              <div class="relative flex-1 max-w-md">
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
                  placeholder="Search..."
                  class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm"
                />
                <button
                  v-if="searchQuery"
                  @click="searchQuery = ''"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center"
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
                      d="M6 18L18 6M6 6l12 12"
                    />
                  </svg>
                </button>
              </div>

              <!-- Add Button -->
              <button
                type="button"
                @click="openAddOwnerModal"
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
                  />
                </svg>
                Add
              </button>
            </div>

            <!-- Owner Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
              <table
                class="min-w-full divide-y divide-gray-200"
                :key="`owners-table-${owners.length}`"
              >
                <thead class="bg-gray-300">
                  <tr>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Name
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Email
                    </th>
                    <th
                      class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="owner in filteredOwners" :key="owner.id">
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                    >
                      {{ owner.name }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      {{ owner.email }}
                    </td>
                    <td
                      class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                    >
                      <button
                        @click="openRemoveOwnerModal(owner)"
                        class="text-red-600 hover:text-red-800"
                      >
                        Remove
                      </button>
                    </td>
                  </tr>
                  <!-- Empty State -->
                  <tr v-if="filteredOwners.length === 0">
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
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                          />
                        </svg>
                        <p class="text-gray-500">
                          No owners assigned to this unit
                        </p>
                        <p class="text-gray-400 text-xs mt-1">
                          Click "Add" to assign owners
                        </p>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Account History Tab -->
          <div v-if="activeTab === 'account-history'" class="space-y-4">
            <!-- Account History Table -->
            <div class="bg-white shadow overflow-hidden sm:rounded-md">
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
                  <tr v-for="history in accountHistory" :key="history.id">
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
                </tbody>
              </table>
            </div>
          </div>

          <!-- Document Tab -->
          <div v-if="activeTab === 'document'" class="space-y-6">
            <!-- Upload Section -->
            <div class="bg-white shadow rounded-lg p-6">
              <DocumentUpload
                :is-uploading="isUploadingDocument"
                :upload-progress="uploadProgress"
                :upload-error="uploadError?.message || null"
                @upload="handleDocumentUpload"
              />
            </div>

            <!-- Documents List -->
            <div class="bg-white shadow rounded-lg p-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Documents</h3>
                <div v-if="documents.length > 0" class="text-sm text-gray-500">
                  {{ documents.length }} document{{
                    documents.length !== 1 ? 's' : ''
                  }}
                </div>
              </div>

              <div
                v-if="isLoadingDocuments"
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
                <span class="ml-2 text-gray-600">Loading documents...</span>
              </div>

              <DocumentList
                v-else
                :documents="documents"
                :is-deleting="isDeletingDocument"
                @download="handleDocumentDownload"
                @delete="handleDocumentDelete"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add Mode: Form Interface -->
    <div v-else>
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
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Title Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="title"
            class="block text-sm font-medium text-text-primary lg:pt-3"
          >
            Title <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="title"
              v-model="form.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.title,
              }"
              placeholder="Enter unit title (e.g., Unit 101, Apartment A)"
            />
            <p v-if="errors.title" class="mt-2 text-sm text-red-600">
              {{ errors.title }}
            </p>
          </div>
        </div>

        <!-- Address Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="address"
            class="block text-sm font-medium text-text-primary lg:pt-3"
          >
            Address <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="address"
              v-model="form.address"
              type="text"
              required
              class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.address,
              }"
              placeholder="Enter full address"
            />
            <p v-if="errors.address" class="mt-2 text-sm text-red-600">
              {{ errors.address }}
            </p>
          </div>
        </div>

        <!-- City Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="city"
            class="block text-sm font-medium text-text-primary lg:pt-3"
          >
            City <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="city"
              v-model="form.city"
              type="text"
              required
              class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.city,
              }"
              placeholder="Enter city"
            />
            <p v-if="errors.city" class="mt-2 text-sm text-red-600">
              {{ errors.city }}
            </p>
          </div>
        </div>

        <!-- State and ZIP Code Fields -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="state"
            class="block text-sm font-medium text-text-primary lg:pt-3"
          >
            State <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <select
                id="state"
                v-model="form.state"
                required
                class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.state,
                }"
              >
                <option value="">Select State</option>
                <option
                  v-for="state in US_STATES"
                  :key="state.value"
                  :value="state.value"
                >
                  {{ state.label }}
                </option>
              </select>
              <p v-if="errors.state" class="mt-2 text-sm text-red-600">
                {{ errors.state }}
              </p>
            </div>
            <div>
              <input
                id="zip_code"
                v-model="form.zip_code"
                type="text"
                required
                @input="formatZipCode"
                class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.zip_code,
                }"
                placeholder="12345"
                maxlength="10"
              />
              <p v-if="errors.zip_code" class="mt-2 text-sm text-red-600">
                {{ errors.zip_code }}
              </p>
            </div>
          </div>
        </div>

        <!-- Starting Balance Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="starting_balance"
            class="block text-sm font-medium text-text-primary lg:pt-3"
          >
            Starting Balance <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <div class="relative">
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <span class="text-gray-500 sm:text-sm">$</span>
              </div>
              <input
                id="starting_balance"
                v-model="form.starting_balance"
                type="number"
                step="0.01"
                min="-999999.99"
                max="999999.99"
                required
                class="w-full pl-6 pr-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
                :class="{
                  'border-red-300 focus:ring-red-500 focus:border-red-500':
                    errors.starting_balance,
                }"
                placeholder="0.00"
              />
            </div>
            <p v-if="errors.starting_balance" class="mt-2 text-sm text-red-600">
              {{ errors.starting_balance }}
            </p>
            <p class="mt-2 text-sm text-gray-600">
              Enter the starting balance for this unit (can be negative)
            </p>
          </div>
        </div>

        <!-- Balance As Of Date Field -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 lg:gap-6 items-start">
          <label
            for="balance_as_of_date"
            class="block text-sm font-medium text-text-primary lg:pt-3"
          >
            Balance As Of Date <span class="text-red-500">*</span>
          </label>
          <div class="lg:col-span-2">
            <input
              id="balance_as_of_date"
              v-model="form.balance_as_of_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200 text-body"
              :class="{
                'border-red-300 focus:ring-red-500 focus:border-red-500':
                  errors.balance_as_of_date,
              }"
            />
            <p
              v-if="errors.balance_as_of_date"
              class="mt-2 text-sm text-red-600"
            >
              {{ errors.balance_as_of_date }}
            </p>
            <p class="mt-2 text-sm text-gray-600">
              The date when the starting balance was recorded
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
              class="flex-1 flex justify-center py-2 px-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200"
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
                      ? 'Update Unit'
                      : 'Add Unit'
                }}
              </span>
              <span v-else>{{ isEditMode ? 'Update Unit' : 'Add Unit' }}</span>
            </button>

            <!-- Cancel Button -->
            <button
              type="button"
              @click="goBack"
              class="flex-1 flex justify-center py-2 px-3 border border-neutral-300 rounded-lg shadow-sm bg-white text-sm font-medium text-text-primary hover:bg-neutral-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-colors duration-200"
            >
              Cancel
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- Add Owner Modal -->
  <div
    v-if="showAddOwnerModal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
  >
    <div
      class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white"
    >
      <!-- Modal Header -->
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Add Owners to Unit</h3>
        <button
          @click="closeAddOwnerModal"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg
            class="h-6 w-6"
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

      <!-- Modal Content -->
      <div class="mb-6">
        <p class="text-sm text-gray-600 mb-4">
          Select active residents to add as owners of this unit. You can select
          multiple people.
        </p>

        <!-- Search Box -->
        <div class="mb-4">
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
              v-model="modalSearchQuery"
              type="text"
              placeholder="Search people..."
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm"
            />
            <button
              v-if="modalSearchQuery"
              @click="modalSearchQuery = ''"
              class="absolute inset-y-0 right-0 pr-3 flex items-center"
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
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>
        </div>

        <!-- People List -->
        <div class="max-h-96 overflow-y-auto border border-gray-200 rounded-md">
          <!-- Loading State -->
          <div v-if="isLoadingResidents" class="p-4 text-center text-gray-500">
            <div class="flex items-center justify-center">
              <svg
                class="animate-spin -ml-1 mr-3 h-5 w-5 text-gray-400"
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
              Loading people...
            </div>
          </div>

          <!-- People List -->
          <div
            v-else
            v-for="person in availablePeople"
            :key="person.id"
            @click="togglePersonSelection(person.id)"
            :class="[
              'flex items-center p-4 border-b border-gray-100 cursor-pointer hover:bg-gray-50',
              selectedPeople.includes(person.id)
                ? 'bg-blue-50 border-blue-200'
                : '',
            ]"
          >
            <div class="flex items-center">
              <input
                type="checkbox"
                :checked="selectedPeople.includes(person.id)"
                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                @click.stop
                @change="togglePersonSelection(person.id)"
              />
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">
                  {{ person.name }}
                </p>
                <p class="text-sm text-gray-500">{{ person.email }}</p>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-if="!isLoadingResidents && availablePeople.length === 0"
            class="p-4 text-center text-gray-500"
          >
            <div v-if="modalSearchQuery">
              No people found matching "{{ modalSearchQuery }}".
            </div>
            <div v-else>No available people to add as owners.</div>
          </div>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end space-x-3">
        <button
          @click="closeAddOwnerModal"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
        >
          Cancel
        </button>
        <button
          @click="addSelectedOwners"
          :disabled="selectedPeople.length === 0 || isAddingOwners"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="isAddingOwners" class="flex items-center">
            <svg
              class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
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
          <span v-else>
            Add {{ selectedPeople.length }} Owner{{
              selectedPeople.length !== 1 ? 's' : ''
            }}
          </span>
        </button>
      </div>
    </div>
  </div>

  <!-- Remove Owner Confirmation Modal -->
  <div
    v-if="showRemoveOwnerModal"
    class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
  >
    <div
      class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/3 shadow-lg rounded-md bg-white"
    >
      <!-- Modal Header -->
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">Remove Owner</h3>
        <button
          @click="closeRemoveOwnerModal"
          class="text-gray-400 hover:text-gray-600"
        >
          <svg
            class="h-6 w-6"
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

      <!-- Modal Content -->
      <div class="mb-6">
        <div class="flex items-center mb-4">
          <div class="flex-shrink-0">
            <svg
              class="h-8 w-8 text-red-400"
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
          <div class="ml-3">
            <h4 class="text-sm font-medium text-gray-900">
              Are you sure you want to remove this owner?
            </h4>
          </div>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
          <p class="text-sm text-gray-600 mb-2">
            <strong>Owner:</strong> {{ ownerToRemove?.name }}
          </p>
          <p class="text-sm text-gray-600 mb-2">
            <strong>Email:</strong> {{ ownerToRemove?.email }}
          </p>
          <p class="text-sm text-gray-500">
            This will remove the ownership relationship between this person and
            the unit. The person will no longer be associated with this unit.
          </p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="flex items-center justify-end space-x-3">
        <button
          @click="closeRemoveOwnerModal"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary"
        >
          Cancel
        </button>
        <button
          @click="confirmRemoveOwner"
          class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          Remove Owner
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, nextTick } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCreateUnit, useUpdateUnit, useUnit } from '../composables/useUnits';
import { useResidents } from '../composables/useResidents';
import { useUnitDocuments } from '../composables/useUnitDocuments';
import { unitsApi, unitKeys } from '@neibrpay/api-client';
import { useQueryClient } from '@tanstack/vue-query';
import { US_STATES } from '@neibrpay/models';
import type {
  UnitFormData,
  UnitFormErrors,
  UnitDocument,
} from '@neibrpay/models';
import DocumentUpload from '../components/DocumentUpload.vue';
import DocumentList from '../components/DocumentList.vue';

// Router
const route = useRoute();
const router = useRouter();

// Computed
const isEditMode = computed(() => route.name === 'EditUnit');
const unitId = computed(() => {
  const id = route.params.id;
  return id ? parseInt(id as string) : null;
});

// State
const isSubmitting = ref(false);
const errors = ref<UnitFormErrors>({});
const activeTab = ref('owner');
const searchQuery = ref('');
const showAddOwnerModal = ref(false);
const selectedPeople = ref<number[]>([]);
const modalSearchQuery = ref('');
const isAddingOwners = ref(false);
const ownersUpdateTrigger = ref(0);
const showRemoveOwnerModal = ref(false);
const ownerToRemove = ref<any>(null);

// Document-related state
const uploadProgress = ref(0);

// Tabs configuration
const tabs = [
  { id: 'owner', name: 'Owner' },
  { id: 'account-history', name: 'Account History' },
  { id: 'document', name: 'Document' },
];

// Owners list - will be populated from API or form data
const owners = ref<any[]>([]);

// Get people from API - residents data will be used as allPeople
const allPeople = computed(() => {
  return residents.value || [];
});

const accountHistory = ref([
  {
    id: 1,
    date: '2024-01-15',
    description: 'Monthly HOA Fee',
    remark: 'Paid on time',
  },
  {
    id: 2,
    date: '2024-01-10',
    description: 'Late Fee',
    remark: 'Payment received after due date',
  },
  {
    id: 3,
    date: '2023-12-15',
    description: 'Monthly HOA Fee',
    remark: 'Paid on time',
  },
]);

// Computed
const filteredOwners = computed(() => {
  // Include ownersUpdateTrigger to force reactivity
  ownersUpdateTrigger.value;

  if (!searchQuery.value) return owners.value;
  return owners.value.filter(
    owner =>
      owner.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      owner.email.toLowerCase().includes(searchQuery.value.toLowerCase())
  );
});

const availablePeople = computed(() => {
  // Filter out people who are already owners
  const ownerIds = owners.value.map((owner: any) => owner.id);
  let filtered = allPeople.value.filter(
    (person: any) => !ownerIds.includes(person.id)
  );

  // Apply search filter
  if (modalSearchQuery.value) {
    filtered = filtered.filter(
      (person: any) =>
        person.name
          .toLowerCase()
          .includes(modalSearchQuery.value.toLowerCase()) ||
        person.email
          .toLowerCase()
          .includes(modalSearchQuery.value.toLowerCase())
    );
  }

  return filtered;
});

const form = ref<UnitFormData>({
  title: '',
  address: '',
  city: '',
  state: '',
  zip_code: '',
  starting_balance: 0,
  balance_as_of_date: new Date().toISOString().split('T')[0], // Today's date
});

// Queries and mutations
const queryClient = useQueryClient();
const createUnitMutation = useCreateUnit();
const updateUnitMutation = useUpdateUnit();
const { data: existingUnit, refetch: refetchUnit } = useUnit(unitId.value || 0);
const { data: residents, isLoading: isLoadingResidents } = useResidents(false); // Get only active residents

// Document composable
const {
  documents,
  isLoading: isLoadingDocuments,
  uploadDocument,
  isUploading: isUploadingDocument,
  uploadError,
  deleteDocument,
  isDeleting: isDeletingDocument,
  downloadDocument,
} = useUnitDocuments(unitId.value || 0);

// Methods
const goBack = () => {
  router.push('/units');
};

const openAddOwnerModal = () => {
  showAddOwnerModal.value = true;
  selectedPeople.value = [];
  modalSearchQuery.value = '';
};

const closeAddOwnerModal = () => {
  showAddOwnerModal.value = false;
  selectedPeople.value = [];
  modalSearchQuery.value = '';
  isAddingOwners.value = false;
};

const openRemoveOwnerModal = (owner: {
  id: number;
  name: string;
  email: string;
}) => {
  ownerToRemove.value = owner;
  showRemoveOwnerModal.value = true;
};

const closeRemoveOwnerModal = () => {
  showRemoveOwnerModal.value = false;
  ownerToRemove.value = null;
};

const togglePersonSelection = (personId: number) => {
  const index = selectedPeople.value.indexOf(personId);
  if (index > -1) {
    selectedPeople.value.splice(index, 1);
  } else {
    selectedPeople.value.push(personId);
  }
};

const addSelectedOwners = async () => {
  if (isAddingOwners.value) return; // Prevent multiple calls

  isAddingOwners.value = true;

  try {
    const newOwners = allPeople.value.filter((person: any) =>
      selectedPeople.value.includes(person.id)
    );

    // Add new owners to the owners list
    // Use array replacement to ensure reactivity
    owners.value = [...owners.value, ...newOwners];

    // Trigger reactivity update
    ownersUpdateTrigger.value++;

    // Force DOM update
    await nextTick();

    // If we're in edit mode and have a unit ID, save to database immediately
    if (isEditMode.value && unitId.value) {
      try {
        const ownerIds = newOwners.map((owner: any) => owner.id);
        await unitsApi.addOwners(unitId.value, ownerIds);

        // Invalidate unit query to refetch latest data
        await queryClient.invalidateQueries({
          queryKey: unitKeys.detail(unitId.value),
        });
      } catch (error) {
        // Remove the owners from local state if API call failed
        const newOwnerIds = newOwners.map((owner: any) => owner.id);
        owners.value = owners.value.filter(
          (owner: any) => !newOwnerIds.includes(owner.id)
        );
        ownersUpdateTrigger.value++;
        // Show error message
        errors.value.general = 'Failed to add owners. Please try again.';
      }
    }

    // Close modal and reset selection
    closeAddOwnerModal();
  } finally {
    isAddingOwners.value = false;
  }
};

const confirmRemoveOwner = async () => {
  if (!ownerToRemove.value) return;

  const ownerId = ownerToRemove.value.id;
  const removedOwner = owners.value.find((owner: any) => owner.id === ownerId);

  if (removedOwner) {
    // Use array replacement to ensure reactivity
    owners.value = owners.value.filter((owner: any) => owner.id !== ownerId);
    ownersUpdateTrigger.value++;

    // If we're in edit mode and have a unit ID, save to database immediately
    if (isEditMode.value && unitId.value) {
      try {
        await unitsApi.removeOwners(unitId.value, [ownerId]);

        // Invalidate unit query to refetch latest data
        await queryClient.invalidateQueries({
          queryKey: unitKeys.detail(unitId.value),
        });
      } catch (error) {
        // Add the owner back to local state if API call failed
        owners.value = [...owners.value, removedOwner];
        ownersUpdateTrigger.value++;
        // Show error message
        errors.value.general = 'Failed to remove owner. Please try again.';
      }
    }
  }

  // Close the confirmation modal
  closeRemoveOwnerModal();
};

const formatZipCode = (event: Event) => {
  const target = event.target as HTMLInputElement;
  let value = target.value.replace(/\D/g, '');

  if (value.length > 5) {
    value = value.slice(0, 5) + '-' + value.slice(5, 9);
  }

  form.value.zip_code = value;
};

const handleSubmit = async () => {
  isSubmitting.value = true;
  errors.value = {};

  try {
    let currentUnitId: number;

    if (isEditMode.value && unitId.value) {
      // Update existing unit
      const updatedUnit = await updateUnitMutation.mutateAsync({
        id: unitId.value,
        data: form.value,
      });
      currentUnitId = updatedUnit.id;
    } else {
      // Create new unit
      const newUnit = await createUnitMutation.mutateAsync(form.value);
      currentUnitId = newUnit.id;
    }

    // Save owner relationships
    if (owners.value.length > 0) {
      const ownerIds = owners.value.map(owner => owner.id);
      await unitsApi.syncOwners(currentUnitId, ownerIds);
    }

    router.push('/units');
  } catch (error: any) {
    console.error('Form submission error:', error);

    if (error.errors) {
      errors.value = error.errors;
    } else {
      errors.value.general = error.message || 'An unexpected error occurred';
    }
  } finally {
    isSubmitting.value = false;
  }
};

// Document methods
const handleDocumentUpload = async (files: File[]) => {
  if (!unitId.value) return;

  for (const file of files) {
    try {
      await uploadDocument({ file });
    } catch (error) {
      console.error('Upload failed for:', file.name, error);
    }
  }
};

const handleDocumentDownload = async (document: UnitDocument) => {
  if (!unitId.value) return;

  try {
    await downloadDocument(document.id, document.file_name);
  } catch (error) {
    console.error('Download failed:', error);
  }
};

const handleDocumentDelete = async (document: UnitDocument) => {
  if (!unitId.value) return;

  try {
    await deleteDocument(document.id);
  } catch (error) {
    console.error('Delete failed:', error);
  }
};

// Watch for unitId changes to refetch data
watch(
  unitId,
  async newUnitId => {
    if (isEditMode.value && newUnitId) {
      await refetchUnit();
    }
  },
  { immediate: true }
);

// Watch for existing unit data changes
watch(
  existingUnit,
  (newUnit: any) => {
    if (isEditMode.value && newUnit) {
      form.value = {
        title: newUnit.title,
        address: newUnit.address,
        city: newUnit.city,
        state: newUnit.state,
        zip_code: newUnit.zip_code,
        starting_balance: newUnit.starting_balance,
        balance_as_of_date: newUnit.balance_as_of_date.split('T')[0],
      };

      // Load existing owners
      if (newUnit.owners) {
        owners.value = newUnit.owners;
        ownersUpdateTrigger.value++;
      }
    }
  },
  { immediate: true }
);

// Load existing unit data for editing
onMounted(async () => {
  if (isEditMode.value && unitId.value) {
    // Force refetch to get latest data from database
    await refetchUnit();

    if (existingUnit.value) {
      form.value = {
        title: existingUnit.value.title,
        address: existingUnit.value.address,
        city: existingUnit.value.city,
        state: existingUnit.value.state,
        zip_code: existingUnit.value.zip_code,
        starting_balance: existingUnit.value.starting_balance,
        balance_as_of_date: existingUnit.value.balance_as_of_date.split('T')[0],
      };

      // Load existing owners
      if (existingUnit.value.owners) {
        owners.value = existingUnit.value.owners;
      }
    }
  }
});
</script>
