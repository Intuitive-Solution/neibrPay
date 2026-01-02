# Integrate Date Range Picker into Transactions View

A step-by-step guide to integrate the DateRangePicker component into your existing Transactions.vue filters.

## 📍 Location

**File:** `apps/admin-web/src/views/Transactions.vue`  
**Section:** Filters Card (lines 126-244)

## 🎯 What Will Change

**Before:**

```vue
<!-- Separate Start Date and End Date inputs -->
<div>
  <label>Start Date</label>
  <input v-model="filters.start_date" type="date" class="input-field" />
</div>

<div>
  <label>End Date</label>
  <input v-model="filters.end_date" type="date" class="input-field" />
</div>
```

**After:**

```vue
<!-- Single DateRangePicker with presets -->
<DateRangePicker v-model="dateRange" />
```

## 📋 Step-by-Step Integration

### Step 1: Import the Component

Add to your `<script setup>` section at the top:

```typescript
import DateRangePicker from '@/components/DateRangePicker.vue';
```

### Step 2: Add Date Range State

Add this new ref to your script:

```typescript
const dateRange = ref({
  startDate: '',
  endDate: '',
});
```

### Step 3: Create a Watcher

Add this watcher to sync the DateRangePicker with your filters:

```typescript
// Watch for date range changes and update filters
watch(
  () => dateRange.value,
  newRange => {
    filters.value.start_date = newRange.startDate;
    filters.value.end_date = newRange.endDate;
    currentPage.value = 1;
  },
  { deep: true }
);
```

### Step 4: Update Template

Replace the Start Date and End Date input divs with:

```vue
<!-- OLD CODE TO REMOVE -->
<!-- 
<div>
  <label class="block text-sm font-medium text-gray-700 mb-1">
    Start Date
  </label>
  <input
    v-model="filters.start_date"
    type="date"
    class="input-field"
    @change="currentPage = 1"
  />
</div>

<div>
  <label class="block text-sm font-medium text-gray-700 mb-1">
    End Date
  </label>
  <input
    v-model="filters.end_date"
    type="date"
    class="input-field"
    @change="currentPage = 1"
  />
</div>
-->

<!-- NEW CODE TO ADD -->
<!-- Wrap DateRangePicker in a border-top for visual separation -->
<div class="col-span-full border-t border-gray-200 pt-6">
  <h3 class="text-sm font-medium text-gray-700 mb-4">Date Range</h3>
  <DateRangePicker v-model="dateRange" />
</div>

<!-- Optional: Show selected range info -->
<div
  v-if="dateRange.startDate && dateRange.endDate"
  class="col-span-full bg-blue-50 border border-blue-200 rounded-lg p-3"
>
  <p class="text-sm text-blue-900">
    <span class="font-semibold">Showing transactions:</span>
    {{ formatDateForDisplay(dateRange.startDate) }} to
    {{ formatDateForDisplay(dateRange.endDate) }}
    <span class="text-blue-700">({{ daysDuration }} days)</span>
  </p>
</div>
```

### Step 5: Add Display Helper Methods

Add these helper methods to your script:

```typescript
// Format date for display (e.g., "Jan 1, 2024")
const formatDateForDisplay = (dateString: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

// Calculate days duration (optional, for display)
const daysDuration = computed(() => {
  if (!dateRange.value.startDate || !dateRange.value.endDate) return 0;
  const start = new Date(dateRange.value.startDate);
  const end = new Date(dateRange.value.endDate);
  return (
    Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  );
});
```

### Step 6: Update Reset Filters

Modify your `resetFilters` method:

```typescript
// OLD
const resetFilters = () => {
  filters.value.start_date = '';
  filters.value.end_date = '';
  // ... other resets
};

// NEW - Add dateRange reset
const resetFilters = () => {
  filters.value.start_date = '';
  filters.value.end_date = '';
  dateRange.value = {
    startDate: '',
    endDate: '',
  };
  // ... other resets
};
```

## 🎨 Layout Options

### Option 1: Side-by-Side (Current)

Keep the DateRangePicker in the same grid as other filters:

```vue
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
  <!-- Bank Account, Status, Search, ... -->
  <DateRangePicker v-model="dateRange" />
</div>
```

### Option 2: Separated (Recommended)

Put DateRangePicker in its own section for emphasis:

```vue
<!-- Section 1: Quick Filters -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4">
  <!-- Bank Account, Status, Search -->
</div>

<!-- Section 2: Date Range with border -->
<div class="border-t border-gray-200 pt-6">
  <h3 class="text-sm font-medium text-gray-700 mb-4">Date Range</h3>
  <DateRangePicker v-model="dateRange" />
</div>
```

**Recommendation:** Option 2 looks cleaner and gives more space to the presets.

## ✅ Verification Checklist

After integration, verify:

- [ ] DateRangePicker component imports without errors
- [ ] Preset buttons appear and work correctly
- [ ] Clicking presets updates the dates
- [ ] Custom dates can be selected
- [ ] `dateRange.value` updates when dates change
- [ ] `filters.start_date` and `filters.end_date` get updated
- [ ] Clear button resets the selection
- [ ] No console errors in browser dev tools
- [ ] Transactions filter by selected date range
- [ ] Reset Filters button clears both date range and other filters

## 🐛 Troubleshooting

### Dates not appearing in filters

**Check:**

- Make sure the watcher is set up correctly
- Verify `filters.value.start_date` and `filters.value.end_date` are being set
- Check browser console for errors

### Component not showing

**Check:**

- Import statement is correct: `import DateRangePicker from '@/components/DateRangePicker.vue';`
- Component is registered in template: `<DateRangePicker v-model="dateRange" />`

### Styling looks off

**Check:**

- Ensure Tailwind CSS is working (other elements styled correctly)
- Check if `.input-field` class is available in your style.css

### Dates not filtering transactions

**Check:**

- API call is using the correct filter parameters
- `filters.start_date` and `filters.end_date` are in YYYY-MM-DD format
- Watcher is triggering when dates change

## 🎨 Customization

### Change Preset Labels

Edit `DateRangePicker.vue` presets array (line ~85):

```typescript
const presets = [
  {
    label: 'Last Week', // Change label
    value: 'last7days',
    getDates: () => ({
      start: getDaysAgo(7),
      end: new Date(),
    }),
  },
  // ... other presets
];
```

### Add Custom Preset

Add to presets array:

```typescript
{
  label: 'Last 2 Weeks',
  value: 'last14days',
  getDates: () => ({
    start: getDaysAgo(14),
    end: new Date(),
  }),
}
```

### Change Component Colors

Edit `DateRangePicker.vue` line ~15:

```vue
activePreset === preset.value ? 'bg-blue-600 text-white shadow-md' // Change
active color : 'bg-gray-200 text-gray-700 hover:bg-gray-300'
```

## 📊 Expected Behavior

### Before Selecting Date Range

```
[Last 7 days] [Last 30 days] [Last 90 days] [Last 365 days] [Custom range]
[   ] [   ]
(Empty state - no transactions shown)
```

### After Selecting a Preset (e.g., Last 7 days)

```
[Last 7 days] [Last 30 days] [Last 90 days] [Last 365 days] [Custom range]
            ↓ (highlighted)
Start Date: [2024-01-01]    (disabled, auto-filled)
End Date:   [2024-01-08]    (disabled, auto-filled)

✓ Showing transactions: Jan 1, 2024 to Jan 8, 2024 (8 days)
(Transactions table displays filtered data)
```

### After Custom Date Selection

```
[Last 7 days] [Last 30 days] [Last 90 days] [Last 365 days] [Custom range]
                                                           ↓ (highlighted)
Start Date: [2024-01-01]    (enabled, user entered)
End Date:   [2024-01-15]    (enabled, user entered)

✓ Showing transactions: Jan 1, 2024 to Jan 15, 2024 (15 days)
(Transactions table displays filtered data)
```

## 🔄 Data Flow Diagram

```
User clicks preset
    ↓
DateRangePicker calculates dates
    ↓
v-model updates dateRange ref
    ↓
Watcher detects change
    ↓
Updates filters.start_date and filters.end_date
    ↓
Your existing API call filters transactions
    ↓
Results displayed in table
```

## 📝 Example with Full Context

Here's a snippet of how it looks in context:

```vue
<template>
  <div class="space-y-6">
    <!-- Bank Accounts Cards -->
    <!-- ... -->

    <!-- Filters Card -->
    <div class="card-modern">
      <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters</h2>

      <!-- Quick Filters Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pb-6">
        <!-- Bank Account Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Bank Account
          </label>
          <select v-model="filters.bank_account_id" class="input-field">
            <option :value="null">All Accounts</option>
            <option
              v-for="account in bankAccounts"
              :key="account.id"
              :value="account.id"
            >
              {{ account.account_name }}
            </option>
          </select>
        </div>

        <!-- Status Filter -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Status
          </label>
          <select v-model="filters.pending" class="input-field">
            <option :value="null">All</option>
            <option :value="false">Posted</option>
            <option :value="true">Pending</option>
          </select>
        </div>

        <!-- Search -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Search
          </label>
          <input
            v-model="filters.search"
            type="text"
            class="input-field"
            placeholder="Transaction name..."
          />
        </div>
      </div>

      <!-- DateRangePicker Section (NEW) -->
      <div class="border-t border-gray-200 pt-6">
        <h3 class="text-sm font-medium text-gray-700 mb-4">Date Range</h3>
        <DateRangePicker v-model="dateRange" />
      </div>

      <!-- Selected Range Display (NEW) -->
      <div
        v-if="dateRange.startDate && dateRange.endDate"
        class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-3"
      >
        <p class="text-sm text-blue-900">
          <span class="font-semibold">Showing transactions:</span>
          {{ formatDateForDisplay(dateRange.startDate) }} to
          {{ formatDateForDisplay(dateRange.endDate) }}
          <span class="text-blue-700">({{ daysDuration }} days)</span>
        </p>
      </div>

      <!-- Filter Actions -->
      <div class="mt-4 flex gap-2">
        <button @click="resetFilters" class="btn-secondary text-sm">
          Reset Filters
        </button>
        <button @click="refreshTransactions" class="btn-secondary text-sm">
          Refresh
        </button>
      </div>
    </div>

    <!-- Transactions Table -->
    <!-- ... -->
  </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import DateRangePicker from '@/components/DateRangePicker.vue';

const filters = ref({
  bank_account_id: null as string | null,
  pending: null as boolean | null,
  search: '',
  start_date: '',
  end_date: '',
});

const dateRange = ref({
  startDate: '',
  endDate: '',
});

const daysDuration = computed(() => {
  if (!dateRange.value.startDate || !dateRange.value.endDate) return 0;
  const start = new Date(dateRange.value.startDate);
  const end = new Date(dateRange.value.endDate);
  return (
    Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24)) + 1
  );
});

const formatDateForDisplay = (dateString: string): string => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

watch(
  () => dateRange.value,
  newRange => {
    filters.value.start_date = newRange.startDate;
    filters.value.end_date = newRange.endDate;
  },
  { deep: true }
);

const resetFilters = () => {
  filters.value = {
    bank_account_id: null,
    pending: null,
    search: '',
    start_date: '',
    end_date: '',
  };
  dateRange.value = {
    startDate: '',
    endDate: '',
  };
};
</script>
```

## ⏱️ Time Estimate

- **Reading this guide:** 10 minutes
- **Making code changes:** 10-15 minutes
- **Testing integration:** 5 minutes
- **Total:** 25-30 minutes

## 🎉 Done!

Your Transactions view now has a beautiful, user-friendly date range picker with presets!

### What You Get

✅ 5 preset options (Last 7/30/90/365 days, Custom)  
✅ Better UX than separate date inputs  
✅ Day counter showing selected duration  
✅ Responsive design  
✅ No additional dependencies  
✅ Type-safe with TypeScript

---

**Need help?** Refer to:

- DateRangePicker component: `/apps/admin-web/src/components/DateRangePicker.vue`
- Example file: `/apps/admin-web/src/views/TransactionsWithDateRangePicker.vue`
- Full docs: `/DATE_RANGE_PICKER_QUICK_START.md`
