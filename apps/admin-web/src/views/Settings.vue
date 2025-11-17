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
              <textarea
                v-model="hoaForm.address"
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
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import {
  useSettings,
  useUpdateTenantSettings,
  useUpdateUserProfile,
  useUpdatePassword,
  useUpdateLocalization,
  type SettingsData,
} from '@neibrpay/api-client';
import { useAuthStore } from '../stores/auth';

// Auth store
const authStore = useAuthStore();
const isResident = computed(() => authStore.isResident);

// Tab state
const activeTab = ref<'hoa' | 'user' | 'localization' | 'security'>('hoa');

// Redirect to 'user' tab if resident tries to access 'localization'
watch(
  [isResident, activeTab],
  ([isResidentValue, currentTab]: [
    boolean,
    'hoa' | 'user' | 'localization' | 'security',
  ]) => {
    if (isResidentValue && currentTab === 'localization') {
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
</script>
