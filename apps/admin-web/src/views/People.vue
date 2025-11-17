<template>
  <div class="space-y-6">
    <!-- Success Message -->
    <div
      v-if="showSuccessMessage"
      class="p-4 bg-green-50 border border-green-200 rounded-lg"
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

    <!-- Error Message -->
    <div
      v-if="showErrorMessage"
      class="p-4 bg-red-50 border border-red-200 rounded-lg"
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

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <!-- Active Residents Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-green-500': activeFilter === 'active',
        }"
        @click="filterByStatus('active')"
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
            <h3 class="text-sm font-medium text-gray-600">Active Residents</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ activeResidentsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- All Residents Card -->
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
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">All Residents</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ allResidentsCount }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Residents Directory Section -->
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
                placeholder="Search residents..."
                class="input-field pl-10 w-full"
                @input="debouncedSearch"
              />
            </div>
          </div>

          <!-- Header Controls (Right) -->
          <div class="flex items-center space-x-3">
            <!-- Show Deleted Checkbox -->
            <div class="flex items-center">
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

            <!-- New Resident Button (Desktop) -->
            <router-link to="/people/add" class="hidden md:inline-flex">
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
            <span class="text-sm text-gray-600">Loading residents...</span>
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
              Error loading residents
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
          v-else-if="!filteredResidents || filteredResidents.length === 0"
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
                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              No residents found
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                searchQuery
                  ? 'Try adjusting your search query.'
                  : 'Get started by adding your first resident.'
              }}
            </p>
            <div v-if="!searchQuery" class="mt-4">
              <router-link to="/people/add">
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
                  Add Resident
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
                @click="sortBy('name')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>NAME</span>
                  <svg
                    v-if="sortColumn === 'name'"
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
                @click="sortBy('email')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>EMAIL</span>
                  <svg
                    v-if="sortColumn === 'email'"
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
                @click="sortBy('phone')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>PHONE</span>
                  <svg
                    v-if="sortColumn === 'phone'"
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
                @click="sortBy('role')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>ROLE</span>
                  <svg
                    v-if="sortColumn === 'role'"
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
              v-for="resident in filteredResidents"
              :key="resident.id"
              class="table-row-hover"
            >
              <!-- Name Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div
                        :class="[
                          'h-10 w-10 rounded-full flex items-center justify-center',
                          resident.deleted_at ? 'bg-red-100' : 'bg-gray-100',
                        ]"
                      >
                        <span
                          :class="[
                            'text-sm font-medium',
                            resident.deleted_at
                              ? 'text-red-600'
                              : 'text-gray-700',
                          ]"
                        >
                          {{ resident.name.charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="flex items-center space-x-2">
                        <div
                          :class="[
                            'text-sm font-medium',
                            resident.deleted_at
                              ? 'text-red-600 line-through'
                              : 'text-gray-900',
                          ]"
                        >
                          {{ resident.name }}
                        </div>
                        <span
                          v-if="resident.deleted_at"
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
                            resident.deleted_at
                              ? 'text-red-400'
                              : 'text-gray-500',
                          ]"
                        >
                          {{ resident.email }} • {{ resident.phone }}
                        </div>
                        <div class="mt-1">
                          <span
                            v-if="resident.deleted_at"
                            class="badge badge-overdue text-xs"
                          >
                            Deleted
                          </span>
                          <span
                            v-else
                            :class="getStatusBadgeClass(resident)"
                            class="badge text-xs"
                          >
                            {{ getStatusText(resident) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Email Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <div
                  :class="[
                    'text-sm',
                    resident.deleted_at
                      ? 'text-red-600 line-through'
                      : 'text-gray-900',
                  ]"
                >
                  {{ resident.email }}
                </div>
              </td>

              <!-- Phone Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                <div
                  :class="[
                    'text-sm',
                    resident.deleted_at
                      ? 'text-red-600 line-through'
                      : 'text-gray-900',
                  ]"
                >
                  {{ resident.phone }}
                </div>
              </td>

              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <span v-if="resident.deleted_at" class="badge badge-overdue">
                  Deleted
                </span>
                <span
                  v-else
                  :class="getStatusBadgeClass(resident)"
                  class="badge"
                >
                  {{ getStatusText(resident) }}
                </span>
              </td>

              <!-- Role Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <span
                  v-if="resident.role"
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    resident.role === 'admin'
                      ? 'bg-purple-100 text-purple-800'
                      : resident.role === 'bookkeeper'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-blue-100 text-blue-800',
                  ]"
                >
                  {{ formatRole(resident.role) }}
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
                      <!-- Actions for deleted residents -->
                      <template v-if="resident.deleted_at">
                        <button
                          @click="
                            () => {
                              restoreResident(resident.id);
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
                              d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"
                            />
                          </svg>
                          Restore
                        </button>
                      </template>

                      <!-- Actions for active residents -->
                      <template v-else>
                        <button
                          @click="
                            () => {
                              editResident(resident.id);
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
                          v-if="isResidentActive(resident)"
                          @click="
                            () => {
                              sendInviteEmail(resident.id);
                              close();
                            }
                          "
                          :disabled="sendingInviteId === resident.id"
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
                              d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
                            />
                          </svg>
                          {{
                            sendingInviteId === resident.id
                              ? 'Sending...'
                              : 'Send Invite'
                          }}
                        </button>
                        <div class="border-t border-gray-200 my-1"></div>
                        <button
                          @click="
                            () => {
                              deleteResident(resident);
                              close();
                            }
                          "
                          :disabled="deletingResidentId === resident.id"
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
                            deletingResidentId === resident.id
                              ? 'Deleting...'
                              : 'Delete'
                          }}
                        </button>
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
      title="Delete Resident"
      :message="`Are you sure you want to delete ${residentToDelete?.name || 'this resident'}?`"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletingResidentId === residentToDelete?.id"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Mobile Fixed Bottom Button -->
    <div
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/people/add" class="block">
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
          Add Resident
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import { residentsApi, queryKeys } from '@neibrpay/api-client';
import type { Resident } from '@neibrpay/models';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import DropdownMenu from '../components/DropdownMenu.vue';

const router = useRouter();
const queryClient = useQueryClient();

// Local state
const searchQuery = ref('');
const includeDeleted = ref(false);
const deletingResidentId = ref<number | null>(null);
const sendingInviteId = ref<number | null>(null);
const showDeleteModal = ref(false);
const residentToDelete = ref<Resident | null>(null);
const activeFilter = ref<'active' | 'all' | null>('active'); // Default to 'active' filter

// Success/Error messages
const successMessage = ref('');
const errorMessage = ref('');
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const sortColumn = ref<'name' | 'email' | 'phone' | 'status' | 'role' | null>(
  null
);
const sortDirection = ref<'asc' | 'desc'>('asc');

// Debounced search
let searchTimeout: NodeJS.Timeout;
const debouncedSearch = () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    applyFilters();
  }, 300);
};

// Query for residents
const {
  data: residentsData,
  isLoading,
  error,
  refetch: refetchResidents,
} = useQuery({
  queryKey: queryKeys.residents.list({ include_deleted: includeDeleted.value }),
  queryFn: () => residentsApi.getResidents(includeDeleted.value),
  select: (data: any) => data,
});

const residents = computed(() => residentsData.value || []);

// Delete mutation
const { mutateAsync: deleteResidentMutation } = useMutation({
  mutationFn: (id: number) => residentsApi.deleteResident(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.residents.all });
  },
});

// Restore mutation
const { mutateAsync: restoreResidentMutation } = useMutation({
  mutationFn: (id: number) => residentsApi.restoreResident(id),
  onSuccess: () => {
    queryClient.invalidateQueries({ queryKey: queryKeys.residents.all });
  },
});

// Send invite email mutation
const { mutateAsync: sendInviteEmailMutation } = useMutation({
  mutationFn: (id: number) => residentsApi.sendInviteEmail(id),
});

// Helper function to check if resident is active
const isResidentActive = (resident: Resident): boolean => {
  return resident.is_active && !resident.deleted_at;
};

// Computed properties for summary cards
const activeResidentsCount = computed(() => {
  if (!residents.value) return 0;
  return residents.value.filter((resident: Resident) =>
    isResidentActive(resident)
  ).length;
});

const allResidentsCount = computed(() => {
  if (!residents.value) return 0;
  return residents.value.length;
});

// Computed properties - filter residents based on activeFilter and search
const filteredResidents = computed(() => {
  if (!residents.value) return [];

  let filtered = residents.value.filter((resident: Resident) => {
    // Apply active filter from summary cards
    if (activeFilter.value === 'active' && !isResidentActive(resident)) {
      return false;
    }

    // 'all' filter shows all residents (no additional filtering needed)
    // If activeFilter is 'all', we don't filter by status

    // Apply search filter
    if (searchQuery.value && searchQuery.value.trim()) {
      const query = searchQuery.value.toLowerCase().trim();
      const name = (resident.name || '').toLowerCase();
      const email = (resident.email || '').toLowerCase();
      const phone = (resident.phone || '').toLowerCase();

      return (
        name.includes(query) || email.includes(query) || phone.includes(query)
      );
    }

    return true;
  });

  // Apply sorting
  if (sortColumn.value) {
    filtered = [...filtered].sort((a: Resident, b: Resident) => {
      let aValue: any;
      let bValue: any;

      switch (sortColumn.value) {
        case 'name':
          aValue = a.name || '';
          bValue = b.name || '';
          break;
        case 'email':
          aValue = a.email || '';
          bValue = b.email || '';
          break;
        case 'phone':
          aValue = a.phone || '';
          bValue = b.phone || '';
          break;
        case 'status':
          aValue = a.deleted_at
            ? 'deleted'
            : a.is_active
              ? 'active'
              : 'inactive';
          bValue = b.deleted_at
            ? 'deleted'
            : b.is_active
              ? 'active'
              : 'inactive';
          break;
        case 'role':
          aValue = a.role || 'resident';
          bValue = b.role || 'resident';
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
  refetchResidents();
};

const filterByStatus = (status: 'active' | 'all') => {
  // Toggle filter: if already active, clear it; otherwise, set it
  if (activeFilter.value === status) {
    activeFilter.value = null;
  } else {
    activeFilter.value = status;
  }
};

const sortBy = (column: 'name' | 'email' | 'phone' | 'status' | 'role') => {
  if (sortColumn.value === column) {
    // Toggle direction if clicking the same column
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column and default to ascending
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
};

const getStatusText = (resident: Resident) => {
  if (resident.deleted_at) return 'Deleted';
  return resident.is_active ? 'Active' : 'Inactive';
};

const getStatusBadgeClass = (resident: Resident) => {
  if (resident.deleted_at) return 'badge-overdue';
  return resident.is_active ? 'badge-paid' : 'badge-draft';
};

const formatRole = (role: string): string => {
  const roleMap: Record<string, string> = {
    admin: 'Admin',
    resident: 'Resident',
    bookkeeper: 'Bookkeeper',
  };
  return roleMap[role] || role;
};

const editResident = (residentId: number) => {
  router.push(`/people/edit/${residentId}`);
};

const restoreResident = async (residentId: number) => {
  try {
    await restoreResidentMutation(residentId);
    console.log('Resident restored successfully');
    showSuccess('Resident restored successfully');
    refetch();
  } catch (error: any) {
    console.error('Error restoring resident:', error);
    showError(
      `Failed to restore resident: ${error.message || 'Unknown error'}`
    );
  }
};

const deleteResident = (resident: Resident) => {
  // Store the resident to delete and show the modal
  residentToDelete.value = resident;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  if (!residentToDelete.value) return;

  const residentId = residentToDelete.value.id;
  deletingResidentId.value = residentId;

  try {
    console.log('Starting delete for resident:', residentId);
    const result = await deleteResidentMutation(residentId);
    console.log('Delete result:', result);
    // Show success message
    console.log('Resident deleted successfully');
    // Close modal
    showDeleteModal.value = false;
    residentToDelete.value = null;
    // Refetch data to update the list
    refetch();
  } catch (error: any) {
    console.error('Error deleting resident:', error);

    // Check if it's an authentication error
    if (error.message && error.message.includes('Resident not found')) {
      // This might be an authentication issue
      showError('Authentication error. Please refresh the page and try again.');
    } else {
      // Show error message to user
      showError(
        `Failed to delete resident: ${error.message || 'Unknown error'}`
      );
    }
  } finally {
    // Always reset the loading state
    deletingResidentId.value = null;
  }
};

const cancelDelete = () => {
  showDeleteModal.value = false;
  residentToDelete.value = null;
};

const sendInviteEmail = async (residentId: number) => {
  sendingInviteId.value = residentId;
  try {
    await sendInviteEmailMutation(residentId);
    showSuccess('Invite email sent successfully!');
  } catch (error: any) {
    console.error('Error sending invite email:', error);
    showError(
      `Failed to send invite email: ${error.message || 'Unknown error'}`
    );
  } finally {
    sendingInviteId.value = null;
  }
};

// Apply filters
const applyFilters = () => {
  // The query will automatically refetch when filters change
};

// Watch for changes in include_deleted to refetch data and update filter
watch(
  () => includeDeleted.value,
  (newValue: boolean) => {
    refetchResidents();

    // When showing deleted residents, automatically set filter to 'all'
    if (newValue) {
      activeFilter.value = 'all';
    }
  }
);

// Reset mutation states on component mount to clear any stuck states
onMounted(() => {
  // Reset any stuck states
});

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

// Global reset function for debugging (can be called from browser console)
(window as any).resetResidentMutations = () => {
  deletingResidentId.value = null;
  console.log('Resident mutations and local state reset');
};
</script>
