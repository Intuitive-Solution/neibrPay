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

    <!-- Loading State -->
    <div v-if="isLoading" class="text-center py-12">
      <div
        class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"
      ></div>
      <p class="mt-4 text-sm text-gray-600">Loading settings...</p>
    </div>

    <!-- Settings Content -->
    <div v-else>
      <!-- Tab Navigation -->
      <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
          <button
            @click="activeTab = 'hoa'"
            :class="[
              activeTab === 'hoa'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
            ]"
          >
            HOA
          </button>
          <button
            @click="activeTab = 'user'"
            :class="[
              activeTab === 'user'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
            ]"
          >
            User
          </button>
          <button
            v-if="!isResident"
            @click="activeTab = 'localization'"
            :class="[
              activeTab === 'localization'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
            ]"
          >
            Localization
          </button>
          <button
            @click="activeTab = 'security'"
            :class="[
              activeTab === 'security'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
            ]"
          >
            Security
          </button>
          <button
            v-if="!isResident"
            @click="activeTab = 'payments'"
            :class="[
              activeTab === 'payments'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
            ]"
          >
            Payments
          </button>
          <button
            v-if="!isResident"
            @click="activeTab = 'bank'"
            :class="[
              activeTab === 'bank'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200',
            ]"
          >
            Bank
          </button>
        </nav>
      </div>

      <!-- Tab Content -->
      <div class="card-modern">
        <!-- HOA Tab -->
        <div v-if="activeTab === 'hoa'" class="space-y-6">
          <h2 class="text-base font-semibold text-gray-900">
            Community Settings
          </h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Community Name</label
              >
              <input
                v-model="hoaForm.name"
                type="text"
                class="input-field"
                placeholder="Enter community name"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Address</label
              >
              <input
                id="hoa-address"
                v-model="hoaForm.address"
                type="text"
                class="input-field"
                autocomplete="off"
                placeholder="Start typing address..."
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Phone Number</label
              >
              <input
                v-model="hoaForm.phone"
                type="tel"
                class="input-field"
                placeholder="Enter phone number"
              />
            </div>
          </div>
          <div
            v-if="!isResident"
            class="flex justify-end pt-4 border-t border-gray-200"
          >
            <button
              @click="saveHoaSettings"
              :disabled="isSavingHoa"
              class="btn-primary"
            >
              <span v-if="isSavingHoa">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </div>

        <!-- User Tab -->
        <div v-if="activeTab === 'user'" class="space-y-6">
          <h2 class="text-base font-semibold text-gray-900">User Settings</h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Full Name</label
              >
              <input
                v-model="userForm.name"
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
                v-model="userForm.email"
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
                v-model="userForm.phone_number"
                type="tel"
                class="input-field"
                placeholder="Enter phone number"
              />
            </div>
          </div>
          <div class="flex justify-end pt-4 border-t border-gray-200">
            <button
              @click="saveUserSettings"
              :disabled="isSavingUser"
              class="btn-primary"
            >
              <span v-if="isSavingUser">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </div>

        <!-- Localization Tab -->
        <div v-if="activeTab === 'localization'" class="space-y-6">
          <h2 class="text-base font-semibold text-gray-900">
            Localization Settings
          </h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Currency</label
              >
              <select v-model="localizationForm.currency" class="input-field">
                <option value="USD">USD</option>
                <option value="INR">INR</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Currency Format</label
              >
              <input
                v-model="localizationForm.currency_format"
                type="text"
                class="input-field"
                placeholder="e.g., $#,##0.00 or ₹#,##0.00"
              />
              <p class="mt-1 text-xs text-gray-500">
                Examples: $#,##0.00, ₹#,##0.00
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Timezone</label
              >
              <select v-model="localizationForm.timezone" class="input-field">
                <option value="UTC">UTC</option>
                <option value="America/New_York">America/New_York (EST)</option>
                <option value="America/Chicago">America/Chicago (CST)</option>
                <option value="America/Denver">America/Denver (MST)</option>
                <option value="America/Los_Angeles">
                  America/Los_Angeles (PST)
                </option>
                <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                <option value="Europe/London">Europe/London (GMT)</option>
                <option value="Europe/Paris">Europe/Paris (CET)</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Date Format</label
              >
              <select
                v-model="localizationForm.date_format"
                class="input-field"
              >
                <option value="MM/DD/YYYY">MM/DD/YYYY</option>
                <option value="DD/MM/YYYY">DD/MM/YYYY</option>
                <option value="YYYY-MM-DD">YYYY-MM-DD</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >First Month of Year</label
              >
              <select
                v-model="localizationForm.first_month_of_year"
                class="input-field"
              >
                <option v-for="month in months" :key="month" :value="month">
                  {{ month }}
                </option>
              </select>
            </div>
          </div>
          <div class="flex justify-end pt-4 border-t border-gray-200">
            <button
              @click="saveLocalizationSettings"
              :disabled="isSavingLocalization"
              class="btn-primary"
            >
              <span v-if="isSavingLocalization">Saving...</span>
              <span v-else>Save Changes</span>
            </button>
          </div>
        </div>

        <!-- Security Tab -->
        <div v-if="activeTab === 'security'" class="space-y-6">
          <h2 class="text-base font-semibold text-gray-900">
            Security Settings
          </h2>
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Current Password</label
              >
              <input
                v-model="passwordForm.current_password"
                type="password"
                class="input-field"
                placeholder="Enter current password"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >New Password</label
              >
              <input
                v-model="passwordForm.new_password"
                type="password"
                class="input-field"
                placeholder="Enter new password"
              />
              <p class="mt-1 text-xs text-gray-500">
                Password must be at least 8 characters long
              </p>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Confirm New Password</label
              >
              <input
                v-model="passwordForm.new_password_confirmation"
                type="password"
                class="input-field"
                placeholder="Confirm new password"
              />
              <p
                v-if="
                  passwordForm.new_password &&
                  passwordForm.new_password_confirmation &&
                  passwordForm.new_password !==
                    passwordForm.new_password_confirmation
                "
                class="mt-1 text-xs text-red-500"
              >
                Passwords do not match
              </p>
            </div>
          </div>
          <div class="flex justify-end pt-4 border-t border-gray-200">
            <button
              @click="savePassword"
              :disabled="isSavingPassword || !isPasswordFormValid"
              class="btn-primary"
            >
              <span v-if="isSavingPassword">Saving...</span>
              <span v-else>Change Password</span>
            </button>
          </div>
        </div>

        <!-- Payments Tab -->
        <div v-if="activeTab === 'payments'" class="space-y-6">
          <h2 class="text-base font-semibold text-gray-900">
            Payment Settings
          </h2>
          <div class="space-y-4">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
              <h3 class="font-medium text-blue-900 mb-2">Stripe Connect</h3>
              <p class="text-sm text-blue-800 mb-4">
                Connect your Stripe account to accept online payments from
                residents.
              </p>

              <!-- Not Connected State -->
              <div v-if="!stripeConnectId" class="space-y-4">
                <p class="text-sm text-gray-600">
                  Status:
                  <span class="font-semibold text-red-600">Not Connected</span>
                </p>
                <button
                  @click="connectStripe"
                  :disabled="isConnectingStripe"
                  class="btn-primary"
                >
                  <span v-if="isConnectingStripe">Connecting...</span>
                  <span v-else>Connect Stripe Account</span>
                </button>
              </div>

              <!-- Connected State -->
              <div v-else class="space-y-4">
                <div class="space-y-2">
                  <p class="text-sm text-gray-600">
                    Status:
                    <span
                      :class="[
                        'font-semibold',
                        stripeConnectStatus === 'active'
                          ? 'text-green-600'
                          : 'text-yellow-600',
                      ]"
                    >
                      {{
                        stripeConnectStatus === 'active' ? 'Active' : 'Pending'
                      }}
                    </span>
                  </p>
                  <p class="text-xs text-gray-500">
                    Account ID: {{ stripeConnectId }}
                  </p>
                </div>

                <!-- Pending Details -->
                <div
                  v-if="stripeConnectStatus !== 'active'"
                  class="p-3 bg-yellow-50 border border-yellow-200 rounded"
                >
                  <p class="text-sm text-yellow-800">
                    {{
                      !chargesEnabled
                        ? 'Your account is being reviewed. Payments cannot be processed yet.'
                        : 'Please complete your account setup.'
                    }}
                  </p>
                </div>

                <!-- Active Details -->
                <div
                  v-else
                  class="space-y-2 p-3 bg-green-50 border border-green-200 rounded"
                >
                  <p class="text-sm text-green-800 font-medium">
                    ✓ Your account is ready to accept payments
                  </p>
                  <p class="text-xs text-green-700">
                    Platform fee: 1% of each transaction
                  </p>
                </div>

                <div class="flex gap-2 pt-2 flex-wrap">
                  <button
                    @click="openStripeDashboard"
                    :disabled="isLoadingDashboard"
                    class="btn-primary flex-1 min-w-[120px]"
                  >
                    <span v-if="isLoadingDashboard">Loading...</span>
                    <span v-else>Open Dashboard</span>
                  </button>
                  <button
                    @click="verifyStripeStatus"
                    :disabled="isVerifyingStatus"
                    class="btn-secondary flex-1 min-w-[120px]"
                  >
                    <span v-if="isVerifyingStatus">Checking...</span>
                    <span v-else>Refresh Status</span>
                  </button>
                  <button
                    @click="showDisconnectConfirmation = true"
                    :disabled="isDisconnecting"
                    class="btn-secondary flex-1 min-w-[120px] text-red-600 hover:text-red-700 hover:bg-red-50 border-red-200"
                  >
                    <span v-if="isDisconnecting">Disconnecting...</span>
                    <span v-else>Disconnect</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bank Tab -->
        <div v-if="activeTab === 'bank'" class="space-y-6">
          <h2 class="text-base font-semibold text-gray-900">
            Bank Account Integration
          </h2>
          <p class="text-sm text-gray-600">
            Connect your bank account to view and reconcile transactions.
          </p>

          <!-- Connected Bank Accounts -->
          <div v-if="bankAccounts.length > 0" class="space-y-4">
            <h3 class="text-sm font-medium text-gray-900">
              Connected Accounts
            </h3>
            <div class="space-y-3">
              <div
                v-for="account in bankAccounts"
                :key="account.id"
                class="p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors"
              >
                <div class="flex items-start justify-between">
                  <div class="flex-1">
                    <p class="font-medium text-gray-900">
                      {{ account.account_name }}
                    </p>
                    <p class="text-sm text-gray-600 mt-1">
                      {{ account.institution_name }} • ••••{{
                        account.account_mask
                      }}
                    </p>
                    <div
                      class="mt-2 flex items-center gap-4 text-xs text-gray-500"
                    >
                      <span
                        :class="[
                          'px-2 py-1 rounded',
                          account.status === 'active'
                            ? 'bg-green-50 text-green-700'
                            : 'bg-yellow-50 text-yellow-700',
                        ]"
                      >
                        {{
                          account.status === 'active' ? 'Connected' : 'Error'
                        }}
                      </span>
                      <span v-if="account.last_synced_at">
                        Last synced: {{ formatDate(account.last_synced_at) }}
                      </span>
                    </div>
                  </div>
                  <button
                    @click="disconnectBank(account.id)"
                    :disabled="isDisconnectingBank"
                    class="ml-4 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                  >
                    Disconnect
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- No Accounts State -->
          <div
            v-else
            class="p-4 bg-gray-50 border border-gray-200 rounded-lg text-center"
          >
            <svg
              class="mx-auto h-12 w-12 text-gray-400 mb-3"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
              />
            </svg>
            <p class="text-gray-600 mb-4">No bank accounts connected yet</p>
            <button
              @click="connectBank"
              :disabled="isConnectingBank"
              class="btn-primary"
            >
              <span v-if="isConnectingBank">Connecting...</span>
              <span v-else>Connect Bank Account</span>
            </button>
          </div>

          <!-- Connect New Account Button -->
          <div
            v-if="bankAccounts.length > 0"
            class="pt-4 border-t border-gray-200"
          >
            <button
              @click="connectBank"
              :disabled="isConnectingBank"
              class="btn-primary"
            >
              <span v-if="isConnectingBank">Connecting...</span>
              <span v-else>+ Add Another Bank Account</span>
            </button>
          </div>

          <!-- Transaction Sync Settings -->
          <div
            v-if="bankAccounts.length > 0"
            class="p-4 bg-blue-50 border border-blue-200 rounded-lg"
          >
            <h3 class="font-medium text-blue-900 mb-2">Transaction Sync</h3>
            <p class="text-sm text-blue-800 mb-4">
              Transactions are automatically synced every hour. You can also
              manually refresh anytime.
            </p>
            <button
              @click="manualSyncBankAccounts"
              :disabled="isManualSyncing"
              class="btn-secondary"
            >
              <span v-if="isManualSyncing">Syncing...</span>
              <span v-else>Refresh Transactions Now</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Stripe Disconnect Confirmation Modal -->
  <div
    v-if="showDisconnectConfirmation"
    class="fixed inset-0 z-50 overflow-y-auto"
  >
    <div
      class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0"
    >
      <!-- Background overlay -->
      <div
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
        @click="showDisconnectConfirmation = false"
      ></div>

      <!-- Modal -->
      <div
        class="relative inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full"
      >
        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
          <div class="sm:flex sm:items-start">
            <div
              class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10"
            >
              <svg
                class="h-6 w-6 text-red-600"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 9v2m0 4v2m0 4v2M6.343 17.657a8 8 0 1111.314 0M12 5a1 1 0 110-2 1 1 0 010 2z"
                />
              </svg>
            </div>
            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
              <h3
                class="text-lg leading-6 font-medium text-gray-900"
                id="modal-title"
              >
                Disconnect Stripe Account?
              </h3>
              <div class="mt-2 space-y-3">
                <p class="text-sm text-gray-500">
                  This will permanently disconnect your Stripe Express account
                  and clear all payment settings.
                </p>
                <div class="p-3 bg-yellow-50 border border-yellow-200 rounded">
                  <p class="text-xs text-yellow-800">
                    <strong>Warning:</strong> After disconnection:
                  </p>
                  <ul
                    class="text-xs text-yellow-800 mt-2 space-y-1 list-disc list-inside"
                  >
                    <li>Residents will not be able to pay invoices online</li>
                    <li>You can reconnect a different Stripe account later</li>
                    <li>This action cannot be undone immediately</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Actions -->
        <div
          class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2"
        >
          <button
            @click="disconnectStripe"
            :disabled="isDisconnecting"
            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto sm:text-sm transition-colors"
          >
            <span v-if="isDisconnecting">Disconnecting...</span>
            <span v-else>Yes, Disconnect</span>
          </button>
          <button
            @click="showDisconnectConfirmation = false"
            :disabled="isDisconnecting"
            class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto sm:text-sm transition-colors"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import {
  useSettings,
  useUpdateTenantSettings,
  useUpdateUserProfile,
  useUpdatePassword,
  useUpdateLocalization,
  stripeApi,
  usePlaidLinkToken,
  useBankAccounts,
  useExchangeToken,
  useDisconnectBankAccount,
  useSyncAccount,
  type SettingsData,
} from '@neibrpay/api-client';
import { useAuthStore } from '../stores/auth';

// Auth store and route
const authStore = useAuthStore();
const route = useRoute();
const isResident = computed(() => authStore.isResident);

// ---------------- GOOGLE AUTOCOMPLETE FOR HOA ADDRESS ----------------

async function loadGoogleMapsScript() {
  return new Promise((resolve, reject) => {
    // Already loaded?
    if (window.google && window.google.maps) {
      resolve(true);
      return;
    }

    // Script exists but waiting
    const existingScript = document.getElementById('google-maps-script');
    if (existingScript) {
      existingScript.addEventListener('load', resolve);
      return;
    }

    // Create script tag
    const script = document.createElement('script');
    script.id = 'google-maps-script';
    script.src = `https://maps.googleapis.com/maps/api/js?key=${
      import.meta.env.VITE_GOOGLE_MAPS_API_KEY
    }&libraries=places`;
    script.async = true;

    script.onload = resolve;
    script.onerror = reject;

    document.head.appendChild(script);
  });
}

async function initHoaAddressAutocomplete() {
  try {
    await google.maps.importLibrary('places');

    const input = document.getElementById('hoa-address') as HTMLTextAreaElement;
    if (!input) return;

    const autocomplete = new google.maps.places.Autocomplete(input, {
      fields: ['formatted_address'],
      componentRestrictions: { country: ['us'] },
    });

    autocomplete.addListener('place_changed', () => {
      const place = autocomplete.getPlace();
      if (!place?.formatted_address) return;

      hoaForm.value.address = place.formatted_address;

      console.log('HOA Address Autocomplete:', place.formatted_address);
    });
  } catch (err) {
    console.error('HOA address autocomplete error:', err);
  }
}

// Tab state - initialize from query param if present
const validTabs = [
  'hoa',
  'user',
  'localization',
  'security',
  'payments',
  'bank',
] as const;
type TabType = (typeof validTabs)[number];

const getInitialTab = (): TabType => {
  const tabParam = route.query.tab as string;
  if (tabParam && validTabs.includes(tabParam as TabType)) {
    return tabParam as TabType;
  }
  return 'hoa';
};

const activeTab = ref<TabType>(getInitialTab());

// Redirect to 'user' tab if resident tries to access admin-only tabs
watch(
  [isResident, activeTab],
  ([isResidentValue, currentTab]: [
    boolean,
    'hoa' | 'user' | 'localization' | 'security' | 'payments' | 'bank',
  ]) => {
    if (
      isResidentValue &&
      (currentTab === 'localization' ||
        currentTab === 'payments' ||
        currentTab === 'bank')
    ) {
      activeTab.value = 'user';
    }
  },
  { immediate: true }
);

// Success/Error messages
const showSuccessMessage = ref(false);
const showErrorMessage = ref(false);
const successMessage = ref('');
const errorMessage = ref('');

// Form data
const hoaForm = ref({
  name: '',
  address: '',
  phone: '',
});

const userForm = ref({
  name: '',
  email: '',
  phone_number: '',
});

const localizationForm = ref({
  currency: 'USD',
  currency_format: '$#,##0.00',
  timezone: 'UTC',
  date_format: 'MM/DD/YYYY',
  first_month_of_year: 'January',
});

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: '',
});

// Months array
const months = [
  'January',
  'February',
  'March',
  'April',
  'May',
  'June',
  'July',
  'August',
  'September',
  'October',
  'November',
  'December',
];

// Query and mutations
const { data: settingsData, isLoading } = useSettings();
const updateTenantMutation = useUpdateTenantSettings();
const updateUserMutation = useUpdateUserProfile();
const updatePasswordMutation = useUpdatePassword();
const updateLocalizationMutation = useUpdateLocalization();

// Loading states
const isSavingHoa = computed(() => updateTenantMutation.isPending.value);
const isSavingUser = computed(() => updateUserMutation.isPending.value);
const isSavingPassword = computed(() => updatePasswordMutation.isPending.value);
const isSavingLocalization = computed(
  () => updateLocalizationMutation.isPending.value
);

// Password form validation
const isPasswordFormValid = computed(() => {
  return (
    passwordForm.value.current_password.length > 0 &&
    passwordForm.value.new_password.length >= 8 &&
    passwordForm.value.new_password ===
      passwordForm.value.new_password_confirmation
  );
});

// Watch for settings data and populate forms
watch(
  settingsData,
  (data: SettingsData | undefined) => {
    if (data) {
      // Populate HOA form
      hoaForm.value = {
        name: data.tenant.name || '',
        address: data.tenant.address || '',
        phone: data.tenant.phone || '',
      };

      // Populate user form
      userForm.value = {
        name: data.user.name || '',
        email: data.user.email || '',
        phone_number: data.user.phone_number || '',
      };

      // Populate localization form
      localizationForm.value = {
        currency: data.tenant.settings.currency || 'USD',
        currency_format: data.tenant.settings.currency_format || '$#,##0.00',
        timezone: data.tenant.settings.timezone || 'UTC',
        date_format: data.tenant.settings.date_format || 'MM/DD/YYYY',
        first_month_of_year:
          data.tenant.settings.first_month_of_year || 'January',
      };
    }
  },
  { immediate: true }
);

onMounted(async () => {
  await loadGoogleMapsScript();

  if (activeTab.value === 'hoa') {
    await nextTick();
    initHoaAddressAutocomplete();
  }
});

watch(activeTab, async tab => {
  if (tab === 'hoa') {
    await nextTick();
    initHoaAddressAutocomplete();
  }
});

// Helper functions
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

// Save functions
const saveHoaSettings = async () => {
  try {
    await updateTenantMutation.mutateAsync(hoaForm.value);
    showSuccess('HOA settings updated successfully');
  } catch (error: any) {
    showError(error.message || 'Failed to update HOA settings');
  }
};

const saveUserSettings = async () => {
  try {
    await updateUserMutation.mutateAsync(userForm.value);
    showSuccess('User profile updated successfully');
  } catch (error: any) {
    showError(error.message || 'Failed to update user profile');
  }
};

const saveLocalizationSettings = async () => {
  try {
    await updateLocalizationMutation.mutateAsync(localizationForm.value);
    showSuccess('Localization settings updated successfully');
  } catch (error: any) {
    showError(error.message || 'Failed to update localization settings');
  }
};

const savePassword = async () => {
  if (!isPasswordFormValid.value) {
    showError('Please fill all password fields correctly');
    return;
  }

  try {
    await updatePasswordMutation.mutateAsync(passwordForm.value);
    showSuccess('Password updated successfully');
    // Reset password form
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: '',
    };
  } catch (error: any) {
    showError(error.message || 'Failed to update password');
  }
};

// ============ STRIPE PAYMENTS SECTION ============

// Stripe state
const stripeConnectId = computed(
  () => settingsData.value?.tenant?.settings?.stripe_connect_id || null
);
const stripeConnectStatus = computed(
  () =>
    settingsData.value?.tenant?.settings?.stripe_connect_status ||
    'not_connected'
);
const chargesEnabled = computed(
  () => settingsData.value?.tenant?.settings?.charges_enabled || false
);

// Stripe loading states
const isConnectingStripe = ref(false);
const isLoadingDashboard = ref(false);
const isVerifyingStatus = ref(false);
const isDisconnecting = ref(false);
const showDisconnectConfirmation = ref(false);

/**
 * Initiate Stripe Connect account creation
 */
const connectStripe = async () => {
  isConnectingStripe.value = true;
  try {
    const response = await stripeApi.connect();
    if (response.onboarding_url) {
      // Redirect to Stripe onboarding
      window.location.href = response.onboarding_url;
    } else {
      showError('Failed to get onboarding URL');
    }
  } catch (error: any) {
    showError(error.message || 'Failed to connect Stripe account');
  } finally {
    isConnectingStripe.value = false;
  }
};

/**
 * Open Stripe Express dashboard
 */
const openStripeDashboard = async () => {
  isLoadingDashboard.value = true;
  try {
    const response = await stripeApi.getDashboardLink();
    if (response.dashboard_url) {
      window.open(response.dashboard_url, '_blank');
    } else {
      showError('Failed to get dashboard link');
    }
  } catch (error: any) {
    showError(error.message || 'Failed to load Stripe dashboard');
  } finally {
    isLoadingDashboard.value = false;
  }
};

/**
 * Verify Stripe account status
 */
const verifyStripeStatus = async () => {
  isVerifyingStatus.value = true;
  try {
    const response = await stripeApi.verifyStatus();
    showSuccess('Account status updated');
    // Invalidate settings query to refresh the UI
    // The mutation will handle this automatically
  } catch (error: any) {
    showError(error.message || 'Failed to verify account status');
  } finally {
    isVerifyingStatus.value = false;
  }
};

/**
 * Disconnect Stripe account
 */
const disconnectStripe = async () => {
  isDisconnecting.value = true;
  try {
    await stripeApi.disconnect();
    showSuccess(
      'Stripe account disconnected successfully. You can now connect a new account.'
    );
    showDisconnectConfirmation.value = false;
    // Small delay to allow settings to update
    setTimeout(() => {
      // The settings query will automatically refetch via TanStack Query
    }, 500);
  } catch (error: any) {
    showError(error.message || 'Failed to disconnect Stripe account');
  } finally {
    isDisconnecting.value = false;
  }
};

/**
 * Check if returning from Stripe onboarding (on mount)
 */
const checkStripeReturn = () => {
  const params = new URLSearchParams(window.location.search);
  if (params.get('stripe_connect') === 'success') {
    showSuccess('Stripe account connected! Verifying status...');
    // Verify status immediately
    verifyStripeStatus();
    // Clean up URL
    window.history.replaceState({}, document.title, window.location.pathname);
  } else if (params.get('stripe_connect') === 'refresh') {
    showError('Stripe onboarding was refreshed. Please try again.');
    window.history.replaceState({}, document.title, window.location.pathname);
  }
};

// Check for Stripe return on mount
import { onBeforeMount } from 'vue';
onBeforeMount(() => {
  checkStripeReturn();
});

// ============ PLAID BANK INTEGRATION ============

// Plaid queries and mutations
const { data: linkTokenData, isLoading: isLoadingLinkToken } =
  usePlaidLinkToken();
const { data: bankAccountsData } = useBankAccounts();
const exchangeTokenMutation = useExchangeToken();
const disconnectBankMutation = useDisconnectBankAccount();
const syncAccountMutation = useSyncAccount();

// Bank account list
const bankAccounts = computed(
  () => bankAccountsData.value?.bank_accounts || []
);

// Plaid loading states
const isConnectingBank = ref(false);
const isDisconnectingBank = ref(false);
const isManualSyncing = ref(false);

// Format date helper
const formatDate = (dateString: string | null) => {
  if (!dateString) return 'Never';
  try {
    return new Date(dateString).toLocaleDateString('en-US', {
      month: 'short',
      day: 'numeric',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    });
  } catch {
    return 'Invalid date';
  }
};

/**
 * Load Plaid Link script via fetch and eval (bypasses CDN 403)
 */
const loadPlaidScript = async (): Promise<void> => {
  return new Promise((resolve, reject) => {
    // Check if already loaded
    if ((window as any).Plaid) {
      resolve();
      return;
    }

    // Try loading script via standard method first
    const script = document.createElement('script');
    script.id = 'plaid-link-script';
    script.src = 'https://cdn.plaid.com/link/v2/stable/link-initialize.js';

    script.onload = () => {
      console.log('Plaid script loaded successfully');
      resolve();
    };

    script.onerror = e => {
      console.error('Plaid script load error:', e);
      // Try alternative URL
      const altScript = document.createElement('script');
      altScript.src = 'https://plaid.com/link/v2/stable/link-initialize.js';
      altScript.onload = () => {
        console.log('Plaid script loaded via alternative URL');
        resolve();
      };
      altScript.onerror = () => {
        reject(
          new Error(
            'Failed to load Plaid Link. Please try using a different browser or disable ad blockers.'
          )
        );
      };
      document.head.appendChild(altScript);
    };

    document.head.appendChild(script);
  });
};

/**
 * Initialize Plaid Link
 */
const connectBank = async () => {
  // First, fetch link token if not already loaded
  if (!linkTokenData.value?.link_token) {
    showError('Loading bank connection...');
    await new Promise(resolve => setTimeout(resolve, 1500));

    if (!linkTokenData.value?.link_token) {
      showError(
        'Failed to initialize bank connection. Please refresh and try again.'
      );
      return;
    }
  }

  isConnectingBank.value = true;

  try {
    // Load Plaid script
    await loadPlaidScript();

    const linkToken = linkTokenData.value!.link_token;

    // Create and open handler
    const handler = (window as any).Plaid.create({
      token: linkToken,
      onSuccess: async (publicToken: string, metadata: any) => {
        console.log('Plaid Link Success:', metadata);
        try {
          showSuccess('Connecting bank account...');
          await exchangeTokenMutation.mutateAsync({
            public_token: publicToken,
          });
          showSuccess('Bank account connected successfully!');
        } catch (error: any) {
          console.error('Exchange token error:', error);
          showError(error.message || 'Failed to connect bank account');
        } finally {
          isConnectingBank.value = false;
        }
      },
      onExit: (err: any, metadata: any) => {
        console.log('Plaid Link Exit:', { err, metadata });
        isConnectingBank.value = false;
        if (err) {
          showError(
            `Bank connection closed: ${err.display_message || err.error_message || 'User cancelled'}`
          );
        }
      },
      onEvent: (eventName: string, metadata: any) => {
        console.log('Plaid Event:', eventName, metadata);
      },
    });

    handler.open();
  } catch (error: any) {
    console.error('Connect bank error:', error);
    isConnectingBank.value = false;
    showError(
      error.message ||
        'Failed to open bank connection. Please try in a different browser.'
    );
  }
};

/**
 * Disconnect a bank account
 */
const disconnectBank = async (accountId: number) => {
  if (!confirm('Are you sure you want to disconnect this bank account?')) {
    return;
  }

  isDisconnectingBank.value = true;
  try {
    await disconnectBankMutation.mutateAsync(accountId);
    showSuccess('Bank account disconnected successfully');
  } catch (error: any) {
    showError(error.message || 'Failed to disconnect bank account');
  } finally {
    isDisconnectingBank.value = false;
  }
};

/**
 * Manually sync all bank accounts
 */
const manualSyncBankAccounts = async () => {
  isManualSyncing.value = true;
  try {
    // Sync all accounts
    for (const account of bankAccounts.value) {
      await syncAccountMutation.mutateAsync({
        bank_account_id: account.id,
      });
    }
    showSuccess('Bank accounts synced successfully');
  } catch (error: any) {
    showError(error.message || 'Failed to sync bank accounts');
  } finally {
    isManualSyncing.value = false;
  }
};
</script>
