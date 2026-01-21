# Quick Integration Guide - Transactions View

Replace the separate date inputs with DateRangePicker in 6 steps.

## 📂 File to Edit

`apps/admin-web/src/views/Transactions.vue`

## 🚀 Quick Steps (10 minutes)

### 1. Add Import (Top of `<script setup>`)

```typescript
import DateRangePicker from '@/components/DateRangePicker.vue';
```

### 2. Add State

```typescript
const dateRange = ref({
  startDate: '',
  endDate: '',
});
```

### 3. Add Watcher

```typescript
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

### 4. Replace Template (Lines 154-178)

**DELETE:** The entire Start Date and End Date input divs

**REPLACE WITH:**

```vue
<!-- Date Range Section -->
<div class="col-span-full border-t border-gray-200 pt-6">
  <h3 class="text-sm font-medium text-gray-700 mb-4">Date Range</h3>
  <DateRangePicker v-model="dateRange" />
</div>

<!-- Optional: Display selected range -->
<div
  v-if="dateRange.startDate && dateRange.endDate"
  class="col-span-full bg-blue-50 border border-blue-200 rounded-lg p-3"
>
  <p class="text-sm text-blue-900">
    Showing: {{ formatDateDisplay(dateRange.startDate) }} to
    {{ formatDateDisplay(dateRange.endDate) }}
  </p>
</div>
```

### 5. Add Helper Method

```typescript
const formatDateDisplay = (dateString: string): string => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};
```

### 6. Update Reset Function

```typescript
const resetFilters = () => {
  filters.value = {
    bank_account_id: null,
    pending: null,
    search: '',
    start_date: '',
    end_date: '',
  };
  dateRange.value = { startDate: '', endDate: '' };
  currentPage.value = 1;
};
```

## ✅ Done!

Your Transactions view now has a beautiful date range picker! 🎉

## 📊 Before & After

### BEFORE

```
Start Date: [____]  End Date: [____]
(Two separate inputs, users have to type manually)
```

### AFTER

```
[Last 7] [Last 30] [Last 90] [Last 365] [Custom]
Start Date: [2024-01-01]  End Date: [2024-01-08]  [Clear]
Showing: Jan 1, 2024 to Jan 8, 2024 (8 days)
(Much better UX with presets and visual feedback!)
```

## 🔗 Full Guide

For detailed instructions and customization:  
→ `INTEGRATE_DATE_RANGE_PICKER_TRANSACTIONS.md`

## 🎯 Verify It Works

After integration:

- ✅ Click "Last 7 days" - dates should update
- ✅ Click "Last 30 days" - dates should update
- ✅ Enter custom dates - should work
- ✅ Click "Clear" - should reset
- ✅ Transactions should filter by date

## 🐛 Issues?

1. **Component not showing?** - Check import statement
2. **Dates not updating?** - Check watcher is set up
3. **Filter not working?** - Check API is using start_date/end_date
4. **Styling looks wrong?** - Check Tailwind CSS is enabled

---

**That's it! You're done!** 🎉
