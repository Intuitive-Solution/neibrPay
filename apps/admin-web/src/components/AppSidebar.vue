<template>
  <div class="flex h-screen bg-gray-50">
    <!-- Sidebar -->
    <div
      :class="[
        'bg-white shadow-lg flex flex-col transition-all duration-300 ease-in-out',
        isCollapsed ? 'w-16' : 'w-64',
      ]"
    >
      <!-- Community Header -->
      <div class="p-6 px-2">
        <div v-if="!isCollapsed" class="flex items-center justify-between">
          <div class="flex items-center space-x-3">
            <img
              src="/owner-logo.png"
              alt="NeibrPay Logo"
              class="h-10 w-10 object-contain rounded-lg"
            />
            <div>
              <h1 class="text-xl font-bold">
                <span class="text-primary">Neibr</span>
                <span style="color: #2ee9b6">Pay</span>
              </h1>
              <p class="text-sm text-gray-600 mt-1">{{ communityName }}</p>
            </div>
          </div>

          <!-- Collapse Button -->
          <button
            @click="toggleCollapse"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200"
            title="Collapse sidebar"
          >
            <svg
              class="w-5 h-5 text-gray-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
              />
            </svg>
          </button>
        </div>

        <div v-else class="flex flex-col items-center space-y-4">
          <!-- Logo -->
          <img
            src="/owner-logo.png"
            alt="NeibrPay Logo"
            class="h-10 w-10 object-contain rounded-lg"
          />

          <!-- Collapse Button -->
          <button
            @click="toggleCollapse"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors duration-200"
            title="Expand sidebar"
          >
            <svg
              class="w-5 h-5 text-gray-600"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M13 5l7 7-7 7M5 5l7 7-7 7"
              />
            </svg>
          </button>
        </div>
      </div>

      <!-- Navigation Links -->
      <nav :class="['flex-1 py-6', isCollapsed ? 'px-2' : 'px-4']">
        <ul class="space-y-2">
          <li>
            <router-link
              to="/"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Dashboard'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Dashboard' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"
                />
              </svg>
              <span v-if="!isCollapsed">Dashboard</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/invoices"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Invoices'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Invoices' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
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
              <span v-if="!isCollapsed">Invoices</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/charges"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Charges' ||
                $route.name === 'AddCharge' ||
                $route.name === 'EditCharge'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Charges' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"
                />
              </svg>
              <span v-if="!isCollapsed">Charges</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/people"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'People'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'People' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
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
              <span v-if="!isCollapsed">People</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/units"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Units' ||
                $route.name === 'AddUnit' ||
                $route.name === 'EditUnit'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Units' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
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
              <span v-if="!isCollapsed">Units</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/payments"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Payments'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Payments' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
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
              <span v-if="!isCollapsed">Payments</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/expenses"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Expenses' ||
                $route.name === 'AddExpense' ||
                $route.name === 'EditExpense' ||
                $route.name === 'ExpenseDetail'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Expenses' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
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
              <span v-if="!isCollapsed">Expenses</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/vendors"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Vendors'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Vendors' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
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
              <span v-if="!isCollapsed">Vendors</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/settings"
              :class="[
                'flex items-center py-3 text-sm font-medium rounded-lg transition-colors duration-200',
                $route.name === 'Settings'
                  ? 'bg-primary text-white'
                  : 'text-gray-700 hover:bg-gray-100',
                isCollapsed ? 'justify-center px-2' : 'px-4',
              ]"
              :title="isCollapsed ? 'Settings' : ''"
            >
              <svg
                :class="['w-5 h-5', isCollapsed ? '' : 'mr-3']"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
                />
              </svg>
              <span v-if="!isCollapsed">Settings</span>
            </router-link>
          </li>
        </ul>
      </nav>

      <!-- User Info & Logout -->
      <div :class="['border-t border-gray-200', isCollapsed ? 'p-2' : 'p-4']">
        <div v-if="!isCollapsed" class="flex items-center space-x-3 mb-4">
          <div
            class="w-8 h-8 bg-primary rounded-full flex items-center justify-center"
          >
            <span class="text-white font-medium text-sm">
              {{ userInitials }}
            </span>
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 truncate">
              {{ userDisplayName }}
            </p>
            <p class="text-xs text-gray-500 truncate">{{ userEmail }}</p>
          </div>
        </div>
        <div v-else class="flex justify-center mb-4">
          <div
            class="w-8 h-8 bg-primary rounded-full flex items-center justify-center"
            :title="`${userDisplayName} (${userEmail})`"
          >
            <span class="text-white font-medium text-sm">
              {{ userInitials }}
            </span>
          </div>
        </div>

        <button
          @click="handleLogout"
          :disabled="isLoading"
          :class="[
            'flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed',
            isCollapsed ? 'w-full' : 'w-full',
          ]"
          :title="isCollapsed ? 'Sign Out' : ''"
        >
          <svg
            v-if="isLoading"
            :class="[
              'animate-spin h-4 w-4 text-gray-500',
              isCollapsed ? '' : '-ml-1 mr-2',
            ]"
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
          <svg
            v-else
            :class="['w-4 h-4', isCollapsed ? '' : 'mr-2']"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"
            />
          </svg>
          <span v-if="!isCollapsed">{{
            isLoading ? 'Signing out...' : 'Sign Out'
          }}</span>
        </button>
      </div>
    </div>

    <!-- Main Content Area -->
    <div
      class="flex-1 flex flex-col overflow-hidden"
      style="background-color: #f3f8fd"
    >
      <!-- Top Header - Hidden for Add/Edit pages -->
      <header v-if="!isAddEditPage" class="px-6 py-4">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">
              {{ pageTitle }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">{{ pageDescription }}</p>
          </div>

          <!-- Search Bar -->
          <div class="flex items-center space-x-4">
            <div class="relative">
              <input
                type="text"
                placeholder="Search..."
                class="w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary transition-colors duration-200"
              />
              <svg
                class="absolute left-3 top-2.5 h-5 w-5 text-gray-400"
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
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 overflow-auto p-6" style="background-color: #f3f8fd">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Collapse state
const isCollapsed = ref(false);

// Toggle collapse function
const toggleCollapse = () => {
  isCollapsed.value = !isCollapsed.value;
};

// Computed properties
const communityName = computed(() => authStore.tenantName || 'Community');
const userDisplayName = computed(() => authStore.userDisplayName || 'User');
const userEmail = computed(() => authStore.user?.email || '');
const userInitials = computed(() => {
  const name = userDisplayName.value;
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .slice(0, 2);
});
const isLoading = computed(() => authStore.isLoading);

// Check if current page is an add/edit page
const isAddEditPage = computed(() => {
  return [
    'AddResident',
    'EditResident',
    'AddUnit',
    'EditUnit',
    'AddInvoice',
    'InvoiceDetail',
    'AddCharge',
    'EditCharge',
    'AddVendor',
    'EditVendor',
  ].includes(route.name as string);
});

// Page title and description based on current route
const pageTitle = computed(() => {
  switch (route.name) {
    case 'Dashboard':
      return 'Dashboard';
    case 'Invoices':
      return 'Invoices';
    case 'AddInvoice':
      return 'Create Invoice';
    case 'InvoiceDetail':
      return 'View Invoice';
    case 'Charges':
      return 'Charges';
    case 'AddCharge':
      return 'Add New Charge';
    case 'EditCharge':
      return 'Edit Charge';
    case 'People':
      return 'People';
    case 'AddResident':
      return 'Add New Resident';
    case 'EditResident':
      return 'Edit Resident';
    case 'Units':
      return 'Units';
    case 'AddUnit':
      return 'Add New Unit';
    case 'EditUnit':
      return 'Edit Unit';
    case 'Payments':
      return 'Payments';
    case 'Expenses':
      return 'Expenses';
    case 'AddExpense':
      return 'Add New Expense';
    case 'EditExpense':
      return 'Edit Expense';
    case 'ExpenseDetail':
      return 'Expense Details';
    case 'Vendors':
      return 'Vendors';
    case 'AddVendor':
      return 'Add New Vendor';
    case 'EditVendor':
      return 'Edit Vendor';
    case 'Settings':
      return 'Settings';
    default:
      return 'Dashboard';
  }
});

const pageDescription = computed(() => {
  switch (route.name) {
    case 'Dashboard':
      return 'Overview of your HOA community management';
    case 'Invoices':
      return 'Manage community invoices and billing';
    case 'AddInvoice':
      return 'Create a new invoice for your community';
    case 'InvoiceDetail':
      return 'View and manage invoice details';
    case 'Charges':
      return 'Manage standard charges and fees';
    case 'AddCharge':
      return 'Create a new charge template';
    case 'EditCharge':
      return 'Update charge information';
    case 'People':
      return 'Manage residents and community members';
    case 'AddResident':
      return 'Add a new resident to your community';
    case 'EditResident':
      return 'Update resident information';
    case 'Units':
      return 'Manage units and properties';
    case 'AddUnit':
      return 'Add a new unit to your community';
    case 'EditUnit':
      return 'Update unit information';
    case 'Payments':
      return 'Track payments and financial transactions';
    case 'Expenses':
      return 'Manage vendor expenses and invoices';
    case 'AddExpense':
      return 'Add a new expense for vendor invoice tracking';
    case 'EditExpense':
      return 'Update expense information';
    case 'ExpenseDetail':
      return 'View expense details and documents';
    case 'Vendors':
      return 'Manage vendors and service providers';
    case 'AddVendor':
      return 'Add a new vendor to your community';
    case 'EditVendor':
      return 'Update vendor information';
    case 'Settings':
      return 'Configure your community settings';
    default:
      return 'Overview of your HOA community management';
  }
});

// Handle logout
const handleLogout = async () => {
  try {
    await authStore.signout();
    router.push('/login');
  } catch (error) {
    console.error('Logout failed:', error);
    router.push('/login');
  }
};
</script>

<style scoped>
/* Custom styles for the sidebar */
.router-link-active {
  @apply bg-primary text-white;
}

/* Smooth transitions */
.transition-colors {
  transition-property: color, background-color, border-color;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>
