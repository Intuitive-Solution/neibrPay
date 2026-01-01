import { ref, computed } from 'vue';

interface DateRange {
  startDate: string;
  endDate: string;
}

/**
 * Composable for managing date range selection with preset options
 * @returns Date range state and methods
 */
export const useDateRange = (initialStartDate = '', initialEndDate = '') => {
  const dateRange = ref<DateRange>({
    startDate: initialStartDate,
    endDate: initialEndDate,
  });

  /**
   * Format date string to readable format
   */
  const formatDate = (dateString: string): string => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  };

  /**
   * Get the number of days in the selected range
   */
  const daysDifference = computed(() => {
    if (!dateRange.value.startDate || !dateRange.value.endDate) return 0;
    const start = new Date(dateRange.value.startDate);
    const end = new Date(dateRange.value.endDate);
    return (
      Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
    );
  });

  /**
   * Format date to YYYY-MM-DD format for input fields
   */
  const formatDateToInput = (date: Date): string => {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
  };

  /**
   * Get a date N days in the past
   */
  const getDaysAgo = (days: number): Date => {
    const date = new Date();
    date.setDate(date.getDate() - days);
    return date;
  };

  /**
   * Set date range for last N days
   */
  const setLastDays = (days: number) => {
    const endDate = new Date();
    const startDate = getDaysAgo(days);
    dateRange.value.startDate = formatDateToInput(startDate);
    dateRange.value.endDate = formatDateToInput(endDate);
  };

  /**
   * Set date range for last 7 days
   */
  const setLast7Days = () => setLastDays(7);

  /**
   * Set date range for last 30 days
   */
  const setLast30Days = () => setLastDays(30);

  /**
   * Set date range for last 90 days
   */
  const setLast90Days = () => setLastDays(90);

  /**
   * Set date range for last 365 days
   */
  const setLast365Days = () => setLastDays(365);

  /**
   * Clear the date range
   */
  const clearDateRange = () => {
    dateRange.value.startDate = '';
    dateRange.value.endDate = '';
  };

  /**
   * Set custom date range
   */
  const setDateRange = (startDate: string, endDate: string) => {
    dateRange.value.startDate = startDate;
    dateRange.value.endDate = endDate;
  };

  /**
   * Check if date range is set
   */
  const isDateRangeSet = computed(() => {
    return !!dateRange.value.startDate && !!dateRange.value.endDate;
  });

  return {
    dateRange,
    daysDifference,
    formatDate,
    formatDateToInput,
    getDaysAgo,
    setLastDays,
    setLast7Days,
    setLast30Days,
    setLast90Days,
    setLast365Days,
    clearDateRange,
    setDateRange,
    isDateRangeSet,
  };
};
