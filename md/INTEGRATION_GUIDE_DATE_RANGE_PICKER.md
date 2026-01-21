# Date Range Picker Integration Guide

This guide shows you how to integrate the new Date Range Picker component into your existing NeibrPay views.

## 📁 New Files Created

```
apps/admin-web/src/
├── components/
│   ├── DateRangePicker.vue              ← Main component
│   ├── DateRangePickerExample.vue       ← Full working example
│   └── DATE_RANGE_PICKER_README.md      ← Detailed documentation
└── composables/
    └── useDateRange.ts                  ← Reusable composable hook
```

## 🚀 Quick Start

### Option 1: Use the Component Directly (Recommended for most cases)

```vue
<template>
  <div class="space-y-6">
    <!-- Your existing header -->
    <h1 class="text-2xl font-semibold">Transactions</h1>

    <!-- Add the Date Range Picker -->
    <div class="card-modern">
      <DateRangePicker v-model="dateRange" />
    </div>

    <!-- Your data table/chart using the date range -->
    <div v-if="dateRange.startDate && dateRange.endDate">
      <YourDataComponent
        :start-date="dateRange.startDate"
        :end-date="dateRange.endDate"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';
import YourDataComponent from '@/components/YourDataComponent.vue';

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

### Option 2: Use the Composable (For programmatic access)

```vue
<template>
  <div class="space-y-4">
    <div class="flex gap-2">
      <button @click="setLast7Days" class="btn-secondary">Last 7 Days</button>
      <button @click="setLast30Days" class="btn-secondary">Last 30 Days</button>
      <button @click="clearDateRange" class="btn-secondary">Clear</button>
    </div>

    <div v-if="isDateRangeSet">
      <p>
        Selected: {{ formatDate(dateRange.value.startDate) }} to
        {{ formatDate(dateRange.value.endDate) }}
      </p>
      <p>Total days: {{ daysDifference }}</p>
    </div>
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

## 📊 Integration Examples

### Example 1: Dashboard with Date Filtering

Perfect for displaying charts and statistics over a date range.

```vue
<template>
  <div class="max-w-7xl space-y-6">
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
          <h3 class="section-title-modern">Revenue Report</h3>
          <p class="section-subtitle-modern">Track revenue over time</p>
        </div>
      </div>

      <DateRangePicker v-model="dateRange" />
    </div>

    <!-- Revenue Chart -->
    <div v-if="dateRange.startDate && dateRange.endDate" class="card-modern">
      <h3 class="font-semibold text-lg mb-4">Revenue Trend</h3>
      <!-- Your chart component here -->
    </div>

    <!-- Summary Stats -->
    <div
      v-if="dateRange.startDate && dateRange.endDate"
      class="grid grid-cols-1 md:grid-cols-3 gap-4"
    >
      <div class="card-modern">
        <p class="text-sm text-gray-600">Total Revenue</p>
        <p class="text-3xl font-bold text-primary">$45,234</p>
      </div>
      <div class="card-modern">
        <p class="text-sm text-gray-600">Transactions</p>
        <p class="text-3xl font-bold text-primary">1,234</p>
      </div>
      <div class="card-modern">
        <p class="text-sm text-gray-600">Average</p>
        <p class="text-3xl font-bold text-primary">$36.74</p>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

const dateRange = ref({
  startDate: '',
  endDate: '',
});
</script>
```

### Example 2: Transactions Table with Filtering

Perfect for viewing transaction records filtered by date.

```vue
<template>
  <div class="space-y-6">
    <h1 class="text-2xl font-semibold">Transaction History</h1>

    <!-- Date Range Filter -->
    <div class="card-modern">
      <DateRangePicker v-model="dateRange" />
    </div>

    <!-- Transactions Table -->
    <div v-if="isLoading" class="text-center py-8">
      <p class="text-gray-600">Loading transactions...</p>
    </div>

    <div
      v-else-if="dateRange.startDate && dateRange.endDate"
      class="card-modern overflow-x-auto"
    >
      <table class="w-full">
        <thead>
          <tr class="border-b border-gray-200">
            <th class="text-left py-3 px-4 font-semibold">Date</th>
            <th class="text-left py-3 px-4 font-semibold">Description</th>
            <th class="text-left py-3 px-4 font-semibold">Amount</th>
            <th class="text-left py-3 px-4 font-semibold">Status</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="tx in filteredTransactions"
            :key="tx.id"
            class="border-b border-gray-100 hover:bg-gray-50"
          >
            <td class="py-3 px-4">{{ formatDate(tx.date) }}</td>
            <td class="py-3 px-4">{{ tx.description }}</td>
            <td class="py-3 px-4 font-semibold">
              {{ formatCurrency(tx.amount) }}
            </td>
            <td class="py-3 px-4">
              <span :class="getStatusBadgeClass(tx.status)">
                {{ tx.status }}
              </span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="text-center py-12 text-gray-500">
      <p>Select a date range to view transactions</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

const dateRange = ref({ startDate: '', endDate: '' });
const isLoading = ref(false);
const transactions = ref([]);

// Watch for date changes and fetch data
import { watch } from 'vue';
watch(
  () => dateRange.value,
  async newRange => {
    if (newRange.startDate && newRange.endDate) {
      isLoading.value = true;
      try {
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

const filteredTransactions = computed(() => {
  return transactions.value.filter(tx => {
    const txDate = new Date(tx.date);
    const startDate = new Date(dateRange.value.startDate);
    const endDate = new Date(dateRange.value.endDate);
    return txDate >= startDate && txDate <= endDate;
  });
});

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const formatCurrency = (amount: number) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
  }).format(amount);
};

const getStatusBadgeClass = (status: string) => {
  const classes = {
    completed: 'badge-paid',
    pending: 'badge-draft',
    failed: 'badge-overdue',
  };
  return `badge ${classes[status] || 'badge-draft'}`;
};
</script>
```

### Example 3: Reports with Export

Perfect for generating reports filtered by date range.

```vue
<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold">Monthly Report</h1>
      <button
        v-if="dateRange.startDate && dateRange.endDate"
        @click="exportReport"
        class="btn-primary"
      >
        Export as PDF
      </button>
    </div>

    <!-- Date Range Filter -->
    <div class="card-modern">
      <DateRangePicker v-model="dateRange" />
    </div>

    <!-- Report Content -->
    <div
      v-if="dateRange.startDate && dateRange.endDate"
      class="card-modern space-y-6"
    >
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-sm text-gray-600">Report Period</p>
          <p class="text-lg font-semibold">
            {{ formatDate(dateRange.startDate) }} —
            {{ formatDate(dateRange.endDate) }}
          </p>
        </div>
        <div>
          <p class="text-sm text-gray-600">Duration</p>
          <p class="text-lg font-semibold">{{ daysDuration }} days</p>
        </div>
      </div>

      <!-- Your report content -->
      <div class="border-t border-gray-200 pt-6">
        <!-- Report details here -->
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

const dateRange = ref({ startDate: '', endDate: '' });

const daysDuration = computed(() => {
  if (!dateRange.value.startDate || !dateRange.value.endDate) return 0;
  const start = new Date(dateRange.value.startDate);
  const end = new Date(dateRange.value.endDate);
  return (
    Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  );
});

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const exportReport = async () => {
  // Your export logic here
  console.log('Exporting report for', dateRange.value);
};
</script>
```

## 🔧 Customization Options

### Change Preset Values

Edit `DateRangePicker.vue` and modify the `presets` array:

```typescript
const presets: DateRangePreset[] = [
  {
    label: 'Last 14 days', // Change label
    value: 'last14days', // Change value
    getDates: () => ({
      start: getDaysAgo(14), // Change days
      end: new Date(),
    }),
  },
  // Add more presets as needed
];
```

### Customize Colors

Modify the active preset color (line 19 in `DateRangePicker.vue`):

```vue
<!-- Change from primary green to your preferred color -->
? 'bg-blue-600 text-white shadow-md' : 'bg-neutral-200 text-neutral-700
hover:bg-neutral-300'
```

### Adjust Responsive Breakpoints

Modify the grid classes in `DateRangePicker.vue` (line 2):

```vue
<!-- Current: 1 col mobile, 2 cols tablet, 5 cols desktop -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-2"></div>
```

## 📱 Responsive Design

The component automatically adapts to screen sizes:

| Breakpoint   | Layout                                 |
| ------------ | -------------------------------------- |
| Mobile       | Single column, inputs stack vertically |
| Tablet (md)  | 2 columns, split presets and inputs    |
| Desktop (lg) | 5 columns for presets, 2 for dates     |

## 🎨 Styling

The component uses your existing Tailwind CSS classes:

- `.input-field` - Already defined in `style.css`
- `.card-modern` - Optional wrapper for the component
- Primary color: `#00C27A` (Bonsai Green)

No additional CSS configuration needed!

## ✅ Testing

To test the component, you can:

1. **Use the Example Component:**

   ```vue
   <DateRangePickerExample />
   ```

2. **Add to any view:**

   ```vue
   <script setup lang="ts">
   import DateRangePicker from '@/components/DateRangePicker.vue';
   const dateRange = ref({ startDate: '', endDate: '' });
   </script>

   <template>
     <DateRangePicker v-model="dateRange" />
   </template>
   ```

3. **Check console for date values:**
   ```typescript
   watch(
     () => dateRange.value,
     newVal => {
       console.log('Date range changed:', newVal);
     },
     { deep: true }
   );
   ```

## 📚 API Reference

### DateRangePicker Component

**Props:**

- `modelValue`: Object with `startDate` and `endDate` strings (YYYY-MM-DD format)

**Emits:**

- `update:modelValue`: Emits updated date range

### useDateRange Composable

**Methods:**

- `setLast7Days()` - Set last 7 days
- `setLast30Days()` - Set last 30 days
- `setLast90Days()` - Set last 90 days
- `setLast365Days()` - Set last 365 days
- `setLastDays(days)` - Set custom number of days
- `setDateRange(start, end)` - Set specific dates
- `clearDateRange()` - Clear selection
- `formatDate(dateString)` - Format date for display
- `formatDateToInput(date)` - Format for input field

**Properties:**

- `dateRange` - Reactive object with dates
- `daysDifference` - Computed days count
- `isDateRangeSet` - Computed boolean

## 🐛 Troubleshooting

### Component not showing

Make sure you're importing it correctly:

```typescript
import DateRangePicker from '@/components/DateRangePicker.vue';
```

### Dates not updating in parent

Check that you're using `v-model` with proper object binding:

```vue
<DateRangePicker v-model="dateRange" />
<!-- Not correct -->
<!-- <DateRangePicker :date-range="dateRange" /> -->
```

### Styling issues

Ensure Tailwind CSS is properly configured and the `.input-field` class is available in your global `style.css`.

## 🎯 Next Steps

1. **Copy one of the examples above** and adapt it to your view
2. **Import DateRangePicker** in your component
3. **Test with sample data** to ensure filtering works
4. **Customize colors/presets** if needed
5. **Connect to your API** to fetch filtered data

For more details, see `DATE_RANGE_PICKER_README.md` in the components folder.
