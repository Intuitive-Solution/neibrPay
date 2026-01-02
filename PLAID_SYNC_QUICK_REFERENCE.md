# Plaid Sync Strategy - Quick Reference

## At a Glance

| Setting              | Value                 | Purpose                              |
| -------------------- | --------------------- | ------------------------------------ |
| **Initial Sync**     | 730 days              | Full 2-year history on first connect |
| **Ongoing Lookback** | 30 days               | Catch pending→posted transitions     |
| **Sync Frequency**   | Weekly                | Every Monday at 00:00 UTC            |
| **Target Use Case**  | Low-activity accounts | ~4 transactions/month                |
| **Cost Impact**      | -95%                  | 720 → 4 API calls/month              |

## Key Files Modified

1. **backend/laravel/app/Console/Kernel.php**
   - Changed scheduler from hourly to weekly
   - Runs Mondays at 00:00 UTC

2. **backend/laravel/app/Services/PlaidService.php**
   - Initial sync: 730 days
   - Ongoing sync: 30 days lookback

3. **backend/laravel/app/Jobs/SyncPlaidTransactions.php**
   - Added comprehensive documentation

4. **apps/admin-web/src/views/Settings.vue**
   - Updated UI message for weekly sync
   - Explains lookback window to users

## User Flows

### Scenario 1: First Bank Connection

```
1. User connects bank account in Settings → Bank
2. PlaidService.exchangePublicToken() creates PlaidBankAccount
3. sync_start_date = NULL (not set)
4. First sync runs (automatic or manual)
5. Syncs 730 days of history (2 years)
6. Stores all transactions in database
```

### Scenario 2: Weekly Automated Sync

```
Every Monday 00:00 UTC:
1. Kernel scheduler triggers plaid:sync command
2. SyncPlaidTransactions job runs
3. For each account with sync_start_date set:
   - Syncs transactions from (today - 30 days) to today
   - Creates new transactions
   - Updates modified transactions
   - Updates last_synced_at timestamp
4. Logs success/failure
```

### Scenario 3: Manual Refresh

```
User clicks "Refresh Transactions Now" in Settings → Bank
1. Calls POST /api/plaid/sync for each account
2. Immediately syncs with 30-day lookback
3. Returns count of synced transactions
4. Shows success/error message
```

### Scenario 4: View Transactions

```
User goes to Transactions page
1. Shows transactions from database
2. User clicks "Refresh" button
3. Re-queries database (NO Plaid API call)
4. Reloads transaction list with current filters
```

## Command Reference

### View Scheduled Tasks

```bash
php artisan schedule:list
```

### Run Sync Manually

```bash
php artisan plaid:sync
```

### Check Recent Logs

```bash
tail -f storage/logs/laravel.log | grep Plaid
```

## Settings by Phase

### Initial Sync (First Time)

- **sync_start_date**: NULL → triggers 730-day window
- **API Call**: Large (retrieves up to 2 years of history)
- **Typical Time**: 2-10 seconds depending on transaction volume
- **Happens**: When user first connects account + first scheduled sync

### Ongoing Sync (Subsequent)

- **sync_start_date**: Set to account creation date
- **Lookback Window**: 30 days (hardcoded in PlaidService)
- **API Call**: Small (only recent transactions)
- **Typical Time**: 1-3 seconds per account
- **Happens**: Every Monday at 00:00 UTC (or when user clicks "Refresh")

## Troubleshooting

### "Bank account shows error status"

- Check `plaid_bank_accounts.error_message` column
- Click "Refresh Transactions Now" to retry
- Or disconnect and reconnect account

### "Transactions not updating"

- Check last_synced_at timestamp
- If older than 7 days, run manual sync
- Verify account status is 'active'

### "Sync taking too long"

- Check transaction volume in database
- Large initial syncs (730 days) are expected to take longer
- Monitor server logs for errors

### "Missing old transactions"

- Initial sync gets 730 days
- If account connected before optimization, may not have full history
- Run manual sync to fetch latest
- Consider disconnecting and reconnecting for fresh 730-day sync

## Performance Metrics

### Before Optimization

- Frequency: Every hour (24 times/day)
- Lookback: 90 days
- Monthly API calls: ~720
- Cost: High

### After Optimization

- Frequency: Weekly (1 time/week)
- Lookback: 30 days for ongoing, 730 for initial
- Monthly API calls: ~4
- Cost: -95% reduction

## Notes for Developers

- **Timezone**: All times reference UTC. Adjust in Kernel.php if needed.
- **sync_start_date**: This field stores when the account was first synced, used to detect initial vs ongoing
- **last_synced_at**: Updated every sync, used to track sync status
- **30-day lookback rationale**: Banks post pending transactions 1-3 days after occurrence, so 30 days ensures overlap
- **730-day window**: Plaid API maximum is 730 days (2 years)

## Related Documentation

- See `PLAID_SYNC_OPTIMIZATION.md` for detailed implementation guide
- See `PLAID_SETUP_GUIDE.md` for initial Plaid setup
- See Laravel Kernel scheduler docs for cron syntax
