# Date Range Picker Component

A beautiful, modern date range picker component for Vue 3 with preset options and custom date selection.

## Features

✨ **Preset Shortcuts**

- Last 7 days
- Last 30 days
- Last 90 days
- Last 365 days
- Custom range

🎨 **Modern UI Design**

- Clean, professional styling with Tailwind CSS
- Responsive grid layout (mobile, tablet, desktop)
- Visual feedback for selected presets
- Days count indicator
- Disabled state for preset selections

⚙️ **Easy Integration**

- Vue 3 Composition API with TypeScript
- v-model support for two-way binding
- Composable hook for standalone logic
- Built-in date formatting and calculations

## Installation & Usage

### Option 1: Direct Component Usage

#### In your Vue component:

```vue
<template>
  <div>
    <DateRangePicker v-model="dateRange" />

    <div v-if="dateRange.startDate && dateRange.endDate">
      <p>From {{ dateRange.startDate }} to {{ dateRange.endDate }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

interface DateRange {
  startDate: string;
  endDate: string;
}

const dateRange = ref<DateRange>({
  startDate: '',
  endDate: '',
});
</script>
```

### Option 2: Using the Composable

For more granular control, use the `useDateRange` composable:

```vue
<template>
  <div>
    <button @click="setLast7Days">Last 7 Days</button>
    <button @click="setLast30Days">Last 30 Days</button>
    <button @click="clearDateRange">Clear</button>

    <p v-if="isDateRangeSet">
      From {{ formatDate(dateRange.value.startDate) }} to
      {{ formatDate(dateRange.value.endDate) }} ({{ daysDifference }} days)
    </p>
  </div>
</template>

<script setup lang="ts">
import { useDateRange } from '@/composables/useDateRange';

const {
  dateRange,
  daysDifference,
  formatDate,
  setLast7Days,
  setLast30Days,
  clearDateRange,
  isDateRangeSet,
} = useDateRange();
</script>
```

## Composable API Reference

### `useDateRange(initialStartDate?: string, initialEndDate?: string)`

Returns an object with the following properties and methods:

#### Properties

- **`dateRange`** - Reactive object with `startDate` and `endDate` (string in YYYY-MM-DD format)
- **`daysDifference`** - Computed property returning the number of days in the selected range
- **`isDateRangeSet`** - Computed property indicating if both dates are set

#### Methods

- **`setLastDays(days: number)`** - Set date range for last N days
- **`setLast7Days()`** - Set date range for last 7 days
- **`setLast30Days()`** - Set date range for last 30 days
- **`setLast90Days()`** - Set date range for last 90 days
- **`setLast365Days()`** - Set date range for last 365 days
- **`setDateRange(startDate: string, endDate: string)`** - Set custom date range
- **`clearDateRange()`** - Clear the date range
- **`formatDate(dateString: string)`** - Format date string to readable format (e.g., "Jan 1, 2024")
- **`formatDateToInput(date: Date)`** - Format Date object to YYYY-MM-DD format
- **`getDaysAgo(days: number)`** - Get a Date object N days in the past

## Component Props

### DateRangePicker

#### v-model

```typescript
interface DateRange {
  startDate: string; // YYYY-MM-DD format
  endDate: string; // YYYY-MM-DD format
}
```

**Type:** `DateRange`  
**Default:** `{ startDate: '', endDate: '' }`

**Example:**

```vue
<DateRangePicker v-model="dateRange" />
```

## Styling & Customization

The component uses Tailwind CSS classes from your `style.css`. Key classes:

- `.input-field` - Input field styling (already defined in your Tailwind config)
- `.card-modern` - Card styling if you want to wrap it
- Color classes use your primary color (`#00C27A`)

### Customizing Colors

Edit the component to use different colors:

```vue
<!-- Change active preset color -->
:class="[ 'px-4 py-2 rounded-lg font-medium text-sm transition-all
duration-200', activePreset === preset.value ? 'bg-blue-600 text-white
shadow-md'
<!-- Change colors here -->
: 'bg-neutral-200 text-neutral-700 hover:bg-neutral-300' ]"
```

### Customizing Presets

Modify the `presets` array in `DateRangePicker.vue`:

```typescript
const presets: DateRangePreset[] = [
  {
    label: 'Last 14 days',
    value: 'last14days',
    getDates: () => ({
      start: getDaysAgo(14),
      end: new Date(),
    }),
  },
  // ... other presets
];
```

## Integration Examples

### Dashboard with Date Filtering

```vue
<template>
  <div class="space-y-6">
    <DateRangePicker v-model="dateRange" />

    <div v-if="dateRange.startDate">
      <YourChartComponent
        :start-date="dateRange.startDate"
        :end-date="dateRange.endDate"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';
import YourChartComponent from '@/components/YourChartComponent.vue';

const dateRange = ref({
  startDate: '',
  endDate: '',
});
</script>
```

### Transaction Report View

```vue
<template>
  <div class="card-modern">
    <h2 class="text-xl font-semibold mb-4">Transaction Report</h2>

    <DateRangePicker v-model="dateRange" />

    <div v-if="isLoading" class="mt-4">
      <p>Loading transactions...</p>
    </div>

    <div v-else-if="dateRange.startDate" class="mt-6">
      <table class="w-full">
        <tbody>
          <tr v-for="tx in transactions" :key="tx.id">
            <td>{{ tx.date }}</td>
            <td>{{ tx.amount }}</td>
            <td>{{ tx.description }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

const dateRange = ref({ startDate: '', endDate: '' });
const isLoading = ref(false);
const transactions = ref([]);

// Watch for date range changes and fetch data
watch(
  () => dateRange.value,
  async newRange => {
    if (newRange.startDate && newRange.endDate) {
      isLoading.value = true;
      try {
        // Fetch transactions for date range
        const response = await fetch(
          `/api/transactions?start=${newRange.startDate}&end=${newRange.endDate}`
        );
        transactions.value = await response.json();
      } finally {
        isLoading.value = false;
      }
    }
  },
  { deep: true }
);
</script>
```

## Responsive Behavior

The component uses Tailwind's responsive grid:

- **Mobile:** Single column layout
- **Tablet (md):** 2 columns
- **Desktop (lg):** 5 columns for presets + 2 columns for dates + clear button

This ensures the component looks great on all screen sizes.

## Type Definitions

```typescript
interface DateRange {
  startDate: string; // YYYY-MM-DD
  endDate: string; // YYYY-MM-DD
}

interface DateRangePreset {
  label: string;
  value: string;
  getDates: () => { start: Date; end: Date };
}
```

## Files Included

- **DateRangePicker.vue** - Main component
- **useDateRange.ts** - Composable hook
- **DateRangePickerExample.vue** - Complete example with UI
- **DATE_RANGE_PICKER_README.md** - This documentation

## Browser Compatibility

Works with all modern browsers supporting:

- Vue 3
- ES6+
- CSS Grid and Flexbox
- HTML5 date inputs

## Performance Notes

- Lightweight component with minimal dependencies
- No external date libraries required
- Uses native JavaScript Date objects
- Efficiently computed properties for reactive updates

## Future Enhancements

Potential improvements:

- Date range validation (min/max dates)
- Locale support for date formatting
- Custom date picker UI instead of native input
- Keyboard navigation support
- Disabled date ranges
- Save/load preset configurations

## Questions or Issues?

Refer to the example component `DateRangePickerExample.vue` for a complete working implementation with all features demonstrated.
