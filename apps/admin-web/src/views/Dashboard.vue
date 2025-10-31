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
      <div class="card card-hover cursor-pointer" @click="navigateToUnits">
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
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ isLoading && !units ? '-' : activeUnitsCount }}
            </p>
          </div>
        </div>
      </div>

      <!-- Active Invoices -->
      <div class="card card-hover cursor-pointer" @click="navigateToInvoices">
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
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ isLoading && !invoices ? '-' : activeInvoicesCount }}
            </p>
            <p v-if="!isLoading" class="text-sm text-gray-500 mt-1">
              {{ formatCurrency(activeInvoicesAmount) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Total Collected Chart -->
      <div class="card card-hover cursor-pointer" @click="navigateToPayments">
        <div class="flex flex-col h-full">
          <div class="flex items-center mb-4">
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
            <div class="ml-4 flex-1">
              <h3 class="text-sm font-medium text-gray-600">
                Total Collected (6M)
              </h3>
              <p
                v-if="!isLoading"
                class="text-2xl font-bold text-gray-900 mt-1"
              >
                {{ formatCurrency(totalCollectedAmount) }}
              </p>
              <p v-else class="text-2xl font-bold text-gray-900 mt-1">-</p>
            </div>
          </div>
          <div v-if="isLoading && !payments" class="relative w-full h-32 mt-2">
            <!-- Loading grid lines -->
            <svg
              class="absolute inset-0 w-full h-full"
              preserveAspectRatio="none"
            >
              <line
                v-for="i in 5"
                :key="`loading-grid-${i}`"
                x1="40"
                :y1="i * 20 + '%'"
                x2="100%"
                :y2="i * 20 + '%'"
                stroke="#F3F4F6"
                stroke-width="1"
              />
            </svg>
            <div
              class="flex items-end justify-center gap-1.5 h-full px-4 ml-10"
            >
              <div
                v-for="i in 6"
                :key="i"
                class="w-10 bg-gray-200 rounded-t flex-1"
                :style="`height: ${20 + Math.random() * 40}%`"
              ></div>
            </div>
          </div>
          <div v-else class="relative w-full h-32 mt-2">
            <!-- SVG for grid lines and axes -->
            <svg
              class="absolute inset-0 w-full"
              style="height: calc(100% - 1rem)"
              preserveAspectRatio="none"
            >
              <!-- Very soft grid lines -->
              <g class="grid-lines">
                <line
                  v-for="i in 5"
                  :key="`grid-${i}`"
                  x1="40"
                  :y1="i * 20 + '%'"
                  x2="100%"
                  :y2="i * 20 + '%'"
                  stroke="#F3F4F6"
                  stroke-width="1"
                />
              </g>
              <!-- Y-axis line -->
              <line
                x1="40"
                y1="0"
                x2="40"
                y2="100%"
                stroke="#E5E7EB"
                stroke-width="1"
              />
              <!-- X-axis line - at the bottom of chart area -->
              <line
                x1="40"
                y1="100%"
                x2="100%"
                y2="100%"
                stroke="#E5E7EB"
                stroke-width="1"
              />
            </svg>

            <!-- Y-axis labels -->
            <div
              class="absolute left-0 top-0 w-10 flex flex-col justify-between pr-1"
              style="height: calc(100% - 1rem)"
            >
              <div
                v-for="(tick, index) in yAxisTicks"
                :key="`y-tick-${index}`"
                class="text-[10px] text-gray-500 text-right leading-tight"
              >
                {{ tick }}
              </div>
            </div>

            <!-- Chart area with bars -->
            <div
              class="flex items-end justify-center gap-1.5 px-4 ml-10"
              style="height: calc(100% - 1rem)"
            >
              <div
                v-for="(monthData, index) in monthlyPaymentData"
                :key="index"
                class="relative group flex-1 flex flex-col items-center"
                style="height: 100%"
              >
                <!-- Bar container - full height, bars aligned to bottom -->
                <div class="w-full flex items-end" style="height: 100%">
                  <!-- Bar - percentage height, starts from bottom (x-axis) -->
                  <div
                    class="w-full bg-gray-900 rounded-t transition-all duration-300 hover:bg-gray-700"
                    :style="`height: ${getBarHeight(monthData.Collections)}%`"
                  ></div>
                </div>
                <!-- Tooltip -->
                <div
                  class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-2 bg-gray-900 text-white text-xs rounded-lg shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 pointer-events-none whitespace-nowrap z-10"
                >
                  <div class="font-medium">{{ monthData.month }}</div>
                  <div class="mt-0.5">
                    {{ formatCurrency(monthData.Collections) }}
                  </div>
                  <!-- Tooltip arrow -->
                  <div
                    class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1 w-2 h-2 bg-gray-900 rotate-45"
                  ></div>
                </div>
              </div>
            </div>

            <!-- X-axis labels - positioned below the chart area -->
            <div
              class="flex justify-center gap-1.5 px-4 ml-10 mt-1"
              style="height: 1rem"
            >
              <div
                v-for="(monthData, index) in monthlyPaymentData"
                :key="`label-${index}`"
                class="flex-1 text-center"
              >
                <div
                  class="text-[10px] text-gray-500 leading-tight whitespace-nowrap"
                >
                  {{ monthData.month }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Residents -->
      <div class="card card-hover cursor-pointer" @click="navigateToPeople">
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
            <p class="text-2xl font-bold text-gray-900 mt-1">
              {{ isLoading && !residents ? '-' : activeResidentsCount }}
            </p>
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
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useUnits } from '../composables/useUnits';
import { useInvoices } from '../composables/useInvoices';
import { usePayments } from '../composables/usePayments';
import { useResidents } from '../composables/useResidents';
import type { Unit, InvoiceUnit, Payment, Resident } from '@neibrpay/models';

const router = useRouter();

// Query hooks
const { data: units, isLoading: unitsLoading } = useUnits(false);
const { data: invoices, isLoading: invoicesLoading } = useInvoices();
const { data: payments, isLoading: paymentsLoading } = usePayments();
const { data: residents, isLoading: residentsLoading } = useResidents(false);

// Currency formatting helper
const formatCurrency = (amount: number): string => {
  // Handle NaN, null, or undefined
  const safeAmount = typeof amount === 'number' && !isNaN(amount) ? amount : 0;
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(safeAmount);
};

// Format number for y-axis: "1.4k", "14k", "1.2M" (max 3-4 chars)
const formatCompactNumber = (value: number): string => {
  if (value === 0) return '0';
  const absValue = Math.abs(value);

  if (absValue >= 1000000) {
    const millions = absValue / 1000000;
    return millions % 1 === 0 ? `${millions}M` : `${millions.toFixed(1)}M`;
  }
  if (absValue >= 1000) {
    const thousands = absValue / 1000;
    return thousands % 1 === 0 ? `${thousands}k` : `${thousands.toFixed(1)}k`;
  }
  return absValue.toString();
};

// Computed statistics
const activeUnitsCount = computed(() => {
  if (!units.value) return 0;
  return units.value.filter(
    (unit: Unit) => unit.is_active === true && !unit.deleted_at
  ).length;
});

const activeInvoices = computed(() => {
  if (!invoices.value) return [];
  return invoices.value.filter(
    (invoice: InvoiceUnit) =>
      invoice.status !== 'paid' &&
      invoice.status !== 'cancelled' &&
      !invoice.deleted_at
  );
});

const activeInvoicesCount = computed(() => {
  return activeInvoices.value.length;
});

const activeInvoicesAmount = computed(() => {
  if (!activeInvoices.value || activeInvoices.value.length === 0) return 0;

  const total = activeInvoices.value.reduce(
    (sum: number, invoice: InvoiceUnit) => {
      // Get the total value - could be number, string, or null/undefined
      const invoiceTotal = invoice?.total;

      // Convert to number - handle runtime string values from API
      let amount = 0;
      if (invoiceTotal != null && invoiceTotal !== undefined) {
        // API might return as string, so convert if needed
        const numValue =
          typeof invoiceTotal === 'string'
            ? parseFloat(invoiceTotal)
            : invoiceTotal;
        amount =
          typeof numValue === 'number' && !isNaN(numValue) ? numValue : 0;
      }

      return sum + amount;
    },
    0
  );

  return total;
});

const activeResidentsCount = computed(() => {
  if (!residents.value) return 0;
  return residents.value.filter(
    (resident: Resident) => resident.is_active === true && !resident.deleted_at
  ).length;
});

// Process payments for last 6 months chart
const monthlyPaymentData = computed(() => {
  if (!payments.value || payments.value.length === 0) {
    // Return empty data for last 6 months
    const months: string[] = [];
    const now = new Date();
    for (let i = 5; i >= 0; i--) {
      const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
      months.push(date.toLocaleDateString('en-US', { month: 'short' }));
    }
    return months.map(month => ({
      month,
      Collections: 0,
    }));
  }

  // Get last 6 months
  const monthlyTotals: Record<string, number> = {};
  const now = new Date();

  // Initialize all months with 0
  for (let i = 5; i >= 0; i--) {
    const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const monthKey = date.toLocaleDateString('en-US', { month: 'short' });
    monthlyTotals[monthKey] = 0;
  }

  // Group payments by month
  payments.value.forEach((payment: Payment) => {
    const paymentDate = new Date(payment.payment_date);
    const monthKey = paymentDate.toLocaleDateString('en-US', {
      month: 'short',
    });
    const sixMonthsAgo = new Date(now.getFullYear(), now.getMonth() - 5, 1);

    // Only include payments from last 6 months
    if (paymentDate >= sixMonthsAgo) {
      if (monthlyTotals[monthKey] !== undefined) {
        // Handle amount as number or string
        const amount =
          typeof payment.amount === 'string'
            ? parseFloat(payment.amount) || 0
            : payment.amount || 0;
        monthlyTotals[monthKey] += amount;
      }
    }
  });

  // Convert to array format for chart
  const months: string[] = [];
  for (let i = 5; i >= 0; i--) {
    const date = new Date(now.getFullYear(), now.getMonth() - i, 1);
    months.push(date.toLocaleDateString('en-US', { month: 'short' }));
  }

  return months.map(month => ({
    month,
    Collections: monthlyTotals[month] || 0,
  }));
});

// Calculate total collected amount for the 6 months
const totalCollectedAmount = computed(() => {
  if (!monthlyPaymentData.value) return 0;
  return monthlyPaymentData.value.reduce(
    (sum: number, monthData: { month: string; Collections: number }) =>
      sum + (monthData.Collections || 0),
    0
  );
});

// Calculate Y-axis ticks for grid lines (formatted as compact numbers)
const yAxisTicks = computed(() => {
  if (!monthlyPaymentData.value || monthlyPaymentData.value.length === 0)
    return ['0', '0', '0', '0', '0'];

  const maxValue = Math.max(
    ...monthlyPaymentData.value.map(
      (m: { month: string; Collections: number }) => m.Collections || 0
    ),
    1
  );

  if (maxValue === 0) return ['0', '0', '0', '0', '0'];

  // Generate 5 ticks from 0 to max
  const ticks: string[] = [];
  for (let i = 4; i >= 0; i--) {
    const tickValue = (maxValue / 4) * i;
    ticks.push(formatCompactNumber(tickValue));
  }
  return ticks;
});

// Calculate bar height as percentage (0-100%)
const getBarHeight = (value: number): number => {
  if (!monthlyPaymentData.value || monthlyPaymentData.value.length === 0)
    return 0;
  const maxValue = Math.max(
    ...monthlyPaymentData.value.map(
      (m: { month: string; Collections: number }) => m.Collections || 0
    ),
    1 // Prevent division by zero
  );
  if (maxValue === 0) return 0;
  if (value === 0) return 0;
  // Return as percentage (0-100%) - allow 0 height for zero values
  return (value / maxValue) * 100;
};

// Navigation handlers
const navigateToUnits = () => {
  router.push('/units');
};

const navigateToInvoices = () => {
  router.push('/invoices');
};

const navigateToPayments = () => {
  router.push('/payments');
};

const navigateToPeople = () => {
  router.push('/people');
};

// Loading state
const isLoading = computed(() => {
  return (
    unitsLoading.value ||
    invoicesLoading.value ||
    paymentsLoading.value ||
    residentsLoading.value
  );
});
</script>
