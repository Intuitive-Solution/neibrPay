<template>
  <div class="space-y-6">
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Active Announcements Card -->
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
            <h3 class="text-sm font-medium text-gray-600">Active</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ activeAnnouncementsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- Expired Announcements Card -->
      <div
        class="card card-hover cursor-pointer transition-all duration-200"
        :class="{
          'ring-2 ring-red-500': activeFilter === 'expired',
        }"
        @click="filterByStatus('expired')"
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
                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Expired</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ expiredAnnouncementsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- All Announcements Card -->
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
                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">All</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ allAnnouncementsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- Placeholder Card for consistency -->
      <div class="card opacity-0 pointer-events-none">
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
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"
              />
            </svg>
          </div>
          <div class="ml-4 flex-1">
            <h3 class="text-sm font-medium text-gray-600">Placeholder</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">0</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Announcements Directory Section -->
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
                placeholder="Search announcements..."
                class="input-field pl-10 w-full"
              />
            </div>
          </div>

          <!-- Header Controls (Right) -->
          <div class="flex items-center space-x-3">
            <!-- Status Filter -->
            <div class="flex items-center">
              <select
                v-model="statusFilter"
                class="input-field text-sm"
                @change="applyFilters"
              >
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="expired">Expired</option>
              </select>
            </div>

            <!-- Show Deleted Checkbox -->
            <div v-if="authStore.isAdmin" class="flex items-center">
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

            <!-- New Announcement Button (Desktop) -->
            <router-link
              v-if="authStore.isAdmin"
              to="/announcements/create"
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
            <span class="text-sm text-gray-600">Loading announcements...</span>
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
              Error loading announcements
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
          v-else-if="
            !filteredAnnouncements || filteredAnnouncements.length === 0
          "
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
                d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
              />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">
              No announcements found
            </h3>
            <p class="mt-1 text-sm text-gray-500">
              {{
                searchQuery
                  ? 'Try adjusting your search query.'
                  : 'Get started by creating your first announcement.'
              }}
            </p>
            <div v-if="!searchQuery && authStore.isAdmin" class="mt-4">
              <router-link to="/announcements/create">
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
                  Create Announcement
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
                @click="sortBy('subject')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>SUBJECT</span>
                  <svg
                    v-if="sortColumn === 'subject'"
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
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden sm:table-cell"
              >
                RECIPIENTS
              </th>
              <th
                @click="sortBy('created')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden lg:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>CREATED</span>
                  <svg
                    v-if="sortColumn === 'created'"
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
                @click="sortBy('removal')"
                class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider hidden xl:table-cell cursor-pointer hover:bg-gray-200 transition-colors"
              >
                <div class="flex items-center space-x-1">
                  <span>REMOVAL DATE</span>
                  <svg
                    v-if="sortColumn === 'removal'"
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
              v-for="announcement in filteredAnnouncements"
              :key="announcement.id"
              class="table-row-hover"
            >
              <!-- Subject Column -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center justify-between">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div
                        :class="[
                          'h-10 w-10 rounded-full flex items-center justify-center',
                          announcement.deleted_at
                            ? 'bg-red-100'
                            : 'bg-primary-100',
                        ]"
                      >
                        <svg
                          :class="[
                            'h-5 w-5',
                            announcement.deleted_at
                              ? 'text-red-600'
                              : 'text-primary',
                          ]"
                          fill="none"
                          stroke="currentColor"
                          viewBox="0 0 24 24"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3.14a7.5 7.5 0 011.291 7.239M19 13a3 3 0 11-6 0 3 3 0 016 0z"
                          />
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="flex items-center space-x-2">
                        <div
                          :class="[
                            'text-sm font-medium',
                            announcement.deleted_at
                              ? 'text-red-600 line-through'
                              : 'text-gray-900',
                          ]"
                        >
                          {{ announcement.subject }}
                        </div>
                        <span
                          v-if="announcement.deleted_at"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800"
                        >
                          Deleted
                        </span>
                      </div>
                      <div
                        :class="[
                          'text-sm',
                          announcement.deleted_at
                            ? 'text-red-400'
                            : 'text-gray-500',
                        ]"
                      >
                        {{ formatDate(announcement.created_at) }}
                      </div>
                      <!-- Mobile-only additional info -->
                      <div class="sm:hidden mt-1">
                        <div
                          :class="[
                            'text-xs',
                            announcement.deleted_at
                              ? 'text-red-400'
                              : 'text-gray-500',
                          ]"
                        >
                          {{
                            formatRecipientSummary(
                              announcement.recipients || []
                            )
                          }}
                        </div>
                        <div class="mt-1">
                          <span
                            v-if="announcement.deleted_at"
                            class="badge badge-overdue text-xs"
                          >
                            Deleted
                          </span>
                          <span
                            v-else
                            :class="
                              isExpired(announcement)
                                ? 'badge badge-overdue'
                                : 'badge badge-paid'
                            "
                            class="badge text-xs"
                          >
                            {{ isExpired(announcement) ? 'Expired' : 'Active' }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </td>

              <!-- Recipients Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <div
                  :class="[
                    'text-sm',
                    announcement.deleted_at
                      ? 'text-red-600 line-through'
                      : 'text-gray-900',
                  ]"
                >
                  {{ formatRecipientSummary(announcement.recipients || []) }}
                </div>
              </td>

              <!-- Created Date Column -->
              <td
                :class="[
                  'px-6 py-4 whitespace-nowrap text-sm hidden lg:table-cell',
                  announcement.deleted_at ? 'text-red-400' : 'text-gray-900',
                ]"
              >
                {{ formatDate(announcement.created_at) }}
              </td>

              <!-- Removal Date Column -->
              <td
                :class="[
                  'px-6 py-4 whitespace-nowrap text-sm hidden xl:table-cell',
                  announcement.deleted_at ? 'text-red-400' : 'text-gray-500',
                ]"
              >
                {{
                  announcement.removal_date
                    ? formatDate(announcement.removal_date)
                    : '—'
                }}
              </td>

              <!-- Status Column -->
              <td class="px-6 py-4 whitespace-nowrap hidden sm:table-cell">
                <span
                  v-if="announcement.deleted_at"
                  class="badge badge-overdue"
                >
                  Deleted
                </span>
                <span
                  v-else
                  :class="
                    isExpired(announcement)
                      ? 'badge badge-overdue'
                      : 'badge badge-paid'
                  "
                  class="badge"
                >
                  {{ isExpired(announcement) ? 'Expired' : 'Active' }}
                </span>
              </td>

              <!-- Actions Column -->
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end relative">
                  <!-- Enhanced Kebab Menu -->
                  <DropdownMenu
                    trigger-class="p-2 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 hover:border-gray-300 shadow-sm hover:shadow-md transition-all duration-200"
                  >
                    <template #default="{ close }">
                      <!-- Actions for deleted announcements -->
                      <template v-if="announcement.deleted_at">
                        <button
                          @click="
                            () => {
                              viewAnnouncement(announcement.id);
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
                      </template>

                      <!-- Actions for active announcements -->
                      <template v-else>
                        <button
                          @click="
                            () => {
                              viewAnnouncement(announcement.id);
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
                        <!-- Edit, Delete - Admin only -->
                        <template v-if="authStore.isAdmin">
                          <button
                            v-if="
                              announcement.created_by === authStore.user?.id
                            "
                            @click="
                              () => {
                                editAnnouncement(announcement.id);
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
                                deleteAnnouncement(announcement);
                                close();
                              }
                            "
                            :disabled="
                              deletingAnnouncementId === announcement.id
                            "
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
                              deletingAnnouncementId === announcement.id
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
      title="Delete Announcement"
      :message="deleteMessage"
      confirm-text="Delete"
      cancel-text="Cancel"
      type="danger"
      :is-loading="deletingAnnouncementId === announcementToDelete?.id"
      @confirm="confirmDelete"
      @cancel="cancelDelete"
    />

    <!-- Mobile Fixed Bottom Button -->
    <div
      v-if="authStore.isAdmin"
      class="md:hidden fixed bottom-0 left-0 right-0 p-4 bg-white border-t border-gray-200 safe-area-inset-bottom"
    >
      <router-link to="/announcements/create" class="block">
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
          New Announcement
        </button>
      </router-link>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import {
  useAnnouncements,
  useDeleteAnnouncement,
} from '../composables/useAnnouncements';
import { formatRecipientSummary } from '@neibrpay/models';
import ConfirmDialog from '../components/ConfirmDialog.vue';
import DropdownMenu from '../components/DropdownMenu.vue';
import type { Announcement } from '@neibrpay/models';

const router = useRouter();
const authStore = useAuthStore();

// Check admin access
if (!authStore.isAdmin) {
  router.push('/');
}

// Local state
const searchQuery = ref('');
const deletingAnnouncementId = ref<number | null>(null);
const showDeleteModal = ref(false);
const announcementToDelete = ref<Announcement | null>(null);
const activeFilter = ref<'active' | 'expired' | 'all' | null>('active');
const sortColumn = ref<'subject' | 'created' | 'removal' | 'status' | null>(
  null
);
const sortDirection = ref<'asc' | 'desc'>('asc');
const includeDeleted = ref(false);
const statusFilter = ref<'active' | 'expired' | 'all'>('active');

// Filters
const filters = computed(() => ({
  status: statusFilter.value === 'all' ? undefined : statusFilter.value,
  include_deleted: includeDeleted.value,
}));

const {
  data: announcements,
  isLoading,
  error,
  refetch,
} = useAnnouncements(filters);

// Computed properties for summary cards
const activeAnnouncementsCount = computed(() => {
  if (!announcements.value) return 0;
  return (
    announcements.value.data?.filter(
      (a: Announcement) => !isExpired(a) && !a.deleted_at
    ).length || 0
  );
});

const expiredAnnouncementsCount = computed(() => {
  if (!announcements.value) return 0;
  return (
    announcements.value.data?.filter(
      (a: Announcement) => isExpired(a) && !a.deleted_at
    ).length || 0
  );
});

const allAnnouncementsCount = computed(() => {
  if (!announcements.value) return 0;
  return (
    announcements.value.data?.filter((a: Announcement) => !a.deleted_at)
      .length || 0
  );
});

// Computed properties - filter announcements
const filteredAnnouncements = computed(() => {
  if (!announcements.value || !announcements.value.data) return [];

  let filtered = announcements.value.data.filter(
    (announcement: Announcement) => {
      // Apply active filter from summary cards
      if (
        activeFilter.value === 'active' &&
        (isExpired(announcement) || announcement.deleted_at)
      ) {
        return false;
      }

      if (
        activeFilter.value === 'expired' &&
        (!isExpired(announcement) || announcement.deleted_at)
      ) {
        return false;
      }

      // Apply search filter
      if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase().trim();
        const subject = (announcement.subject || '').toLowerCase();
        const recipients = formatRecipientSummary(
          announcement.recipients || []
        ).toLowerCase();

        return subject.includes(query) || recipients.includes(query);
      }

      return true;
    }
  );

  // Apply sorting
  if (sortColumn.value) {
    filtered = [...filtered].sort((a: Announcement, b: Announcement) => {
      let aValue: any;
      let bValue: any;

      switch (sortColumn.value) {
        case 'subject':
          aValue = a.subject || '';
          bValue = b.subject || '';
          break;
        case 'created':
          aValue = a.created_at ? new Date(a.created_at).getTime() : 0;
          bValue = b.created_at ? new Date(b.created_at).getTime() : 0;
          break;
        case 'removal':
          aValue = a.removal_date ? new Date(a.removal_date).getTime() : 0;
          bValue = b.removal_date ? new Date(b.removal_date).getTime() : 0;
          break;
        case 'status':
          aValue = isExpired(a) ? 'expired' : 'active';
          bValue = isExpired(b) ? 'expired' : 'active';
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

function isExpired(announcement: Announcement): boolean {
  if (!announcement.removal_date) return false;
  const removalDate = new Date(announcement.removal_date);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return removalDate < today;
}

function formatDate(dateString: string): string {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function filterByStatus(status: 'active' | 'expired' | 'all') {
  // Toggle filter: if already active, clear it; otherwise, set it
  if (activeFilter.value === status) {
    activeFilter.value = null;
  } else {
    activeFilter.value = status;
  }
}

function sortBy(column: 'subject' | 'created' | 'removal' | 'status') {
  if (sortColumn.value === column) {
    // Toggle direction if clicking the same column
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  } else {
    // Set new column and default to ascending
    sortColumn.value = column;
    sortDirection.value = 'asc';
  }
}

function applyFilters() {
  refetch();
}

const deleteMutation = useDeleteAnnouncement();

const deleteMessage = computed(() => {
  const subject = announcementToDelete.value?.subject || 'this announcement';
  return `Are you sure you want to delete "${subject}"?`;
});

function viewAnnouncement(id: number) {
  // For now, just navigate to edit page to view
  router.push(`/announcements/${id}/edit`);
}

function editAnnouncement(id: number) {
  router.push(`/announcements/${id}/edit`);
}

function deleteAnnouncement(announcement: Announcement) {
  announcementToDelete.value = announcement;
  showDeleteModal.value = true;
}

async function confirmDelete() {
  if (!announcementToDelete.value) return;

  const announcementId = announcementToDelete.value.id;
  deletingAnnouncementId.value = announcementId;

  try {
    await deleteMutation.mutateAsync(announcementId);
    showDeleteModal.value = false;
    announcementToDelete.value = null;
    refetch();
  } catch (error: any) {
    console.error('Error deleting announcement:', error);
    alert(`Failed to delete announcement: ${error.message || 'Unknown error'}`);
  } finally {
    deletingAnnouncementId.value = null;
  }
}

function cancelDelete() {
  showDeleteModal.value = false;
  announcementToDelete.value = null;
}

// Watch for changes in includeDeleted to refetch data
watch(includeDeleted, () => {
  refetch();
  if (includeDeleted.value) {
    activeFilter.value = 'all';
  }
});
</script>
