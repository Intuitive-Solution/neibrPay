<template>
  <div class="space-y-6">
    <!-- Settings Sections -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Community Settings -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-900 mb-4">
          Community Settings
        </h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Community Name</label
            >
            <input
              type="text"
              class="input-field"
              placeholder="Enter community name"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Address</label
            >
            <textarea
              class="input-field"
              rows="3"
              placeholder="Enter community address"
            ></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Phone Number</label
            >
            <input
              type="tel"
              class="input-field"
              placeholder="Enter phone number"
            />
          </div>
        </div>
      </div>

      <!-- User Settings -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-900 mb-4">
          User Settings
        </h2>
        <div class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Full Name</label
            >
            <input
              type="text"
              class="input-field"
              placeholder="Enter full name"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Email Address</label
            >
            <input
              type="email"
              class="input-field"
              placeholder="Enter email address"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2"
              >Phone Number</label
            >
            <input
              type="tel"
              class="input-field"
              placeholder="Enter phone number"
            />
          </div>
        </div>
      </div>

      <!-- Notification Settings -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-900 mb-4">
          Notifications
        </h2>
        <div class="space-y-4">
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-medium text-gray-900">
                Email Notifications
              </h3>
              <p class="text-sm text-gray-500">
                Receive notifications via email
              </p>
            </div>
            <input
              type="checkbox"
              class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
            />
          </div>
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-medium text-gray-900">
                SMS Notifications
              </h3>
              <p class="text-sm text-gray-500">Receive notifications via SMS</p>
            </div>
            <input
              type="checkbox"
              class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
            />
          </div>
          <div class="flex items-center justify-between">
            <div>
              <h3 class="text-sm font-medium text-gray-900">
                Payment Reminders
              </h3>
              <p class="text-sm text-gray-500">
                Get reminders for upcoming payments
              </p>
            </div>
            <input
              type="checkbox"
              class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
            />
          </div>
        </div>
      </div>

      <!-- Security Settings -->
      <div class="card">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Security</h2>
        <div class="space-y-4">
          <button
            class="w-full text-left px-4 py-3 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-medium text-gray-900">
                  Change Password
                </h3>
                <p class="text-sm text-gray-500">
                  Update your account password
                </p>
              </div>
              <svg
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </div>
          </button>
          <button
            class="w-full text-left px-4 py-3 border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-medium text-gray-900">
                  Two-Factor Authentication
                </h3>
                <p class="text-sm text-gray-500">
                  Add extra security to your account
                </p>
              </div>
              <svg
                class="w-5 h-5 text-gray-400"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </div>
          </button>
        </div>
      </div>
    </div>

    <!-- Save Button -->
    <div class="flex justify-end">
      <button class="btn-primary">Save Changes</button>
    </div>
  </div>
</template>

<script setup lang="ts">
// Settings page component
</script>
