# Plaid Sync Optimization - Verification Checklist

## Implementation Checklist

### ✅ Backend Changes

- [x] **Kernel.php** - Scheduler updated to weekly
  - File: `backend/laravel/app/Console/Kernel.php`
  - Change: `hourly()` → `weekly()->mondays()->timezone('UTC')`
  - Status: ✅ Complete

- [x] **PlaidService.php** - Sync window logic updated
  - File: `backend/laravel/app/Services/PlaidService.php`
  - Changes:
    - Initial sync: 730 days (was 90)
    - Ongoing sync: 30 days (was 90)
    - Added logging for initial vs ongoing syncs
  - Status: ✅ Complete

- [x] **SyncPlaidTransactions.php** - Documentation added
  - File: `backend/laravel/app/Jobs/SyncPlaidTransactions.php`
  - Change: Added comprehensive docblock explaining strategy
  - Status: ✅ Complete

### ✅ Frontend Changes

- [x] **Settings.vue** - UI message updated
  - File: `apps/admin-web/src/views/Settings.vue`
  - Change: Updated Transaction Sync info box with weekly schedule
  - Location: Line 642-661 (Bank tab, Transaction Sync section)
  - Status: ✅ Complete

### ✅ Documentation

- [x] **PLAID_SYNC_OPTIMIZATION.md** - Detailed implementation guide
  - Status: ✅ Created

- [x] **PLAID_SYNC_QUICK_REFERENCE.md** - Quick reference guide
  - Status: ✅ Created

- [x] **PLAID_SYNC_VERIFICATION.md** - This verification checklist
  - Status: ✅ Created

## Testing Checklist

### Backend Testing

```bash
# 1. Verify scheduler configuration
[ ] php artisan schedule:list
    Expected output: "plaid:sync ... weekly ... Monday 00:00 UTC"

# 2. Test sync command manually
[ ] php artisan plaid:sync
    Expected: Should log sync operations (initial or ongoing based on sync_start_date)

# 3. Check logs
[ ] tail -f storage/logs/laravel.log | grep -i plaid
    Expected: Should see log entries for sync operations

# 4. Verify database schema
[ ] Run: php artisan migrate --list
    Expected: All migrations should be completed

# 5. Check PlaidBankAccount model
[ ] Verify fields exist:
    - sync_start_date (date, nullable)
    - last_synced_at (timestamp, nullable)
    - status (enum)
    - error_message (string, nullable)
```

### Frontend Testing

```
# 1. UI Message Verification
[ ] Navigate to http://localhost:3000/settings#bank
[ ] Verify message displays:
    "Transactions are automatically synced weekly (every Monday at 12:00 AM UTC).
     With a 30-day lookback window to catch pending transactions. You can also
     manually refresh anytime to sync immediately."

# 2. Manual Sync Button
[ ] Click "Refresh Transactions Now" button
[ ] Should call POST /api/plaid/sync for each connected account
[ ] Should show success/error message
[ ] Should update last_synced_at in UI

# 3. Transaction Refresh
[ ] Go to http://localhost:3000/transactions
[ ] Click "Refresh" button
[ ] Should reload transaction list
[ ] Should NOT make any Plaid API calls (only database query)

# 4. Bank Account Display
[ ] Verify "Last synced" timestamp shows in account cards
[ ] Should update after manual sync
```

### Integration Testing

```
# 1. First Account Connection
[ ] Connect a new bank account
[ ] Manually run: php artisan plaid:sync
[ ] Check logs for: "Initial Plaid sync - requesting 730 days of history"
[ ] Verify transactions are imported (check plaid_transactions table)
[ ] Verify sync_start_date is now NULL or set appropriately

# 2. Subsequent Syncs
[ ] Manually run: php artisan plaid:sync again
[ ] Check logs for: "Ongoing Plaid sync - using 30-day lookback window"
[ ] Verify only recent transactions are fetched

# 3. Error Handling
[ ] Disconnect bank account (simulate failure)
[ ] Run sync, should handle gracefully
[ ] Account status should be 'error' if sync fails
```

## Code Review Checklist

```
# 1. PlaidService.php
[ ] Verify 730-day window for initial sync
[ ] Verify 30-day window for ongoing syncs
[ ] Check logging is comprehensive
[ ] Ensure error handling is in place

# 2. Kernel.php
[ ] Verify weekly() method is used
[ ] Verify mondays() method is used
[ ] Verify timezone is set to 'UTC'
[ ] Check withoutOverlapping() is present

# 3. Settings.vue
[ ] Verify message text is clear
[ ] Check button functionality still works
[ ] Verify styling is consistent

# 4. SyncPlaidTransactions.php
[ ] Check docblock is comprehensive
[ ] Verify job still handles errors properly
```

## Performance Verification

```
# 1. API Call Reduction
[ ] Before: ~24 API calls/day = ~720/month (hourly sync)
[ ] After: ~4 API calls/month (weekly sync)
[ ] Expected savings: ~95% reduction

# 2. Execution Time
[ ] Initial sync: Expect 2-10 seconds (730 days of data)
[ ] Ongoing sync: Expect <3 seconds per account (30 days)
[ ] Manual sync: Should be instantaneous (<1 second response to UI)

# 3. Database Impact
[ ] Synced transactions stored in plaid_transactions table
[ ] Bank accounts updated in plaid_bank_accounts table
[ ] No duplicate transactions (plaid_transaction_id is unique)
```

## Deployment Checklist

```
# Pre-Deployment
[ ] Run migrations (if any schema changes needed)
[ ] Run tests
[ ] Check for linting errors: php artisan lint
[ ] Verify no console errors in browser dev tools

# Deployment Steps
[ ] Deploy backend changes
[ ] Deploy frontend changes
[ ] Run php artisan cache:clear
[ ] Run php artisan config:cache
[ ] Verify schedule via: php artisan schedule:list
[ ] Monitor logs for first sync

# Post-Deployment
[ ] Monitor first scheduled sync (Monday 00:00 UTC)
[ ] Check logs for any errors
[ ] Verify UI displays correctly
[ ] Test manual sync button
[ ] Test transaction refresh button
```

## Rollback Plan (if needed)

If issues occur, revert to hourly sync:

```bash
# 1. Restore Kernel.php
# Change: ->weekly()->mondays()->timezone('UTC')
# To: ->hourly()

# 2. Restore PlaidService.php
# Change: now()->subDays(730) and now()->subDays(30)
# To: now()->subDays(90)

# 3. Restore Settings.vue
# Revert UI message to original

# 4. Clear scheduler cache
php artisan schedule:clear-cache

# 5. Verify revert
php artisan schedule:list
# Should show hourly again
```

## Success Criteria

✅ **Implementation Complete** when:

- [ ] All 4 files are updated
- [ ] No linting errors
- [ ] UI message displays correctly
- [ ] Manual sync button works
- [ ] Scheduler shows weekly sync
- [ ] Logs show correct sync windows (730 → 30 days)
- [ ] Database updates correctly
- [ ] No errors in browser console

## Expected Outcomes

| Metric          | Before         | After              | Improvement             |
| --------------- | -------------- | ------------------ | ----------------------- |
| API Calls/Month | ~720           | ~4                 | ↓95%                    |
| Sync Frequency  | Hourly         | Weekly             | ↓96%                    |
| Lookback Window | 90 days        | 30 days (ongoing)  | Better for pending txns |
| Initial Setup   | 90 days        | 730 days           | 8.1x more history       |
| Cost            | High           | ~95% reduction     | Significant savings     |
| User Experience | Frequent noise | Clear, predictable | Better                  |

## Monitoring

After deployment, monitor:

```
# Daily
[ ] Check for sync errors in logs
[ ] Verify no Plaid API quota exceeded
[ ] Monitor database size (plaid_transactions table)

# Weekly
[ ] Verify sync runs on Monday
[ ] Check sync execution time
[ ] Review log entries for patterns

# Monthly
[ ] Calculate actual API usage vs expected
[ ] Review transaction sync completeness
[ ] Check for any error patterns
```

## Questions to Verify

- [ ] Does initial sync grab 730 days? (Check logs)
- [ ] Do ongoing syncs use 30 days? (Check logs)
- [ ] Does sync run weekly on Mondays? (Check scheduler)
- [ ] Is timezone correct for your location? (Check Kernel.php)
- [ ] Do users see the new message? (Check Settings UI)
- [ ] Can users manually sync? (Test button)
- [ ] Are pending transactions being caught? (30-day window covers this)
- [ ] Are there any API errors? (Check logs + settings tab)

---

**Status**: ✅ All changes implemented and documented  
**Date**: January 2026  
**Next Review**: After first weekly sync (Monday)
