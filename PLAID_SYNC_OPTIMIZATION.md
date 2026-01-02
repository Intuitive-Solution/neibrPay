# Plaid Transaction Sync Optimization

## Overview

Updated the Plaid transaction sync strategy to be optimized for low-activity HOA accounts (~4 transactions/month).

## Changes Implemented

### 1. **Scheduler Update** (Kernel.php)

**File**: `backend/laravel/app/Console/Kernel.php`

- ✅ Changed from **hourly** sync to **weekly** sync
- ✅ Scheduled to run **every Monday at 00:00 UTC**
- ✅ Reduces API costs and database load by ~95%
- ✅ Allows time for pending transactions to post (typically 1-3 days)

**Before**:

```php
$schedule->command('plaid:sync')->hourly()
// 24 syncs/day = 720 syncs/month
```

**After**:

```php
$schedule->command('plaid:sync')
    ->weekly()
    ->mondays()
    ->timezone('UTC')
// 1 sync/week = 4 syncs/month
```

### 2. **Sync Window Optimization** (PlaidService.php)

**File**: `backend/laravel/app/Services/PlaidService.php`

#### Initial Sync (First Time)

- **Lookback Window**: 730 days (2 years)
- **Purpose**: Retrieve complete historical baseline
- **When**: First time bank account is connected

#### Ongoing Syncs (Subsequent)

- **Lookback Window**: 30 days (4-5 weeks)
- **Purpose**: Catch delayed postings and status changes
- **Frequency**: Weekly (every Monday)

**Benefits of 30-day lookback**:

- Captures pending → posted transitions (1-3 days)
- Handles delayed postings (up to 5 days)
- Overlaps with previous syncs to ensure no gaps
- Minimal API redundancy

### 3. **Job Documentation** (SyncPlaidTransactions.php)

**File**: `backend/laravel/app/Jobs/SyncPlaidTransactions.php`

Added comprehensive documentation explaining:

- Initial vs ongoing sync strategy
- Low-activity account optimization
- Frequency rationale
- Cost/efficiency balance

### 4. **UI Message Update** (Settings.vue)

**File**: `apps/admin-web/src/views/Settings.vue`

Updated the Transaction Sync info message to reflect:

- Weekly frequency instead of hourly
- Specific timing (Monday at 12:00 AM UTC)
- 30-day lookback window
- Manual refresh option for immediate syncs

**Before**:

```
"Transactions are automatically synced every hour. You can also
manually refresh anytime."
```

**After**:

```
"Transactions are automatically synced weekly (every Monday at 12:00 AM UTC).
With a 30-day lookback window to catch pending transactions. You can also
manually refresh anytime to sync immediately."
```

## Sync Strategy Summary

| Phase                     | Initial Sync                 | Ongoing Syncs       |
| ------------------------- | ---------------------------- | ------------------- |
| **Lookback Window**       | 730 days (2 years)           | 30 days             |
| **Frequency**             | Once (on account connect)    | Weekly (Mondays)    |
| **Purpose**               | Complete historical baseline | Incremental updates |
| **Expected Transactions** | Full history                 | Only new/modified   |
| **API Calls/Month**       | 1 (large)                    | ~4 (small)          |
| **Cost Impact**           | Baseline                     | ~95% reduction      |

## User Actions Available

1. **Automatic Sync** (Passive)
   - Happens every Monday at 00:00 UTC
   - No user action required
   - Updates all connected accounts

2. **Manual Sync** (Active)
   - Button: "Refresh Transactions Now" (Settings → Bank)
   - Syncs all accounts immediately
   - Useful when user wants to see latest transactions

3. **Local Refresh** (Display Only)
   - Button: "Refresh" (Transactions page)
   - Reloads transaction list from database
   - No Plaid API call
   - Fast operation

## Implementation Details

### Initial Sync Detection

The system detects initial sync by checking if `sync_start_date` is `NULL`:

- First sync: `sync_start_date` is null → Use 730-day window
- Subsequent syncs: `sync_start_date` is set → Use 30-day window

### Error Handling

- Failed syncs update account status to `error`
- Error message stored in `error_message` field
- Users can manually retry via "Refresh Transactions Now"
- Failed accounts can be disconnected and reconnected

## Testing

To verify the changes:

1. **Check Scheduler**

   ```bash
   php artisan schedule:list
   # Should show: plaid:sync weekly on Monday at 00:00 UTC
   ```

2. **Manual Test**

   ```bash
   php artisan plaid:sync
   # Should log initial 730-day sync or ongoing 30-day sync
   ```

3. **UI Test**
   - Go to Settings → Bank tab
   - Verify message displays correctly
   - Test "Refresh Transactions Now" button

## Notes

- **Timezone**: All times are in UTC. Adjust if your servers use different timezone.
- **Initial Sync**: Will be large on first run due to 730-day window
- **Pending Transactions**: 30-day window ensures pending transactions are captured before they post
- **Cost Savings**: ~95% reduction in API calls (720 → 4 per month)

## Future Enhancements

1. **Webhook Integration**: Implement Plaid `/transactions/sync` endpoint for real-time updates
2. **Cursor-Based Pagination**: Use Plaid's cursor for more efficient incremental syncs
3. **User-Configurable Frequency**: Allow different sync frequencies per account
4. **Activity-Based Scheduling**: Auto-adjust frequency based on transaction volume
