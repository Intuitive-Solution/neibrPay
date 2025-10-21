<template>
  <div class="flex h-screen bg-neutral-50">
    <!-- Mobile Backdrop -->
    <div
      v-if="isMobileMenuOpen"
      class="fixed inset-0 bg-black bg-opacity-50 z-40 md:hidden"
      @click="closeMobileMenu"
    ></div>

    <!-- Sidebar -->
    <div
      @mouseenter="handleSidebarHover(true)"
      @mouseleave="handleSidebarHover(false)"
      :class="[
        'bg-white shadow-lg flex flex-col transition-all duration-300 ease-in-out',
        // Desktop responsive behavior
        'hidden md:flex',
        // Width logic: collapsed shows icons only (w-16), expanded shows full width (w-64)
        isExpanded ? 'w-64' : 'w-16',
        // Position: fixed for desktop to overlap content, mobile drawer
        'fixed inset-y-0 left-0',
        // Z-index: higher when expanded to overlap content
        isExpanded ? 'z-50' : 'z-40',
        isMobileMenuOpen
          ? 'translate-x-0'
          : '-translate-x-full md:translate-x-0',
      ]"
    >
      <!-- Community Header -->
      <div :class="isExpanded ? 'p-6' : 'p-4'">
        <div v-if="isExpanded" class="flex flex-col">
          <div class="flex items-center justify-between">
            <NeibrPayLogo />
          </div>

          <!-- Community Name -->
          <div class="mt-3">
            <p class="text-sm text-gray-600">{{ communityName }}</p>
          </div>
        </div>

        <div v-else class="flex flex-col items-center">
          <!-- Logo Icon Only -->
          <NeibrPayLogo icon-only size="md" />
        </div>
      </div>

      <!-- Navigation Links -->
      <nav
        :class="[
          'flex-1 overflow-y-auto',
          isExpanded ? 'py-6 px-4' : 'py-4 px-2',
        ]"
      >
        <ul class="space-y-1">
          <li>
            <router-link
              to="/"
              :class="getNavLinkClass('Dashboard')"
              :title="!isExpanded ? 'Dashboard' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                />
              </svg>
              <span v-if="isExpanded">Dashboard</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/invoices"
              :class="getNavLinkClass('Invoices')"
              :title="!isExpanded ? 'Invoices' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">Invoices</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/charges"
              :class="getNavLinkClass(['Charges', 'AddCharge', 'EditCharge'])"
              :title="!isExpanded ? 'Charges' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">Charges</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/people"
              :class="getNavLinkClass('People')"
              :title="!isExpanded ? 'People' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">People</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/units"
              :class="getNavLinkClass(['Units', 'AddUnit', 'EditUnit'])"
              :title="!isExpanded ? 'Units' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">Units</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/payments"
              :class="getNavLinkClass('Payments')"
              :title="!isExpanded ? 'Payments' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">Payments</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/expenses"
              :class="
                getNavLinkClass([
                  'Expenses',
                  'AddExpense',
                  'EditExpense',
                  'ExpenseDetail',
                ])
              "
              :title="!isExpanded ? 'Expenses' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">Expenses</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/vendors"
              :class="getNavLinkClass('Vendors')"
              :title="!isExpanded ? 'Vendors' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">Vendors</span>
            </router-link>
          </li>

          <li>
            <router-link
              to="/settings"
              :class="getNavLinkClass('Settings')"
              :title="!isExpanded ? 'Settings' : ''"
            >
              <svg
                :class="['w-5 h-5', isExpanded ? 'mr-3' : '']"
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
              <span v-if="isExpanded">Settings</span>
            </router-link>
          </li>
        </ul>
      </nav>

      <!-- User Info & Logout -->
      <div :class="['border-t border-gray-200', isExpanded ? 'p-4' : 'p-3']">
        <div v-if="isExpanded" class="flex items-center space-x-3 mb-4">
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
            'w-full',
          ]"
          :title="!isExpanded ? 'Sign Out' : ''"
        >
          <svg
            v-if="isLoading"
            :class="[
              'animate-spin h-4 w-4 text-gray-500',
              isExpanded ? '-ml-1 mr-2' : '',
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
            :class="['w-4 h-4', isExpanded ? 'mr-2' : '']"
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
          <span v-if="isExpanded">{{
            isLoading ? 'Signing out...' : 'Sign Out'
          }}</span>
        </button>
      </div>
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col overflow-hidden bg-neutral-50 md:ml-16">
      <!-- Mobile Top Bar -->
      <header
        class="md:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between"
      >
        <div class="flex items-center space-x-3">
          <!-- Hamburger Menu -->
          <button
            @click="toggleMobileMenu"
            class="p-2 rounded-lg hover:bg-gray-100"
          >
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
                d="M4 6h16M4 12h16M4 18h16"
              />
            </svg>
          </button>

          <!-- Page Title -->
          <h2 class="text-lg font-semibold text-gray-900">{{ pageTitle }}</h2>
        </div>

        <!-- Mobile Action Icons -->
        <div class="flex items-center space-x-2">
          <button class="p-2 rounded-lg hover:bg-gray-100">
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
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
              />
            </svg>
          </button>
          <button class="p-2 rounded-lg hover:bg-gray-100">
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
                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
              />
            </svg>
          </button>
          <button class="p-2 rounded-lg hover:bg-gray-100">
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
                d="M12 6v6m0 0v6m0-6h6m-6 0H6"
              />
            </svg>
          </button>
        </div>
      </header>

      <!-- Desktop Top Header -->
      <header
        v-if="!isAddEditPage"
        class="hidden md:block bg-white border-b border-gray-200 px-6 py-4"
      >
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-semibold text-gray-900">
              {{ pageTitle }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">{{ pageDescription }}</p>
          </div>

          <!-- Desktop Action Icons -->
          <div class="flex items-center space-x-3">
            <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
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
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                />
              </svg>
            </button>
            <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
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
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"
                />
              </svg>
            </button>

            <!-- Create Dropdown -->
            <div class="relative">
              <button
                @click="toggleCreateDropdown"
                class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
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
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"
                  />
                </svg>
              </button>

              <!-- Create Dropdown Menu -->
              <Transition
                enter-active-class="transition ease-out duration-100"
                enter-from-class="transform opacity-0 scale-95"
                enter-to-class="transform opacity-100 scale-100"
                leave-active-class="transition ease-in duration-75"
                leave-from-class="transform opacity-100 scale-100"
                leave-to-class="transform opacity-0 scale-95"
              >
                <div
                  v-if="isCreateDropdownOpen"
                  class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50"
                  @click="closeCreateDropdown"
                >
                  <router-link to="/units/add" class="dropdown-item">
                    <svg
                      class="w-5 h-5 text-primary mr-3"
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
                    Unit
                  </router-link>
                  <router-link to="/invoices/create" class="dropdown-item">
                    <svg
                      class="w-5 h-5 text-primary mr-3"
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
                    Invoice
                  </router-link>
                  <router-link to="/people/add" class="dropdown-item">
                    <svg
                      class="w-5 h-5 text-primary mr-3"
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
                    Resident
                  </router-link>
                  <router-link to="/expenses/add" class="dropdown-item">
                    <svg
                      class="w-5 h-5 text-primary mr-3"
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
                    Expense
                  </router-link>
                  <router-link to="/vendors/add" class="dropdown-item">
                    <svg
                      class="w-5 h-5 text-primary mr-3"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"
                      />
                    </svg>
                    Vendor
                  </router-link>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </header>

      <!-- Main Content -->
      <main class="flex-1 overflow-auto p-4 md:p-6">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import NeibrPayLogo from './NeibrPayLogo.vue';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Hover state for sidebar expansion
const isExpanded = ref(false);
const isMobileMenuOpen = ref(false);
const isCreateDropdownOpen = ref(false);

// Handle sidebar hover
const handleSidebarHover = (hovered: boolean) => {
  isExpanded.value = hovered;
};

// Toggle functions
const toggleMobileMenu = () => {
  isMobileMenuOpen.value = !isMobileMenuOpen.value;
};

const closeMobileMenu = () => {
  isMobileMenuOpen.value = false;
};

const toggleCreateDropdown = () => {
  isCreateDropdownOpen.value = !isCreateDropdownOpen.value;
};

const closeCreateDropdown = () => {
  isCreateDropdownOpen.value = false;
};

// Computed properties
const communityName = computed(() => authStore.tenantName || 'Community');
const userDisplayName = computed(() => authStore.userDisplayName || 'User');
const userEmail = computed(() => authStore.user?.email || '');
const userInitials = computed(() => {
  const name = userDisplayName.value;
  return name
    .split(' ')
    .map((n: string) => n[0])
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
    'AddExpense',
    'EditExpense',
  ].includes(route.name as string);
});

// Page title and description based on current route
const pageTitle = computed(() => {
  switch (route.name) {
    case 'Dashboard':
      return 'Dashboard';
    case 'Invoices':
      return 'Invoices';
    case 'Charges':
      return 'Charges';
    case 'People':
      return 'People';
    case 'Units':
      return 'Units';
    case 'Payments':
      return 'Payments';
    case 'Expenses':
      return 'Expenses';
    case 'Vendors':
      return 'Vendors';
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
    case 'Charges':
      return 'Manage standard charges and fees';
    case 'People':
      return 'Manage residents and community members';
    case 'Units':
      return 'Manage units and properties';
    case 'Payments':
      return 'Track payments and financial transactions';
    case 'Expenses':
      return 'Manage vendor expenses and invoices';
    case 'Vendors':
      return 'Manage vendors and service providers';
    case 'Settings':
      return 'Configure your community settings';
    default:
      return 'Overview of your HOA community management';
  }
});

// Get nav link classes
const getNavLinkClass = (routeNames: string | string[]) => {
  const names = Array.isArray(routeNames) ? routeNames : [routeNames];
  const isActive = names.includes(route.name as string);

  return [
    'flex items-center py-2.5 text-sm font-medium rounded-lg transition-colors duration-200',
    isActive ? 'bg-primary text-white' : 'text-gray-700 hover:bg-gray-100',
    isExpanded.value ? 'px-4' : 'justify-center px-2',
  ];
};

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
.dropdown-item {
  @apply flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors duration-150;
}
</style>
