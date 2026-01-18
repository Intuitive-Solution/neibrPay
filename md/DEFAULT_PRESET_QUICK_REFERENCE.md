# Quick Reference - Default Preset

## 🎯 What Changed

The DateRangePicker now defaults to **"Last 90 days"** automatically.

## 📝 Implementation

**File:** `apps/admin-web/src/components/DateRangePicker.vue`

```typescript
// Initialize with "Last 90 days" as default
onMounted(() => {
  if (!props.modelValue.startDate && !props.modelValue.endDate) {
    selectPreset('last90days');
  }
});
```

## ✨ How It Works

1. Component mounts
2. Checks if dateRange is empty
3. Automatically selects "Last 90 days"
4. Sets dates and highlights button
5. Emits update to parent

## 🔄 User Experience

**Before:** Empty date range, no filter applied
**After:** "Last 90 days" selected automatically, data filtered immediately

## 🎨 Visual Feedback

- "Last 90 days" button is highlighted in green
- Start/End date inputs show 90 days ago - today
- Transactions display last 90 days of data

## 🔧 To Change Default

Edit line 240 in `DateRangePicker.vue`:

```typescript
selectPreset('last90days'); // Change to: 'last7days', 'last30days', 'last365days', or 'custom'
```

Options:

- `last7days` - Last 7 days
- `last30days` - Last 30 days
- `last90days` - Last 90 days (current)
- `last365days` - Last 365 days
- `custom` - Custom range (requires manual selection)

## ✅ Quality

- No linting errors
- No TypeScript errors
- Respects external values
- No breaking changes

---

**Status:** ✅ COMPLETE
