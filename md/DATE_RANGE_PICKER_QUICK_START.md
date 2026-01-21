# 🚀 Date Range Picker - Quick Start Guide

A modern, fully-typed date range picker component with preset shortcuts and custom date selection for your NeibrPay admin dashboard.

## ✨ Features

✅ **5 Preset Options**

- Last 7 days
- Last 30 days
- Last 90 days
- Last 365 days
- Custom range

✅ **Beautiful UI**

- Modern design matching your Bonsai color scheme
- Responsive layout (mobile, tablet, desktop)
- Visual feedback on selection
- Day counter

✅ **TypeScript Support**

- Full type safety
- Type definitions included
- Composable hooks

✅ **Zero Dependencies**

- No date libraries needed
- Built-in native JavaScript dates
- Works with your existing Tailwind setup

## 📦 What's Included

```
Created:
├── DateRangePicker.vue              (Main component)
├── DateRangePickerExample.vue       (Full working example)
├── useDateRange.ts                  (Composable hook)
├── types/dateRange.ts               (TypeScript types)
├── DATE_RANGE_PICKER_README.md      (Full documentation)
└── INTEGRATION_GUIDE_DATE_RANGE_PICKER.md (Integration examples)
```

## 🎯 Basic Usage (30 seconds)

### Step 1: Import

```typescript
import DateRangePicker from '@/components/DateRangePicker.vue';
```

### Step 2: Add to template

```vue
<template>
  <DateRangePicker v-model="dateRange" />
</template>
```

### Step 3: Define state

```typescript
<script setup lang="ts">
import { ref } from 'vue';

const dateRange = ref({
  startDate: '',
  endDate: '',
});
</script>
```

**That's it!** Your date picker is ready to use.

## 🎨 Component Preview

```
┌─────────────────────────────────────────────────────────────┐
│ [Last 7 days] [Last 30 days] [Last 90 days] [Last 365 days] [Custom range] │
├─────────────────────────────────────────────────────────────┤
│ Start Date           │  End Date                             │
│ [2024-01-01]        │  [2024-01-08]                         │
├─────────────────────────────────────────────────────────────┤
│ ℹ️ Selected Range: Jan 1, 2024 to Jan 8, 2024 (8 days)      │
└─────────────────────────────────────────────────────────────┘
```

## 💡 Common Use Cases

### 1. Dashboard with Charts

```vue
<template>
  <div class="space-y-6">
    <DateRangePicker v-model="dateRange" />

    <RevenueChart
      v-if="dateRange.startDate"
      :start-date="dateRange.startDate"
      :end-date="dateRange.endDate"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

const dateRange = ref({ startDate: '', endDate: '' });
</script>
```

### 2. Filtered Data Table

```vue
<template>
  <div class="space-y-6">
    <DateRangePicker v-model="dateRange" />

    <table v-if="dateRange.startDate">
      <tr v-for="item in filteredData">
        <td>{{ item.date }}</td>
        <td>{{ item.amount }}</td>
      </tr>
    </table>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

const dateRange = ref({ startDate: '', endDate: '' });
const allData = ref([]);

// Fetch data when date range changes
watch(
  () => dateRange.value,
  async newRange => {
    if (newRange.startDate && newRange.endDate) {
      const response = await fetch(
        `/api/data?start=${newRange.startDate}&end=${newRange.endDate}`
      );
      allData.value = await response.json();
    }
  },
  { deep: true }
);
</script>
```

### 3. Using the Composable Directly

```vue
<template>
  <div class="space-y-4">
    <div class="flex gap-2">
      <button @click="setLast7Days">📅 Last 7 Days</button>
      <button @click="setLast30Days">📅 Last 30 Days</button>
      <button @click="clearDateRange">❌ Clear</button>
    </div>

    <p v-if="isDateRangeSet">Selected: {{ daysDifference }} days</p>
  </div>
</template>

<script setup lang="ts">
import { useDateRange } from '@/composables/useDateRange';

const {
  setLast7Days,
  setLast30Days,
  clearDateRange,
  daysDifference,
  isDateRangeSet,
} = useDateRange();
</script>
```

## 🔧 Customization

### Change Preset Labels

Edit `DateRangePicker.vue` line ~85:

```typescript
const presets: DateRangePreset[] = [
  {
    label: 'Previous Week', // ← Change this
    value: 'last7days',
    getDates: () => ({
      start: getDaysAgo(7),
      end: new Date(),
    }),
  },
  // ... more presets
];
```

### Add Custom Preset

Add to the `presets` array:

```typescript
{
  label: 'Last 6 months',
  value: 'last6months',
  getDates: () => ({
    start: getDaysAgo(180),
    end: new Date(),
  }),
}
```

### Change Colors

Edit `DateRangePicker.vue` line ~15 (active preset button):

```vue
activePreset === preset.value ? 'bg-blue-600 text-white shadow-md' // ←
Customize color here : 'bg-neutral-200 text-neutral-700 hover:bg-neutral-300'
```

## 📊 API Reference

### Component Props

```typescript
v-model: {
  startDate: string;  // YYYY-MM-DD format
  endDate: string;    // YYYY-MM-DD format
}
```

### Composable Methods

```typescript
const {
  dateRange,              // Reactive object with dates
  daysDifference,         // Number of days selected
  isDateRangeSet,         // Boolean - is range selected?
  formatDate(str),        // Format date for display
  setLast7Days(),         // Set last 7 days
  setLast30Days(),        // Set last 30 days
  setLast90Days(),        // Set last 90 days
  setLast365Days(),       // Set last 365 days
  setLastDays(n),         // Set custom days
  setDateRange(s, e),     // Set specific dates
  clearDateRange(),       // Clear selection
} = useDateRange();
```

## 🎯 Integration Checklist

- [ ] Copy component imports to your view
- [ ] Add `<DateRangePicker v-model="dateRange" />` to template
- [ ] Define `const dateRange = ref({ startDate: '', endDate: '' })`
- [ ] Watch `dateRange` to fetch/filter data
- [ ] Test with your API endpoints
- [ ] Customize colors if needed
- [ ] Deploy! 🚀

## 📖 Full Documentation

For comprehensive guides and examples, see:

- **INTEGRATION_GUIDE_DATE_RANGE_PICKER.md** - Detailed integration examples
- **DATE_RANGE_PICKER_README.md** - Complete API documentation

## 🧪 Testing

View the example component:

```vue
<DateRangePickerExample />
```

Or check the console for reactive updates:

```typescript
watch(
  () => dateRange.value,
  newVal => {
    console.log('Date range:', newVal);
  }
);
```

## 🎨 Styling

Uses your existing Tailwind setup:

- ✅ `.input-field` class already configured
- ✅ Primary color: `#00C27A` (Bonsai Green)
- ✅ Responsive grid (mobile, tablet, desktop)
- ✅ No additional CSS needed

## ⚡ Performance

- **Lightweight** - ~2KB minified
- **No dependencies** - Uses native Date API
- **Efficient** - Computed properties for reactivity
- **Mobile-friendly** - Responsive design
- **Accessible** - Standard HTML date inputs

## 🐛 Troubleshooting

**Component not showing?**

```typescript
// ✅ Correct import
import DateRangePicker from '@/components/DateRangePicker.vue';

// ✅ Correct usage
<DateRangePicker v-model="dateRange" />
```

**Dates not updating in parent?**

```typescript
// ✅ Use object binding with v-model
<DateRangePicker v-model="dateRange" />

// ❌ Don't use :date-range prop
```

**Styling not showing?**

- Ensure Tailwind CSS is enabled
- Check that `.input-field` exists in `style.css`
- Try clearing browser cache

## 📞 Support

If you encounter issues:

1. Check the **INTEGRATION_GUIDE_DATE_RANGE_PICKER.md** for examples
2. Review **DATE_RANGE_PICKER_README.md** for API details
3. Look at **DateRangePickerExample.vue** for working code
4. Check **types/dateRange.ts** for type definitions

## 🎉 You're All Set!

Start using the date range picker in your views. Pick a preset or create custom ranges - your data filtering just got easier!

**Questions?** Refer to the documentation files or the example component.

---

**Happy coding! 🚀**
