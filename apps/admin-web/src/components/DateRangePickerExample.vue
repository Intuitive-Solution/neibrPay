<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="mb-6">
      <h2 class="text-2xl font-semibold text-gray-900">
        Date Range Filter Example
      </h2>
      <p class="text-gray-600 mt-1">
        Select a preset or create a custom date range
      </p>
    </div>

    <!-- Date Range Picker -->
    <div class="card-modern">
      <div class="card-header-modern">
        <div class="card-icon">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
          >
            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
            <line x1="16" y1="2" x2="16" y2="6"></line>
            <line x1="8" y1="2" x2="8" y2="6"></line>
            <line x1="3" y1="10" x2="21" y2="10"></line>
          </svg>
        </div>
        <div>
          <h3 class="section-title-modern">Date Range</h3>
          <p class="section-subtitle-modern">Filter by time period</p>
        </div>
      </div>

      <DateRangePicker v-model="dateRange" />
    </div>

    <!-- Display Selected Range -->
    <div
      v-if="dateRange.startDate && dateRange.endDate"
      class="card-modern bg-blue-50 border-blue-200"
    >
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700">Start Date:</span>
          <span class="text-sm font-semibold text-blue-900">{{
            formatDateDisplay(dateRange.startDate)
          }}</span>
        </div>
        <div class="flex items-center justify-between">
          <span class="text-sm font-medium text-gray-700">End Date:</span>
          <span class="text-sm font-semibold text-blue-900">{{
            formatDateDisplay(dateRange.endDate)
          }}</span>
        </div>
        <div class="pt-2 border-t border-blue-200">
          <span class="text-xs text-blue-600"
            >Days selected: {{ dayCount }}</span
          >
        </div>
      </div>
    </div>

    <!-- Usage Instructions -->
    <div class="card-modern bg-gray-50">
      <h3 class="font-semibold text-gray-900 mb-4">How to Use</h3>
      <ul class="space-y-2 text-sm text-gray-700">
        <li class="flex items-start gap-3">
          <span class="text-primary font-bold flex-shrink-0">1.</span>
          <span
            >Click on any preset button (Last 7 days, Last 30 days, etc.) to
            quickly set a date range</span
          >
        </li>
        <li class="flex items-start gap-3">
          <span class="text-primary font-bold flex-shrink-0">2.</span>
          <span
            >For custom dates, click "Custom range" and manually select start
            and end dates</span
          >
        </li>
        <li class="flex items-start gap-3">
          <span class="text-primary font-bold flex-shrink-0">3.</span>
          <span
            >The selected range is immediately available in the component's
            model value</span
          >
        </li>
        <li class="flex items-start gap-3">
          <span class="text-primary font-bold flex-shrink-0">4.</span>
          <span>Use the Clear button to reset custom date selections</span>
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import DateRangePicker from './DateRangePicker.vue';

interface DateRange {
  startDate: string;
  endDate: string;
}

const dateRange = ref<DateRange>({
  startDate: '',
  endDate: '',
});

const formatDateDisplay = (dateString: string): string => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const dayCount = computed(() => {
  if (!dateRange.value.startDate || !dateRange.value.endDate) return 0;
  const start = new Date(dateRange.value.startDate);
  const end = new Date(dateRange.value.endDate);
  return (
    Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  );
});
</script>
