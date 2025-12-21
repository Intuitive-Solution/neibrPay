<template>
  <div class="overflow-x-auto">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center py-8">
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
        <span class="text-sm text-gray-600">Loading activity log...</span>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="px-6 py-8 text-center">
      <p class="text-sm text-red-600">{{ error }}</p>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="!auditLogs || auditLogs.length === 0"
      class="px-6 py-8 text-center"
    >
      <p class="text-sm text-gray-500">No activity recorded for this year</p>
    </div>

    <!-- Audit Log Table -->
    <table v-else class="w-full divide-y divide-gray-200">
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
            User
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Action
          </th>
          <th
            class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            Details
          </th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        <tr
          v-for="log in auditLogs"
          :key="log.id"
          class="hover:bg-gray-50 transition-colors"
        >
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ formatDate(log.created_at) }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
            {{ log.user?.name || 'Unknown' }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
              :class="getActionBadgeClass(log.action)"
            >
              {{ getActionDisplayName(log.action) }}
            </span>
          </td>
          <td class="px-6 py-4 text-sm text-gray-600">
            {{ log.description }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup lang="ts">
import { useBudgetAuditLogs } from '../composables/useBudget';
import type { BudgetAuditAction } from '@neibrpay/models';
import { getBudgetAuditActionDisplayName } from '@neibrpay/models';

interface Props {
  year: number;
}

const props = defineProps<Props>();

const { data: auditLogs, isLoading, error } = useBudgetAuditLogs(props.year);

const formatDate = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const getActionDisplayName = (action: BudgetAuditAction): string => {
  return getBudgetAuditActionDisplayName(action);
};

const getActionBadgeClass = (action: BudgetAuditAction): string => {
  const classes: Record<BudgetAuditAction, string> = {
    create_category: 'bg-green-100 text-green-800',
    update_category: 'bg-blue-100 text-blue-800',
    delete_category: 'bg-red-100 text-red-800',
    update_forecast: 'bg-yellow-100 text-yellow-800',
    copy_budget: 'bg-purple-100 text-purple-800',
  };
  return classes[action] || 'bg-gray-100 text-gray-800';
};
</script>
