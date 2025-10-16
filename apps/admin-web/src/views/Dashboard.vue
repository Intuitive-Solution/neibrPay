<template>
  <div class="space-y-6">
    <!-- Welcome Section -->
    <div class="card card-hover">
      <h2 class="text-2xl font-bold text-gray-900 mb-2">Welcome Back!</h2>
      <p class="text-gray-600">
        Here's an overview of your HOA community management
      </p>
    </div>

    <!-- Dashboard Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Units -->
      <div class="card card-hover">
        <div class="flex items-center">
          <div class="p-3 bg-primary-100 rounded-lg">
            <svg
              class="w-6 h-6 text-primary"
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
          </div>
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-600">Total Units</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">-</p>
          </div>
        </div>
      </div>

      <!-- Active Invoices -->
      <div class="card card-hover">
        <div class="flex items-center">
          <div class="p-3 bg-primary-100 rounded-lg">
            <svg
              class="w-6 h-6 text-primary"
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
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-600">Active Invoices</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">-</p>
          </div>
        </div>
      </div>

      <!-- Total Payments -->
      <div class="card card-hover">
        <div class="flex items-center">
          <div class="p-3 bg-primary-100 rounded-lg">
            <svg
              class="w-6 h-6 text-primary"
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
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-600">Total Collected</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">$-</p>
          </div>
        </div>
      </div>

      <!-- Residents -->
      <div class="card card-hover">
        <div class="flex items-center">
          <div class="p-3 bg-primary-100 rounded-lg">
            <svg
              class="w-6 h-6 text-primary"
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
          <div class="ml-4">
            <h3 class="text-sm font-medium text-gray-600">Residents</h3>
            <p class="text-2xl font-bold text-gray-900 mt-1">-</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="card">
      <h3 class="text-base font-semibold text-gray-900 mb-4">Quick Actions</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <router-link to="/invoices/create" class="block">
          <div
            class="p-4 rounded-lg border border-gray-200 hover:border-primary hover:bg-primary-50 transition-colors duration-200 text-center"
          >
            <svg
              class="w-8 h-8 text-primary mx-auto mb-2"
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
            <p class="text-sm font-medium text-gray-900">New Invoice</p>
          </div>
        </router-link>

        <router-link to="/people/add" class="block">
          <div
            class="p-4 rounded-lg border border-gray-200 hover:border-primary hover:bg-primary-50 transition-colors duration-200 text-center"
          >
            <svg
              class="w-8 h-8 text-primary mx-auto mb-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
              />
            </svg>
            <p class="text-sm font-medium text-gray-900">Add Resident</p>
          </div>
        </router-link>

        <router-link to="/units/add" class="block">
          <div
            class="p-4 rounded-lg border border-gray-200 hover:border-primary hover:bg-primary-50 transition-colors duration-200 text-center"
          >
            <svg
              class="w-8 h-8 text-primary mx-auto mb-2"
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
            <p class="text-sm font-medium text-gray-900">Add Unit</p>
          </div>
        </router-link>

        <router-link to="/expenses/add" class="block">
          <div
            class="p-4 rounded-lg border border-gray-200 hover:border-primary hover:bg-primary-50 transition-colors duration-200 text-center"
          >
            <svg
              class="w-8 h-8 text-primary mx-auto mb-2"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"
              />
            </svg>
            <p class="text-sm font-medium text-gray-900">Add Expense</p>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
      <h3 class="text-base font-semibold text-gray-900 mb-4">
        Recent Activity
      </h3>
      <div class="text-center py-8">
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
          No Recent Activity
        </h3>
        <p class="mt-1 text-sm text-gray-500">
          Activity feed will be displayed here
        </p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
// Dashboard component - logout functionality is handled by AppSidebar
</script>
