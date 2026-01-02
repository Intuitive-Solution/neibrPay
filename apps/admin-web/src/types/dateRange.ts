/**
 * Date Range Types
 * Used throughout the application for date range filtering and selection
 */

/**
 * Represents a date range with start and end dates in YYYY-MM-DD format
 */
export interface DateRange {
  /** Start date in YYYY-MM-DD format */
  startDate: string;
  /** End date in YYYY-MM-DD format */
  endDate: string;
}

/**
 * Represents a preset date range option
 */
export interface DateRangePreset {
  /** Display label for the preset */
  label: string;
  /** Unique identifier for the preset */
  value: string;
  /** Function that returns the date range for this preset */
  getDates: () => { start: Date; end: Date };
}

/**
 * Represents the state of the date range picker
 */
export interface DateRangePickerState {
  /** Current active preset (null if custom range or no selection) */
  activePreset: string | null;
  /** Current date range selection */
  dateRange: DateRange;
  /** Whether a preset is currently selected */
  isPresetSelected: boolean;
  /** Whether a custom range is selected */
  isCustomRange: boolean;
  /** Number of days in the selected range */
  daysDifference: number;
}

/**
 * Options for date range formatting
 */
export interface DateFormatOptions {
  /** Format style for display (default: "long") */
  style?: 'short' | 'long' | 'full';
  /** Include time in format (default: false) */
  includeTime?: boolean;
  /** Locale string (default: "en-US") */
  locale?: string;
}

/**
 * Common date range presets
 */
export enum DateRangePresetValue {
  Last7Days = 'last7days',
  Last30Days = 'last30days',
  Last90Days = 'last90days',
  Last365Days = 'last365days',
  Custom = 'custom',
}

/**
 * API request parameters with date range filtering
 */
export interface DateRangeFilterParams {
  /** Start date for filtering (YYYY-MM-DD format) */
  startDate: string;
  /** End date for filtering (YYYY-MM-DD format) */
  endDate: string;
  /** Additional filter parameters */
  [key: string]: any;
}

/**
 * Response data with date range metadata
 */
export interface DateRangeResponse<T> {
  /** Array of filtered data */
  data: T[];
  /** Metadata about the response */
  meta: {
    startDate: string;
    endDate: string;
    totalDays: number;
    count: number;
  };
}

/**
 * Type guard to check if value is a valid DateRange
 */
export const isDateRange = (value: any): value is DateRange => {
  return (
    typeof value === 'object' &&
    value !== null &&
    typeof value.startDate === 'string' &&
    typeof value.endDate === 'string' &&
    /^\d{4}-\d{2}-\d{2}$/.test(value.startDate) &&
    /^\d{4}-\d{2}-\d{2}$/.test(value.endDate)
  );
};

/**
 * Type guard to check if string is valid YYYY-MM-DD date format
 */
export const isValidDateString = (dateString: string): boolean => {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(dateString)) {
    return false;
  }
  const date = new Date(dateString);
  return date instanceof Date && !isNaN(date.getTime());
};

/**
 * Parse date string to Date object with validation
 */
export const parseDateString = (dateString: string): Date | null => {
  if (!isValidDateString(dateString)) {
    return null;
  }
  return new Date(dateString);
};
