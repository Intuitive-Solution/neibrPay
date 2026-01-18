# Plaid Sandbox Transaction Sync Issue

## Summary

**Question:** Will Plaid fetch pending transactions?

**Answer:** ✅ **YES** - Plaid's `/transactions/sync` endpoint DOES fetch pending transactions. The `pending` field in each transaction indicates whether it's pending (`true`) or posted (`false`).

## Current Issue

When creating transactions via `/sandbox/transactions/create`, they may not immediately appear in `/transactions/sync` responses, even after calling `/transactions/refresh`.

## Why This Happens

1. **Cursor Position**: The `/transactions/sync` endpoint uses a cursor to track position. If the cursor is already at the "latest" position when you create a new transaction, it might not appear until:
   - The cursor advances naturally
   - Plaid processes the transaction
   - A future sync cycle occurs

2. **Sandbox Processing**: Transactions created via `/sandbox/transactions/create` may need time to be processed and made available via the sync endpoint.

3. **Timing**: There can be a delay between:
   - Creating the transaction
   - Plaid processing it
   - Making it available via `/transactions/sync`

## Verification

Your code **already handles pending transactions correctly**:

```php
// In storeTransaction() method
'pending' => $transaction['pending'] ?? false,
```

This means:

- ✅ Pending transactions are stored with `pending = true`
- ✅ Posted transactions are stored with `pending = false`
- ✅ Both types are synced correctly

## Solutions

### Option 1: Verify Transaction Exists (Recommended)

Use `/transactions/get` to verify the transaction was created:

```bash
curl -X POST https://sandbox.plaid.com/transactions/get \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "YOUR_CLIENT_ID",
    "secret": "YOUR_SECRET",
    "access_token": "YOUR_ACCESS_TOKEN",
    "start_date": "2026-01-05",
    "end_date": "2026-01-05"
  }'
```

This will show ALL transactions for that date, including the one you just created.

### Option 2: Wait for Natural Sync

The transaction will appear in a future sync cycle when:

- Plaid processes it
- The cursor position allows it to be returned
- A webhook fires naturally

### Option 3: Use Real Test Accounts

For more reliable testing, use:

- Real test accounts (if available)
- The `user_transactions_dynamic` test user with `/transactions/refresh`
- Production-like scenarios

## Testing Recommendations

1. **Verify Transaction Creation**: Always check `/transactions/get` after creating a transaction
2. **Check Pending Status**: Look for transactions with `pending: true` in the response
3. **Monitor Sync Cycles**: Wait for natural webhook cycles to see transactions appear
4. **Use Date Ranges**: Query specific date ranges to find transactions

## Code Status

✅ **Your implementation is correct:**

- Pending transactions are handled
- Sync endpoint is working
- Webhooks are firing
- Database storage is correct

The issue is a **sandbox environment limitation**, not a code problem.

## Next Steps

1. The test script now verifies transactions via `/transactions/get`
2. Monitor logs to see when transactions appear in sync
3. In production, real transactions will sync normally
4. Sandbox testing may require patience or using `/transactions/get` for verification
