# Plaid Sandbox - Simulate New Transactions

This guide provides curl commands to simulate new transactions in Plaid's sandbox environment, which will trigger `SYNC_UPDATES_AVAILABLE` webhooks.

## Prerequisites

Set these environment variables or replace in commands:

```bash
export PLAID_CLIENT_ID="6923886e63de1f00215b725d"
export PLAID_CLIENT_SECRET="your_plaid_secret_here"
export PLAID_ACCESS_TOKEN="your_access_token_here"  # Get from database
export PLAID_ITEM_ID="mzLrA57erGhD7lBVRoQ3iMN6AeRR3ohLPkPQ4"  # Or your item_id
export PLAID_ACCOUNT_ID="your_account_id_here"  # Get from database
```

## Method 1: Create Custom Transaction (Recommended)

This method creates a new transaction and automatically triggers a `SYNC_UPDATES_AVAILABLE` webhook.

### Get Access Token from Database

```bash
cd backend/laravel
php artisan tinker --execute="
\$account = \App\Models\PlaidBankAccount::where('plaid_item_id', 'YOUR_ITEM_ID')->first();
if (\$account) {
    echo \$account->getDecryptedAccessToken();
} else {
    echo 'Account not found';
}
"
```

### Create a New Transaction

**Correct Format:** The endpoint expects a `transactions` array, not individual fields.

```bash
curl -X POST https://sandbox.plaid.com/sandbox/transactions/create \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "'"$PLAID_CLIENT_ID"'",
    "secret": "'"$PLAID_CLIENT_SECRET"'",
    "access_token": "'"$PLAID_ACCESS_TOKEN"'",
    "transactions": [
      {
        "date": "'"$(date +%Y-%m-%d)"'",
        "amount": 50.00,
        "description": "Test Transaction from Sandbox"
      }
    ]
  }'
```

### Example with Hardcoded Values

**Correct Format:** Use `date_posted` and `date_transacted` (not `date`)

```bash
curl -X POST https://sandbox.plaid.com/sandbox/transactions/create \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "6923886e63de1f00215b725d",
    "secret": "YOUR_PLAID_SECRET",
    "access_token": "YOUR_ACCESS_TOKEN",
    "transactions": [
      {
        "date_posted": "2026-01-05",
        "date_transacted": "2026-01-05",
        "amount": 25.50,
        "description": "Test Transaction - Coffee Shop"
      }
    ]
  }'
```

**Field Requirements:**

- `date_posted` (required) - Date transaction posted (YYYY-MM-DD), must be today or up to 14 days in the past
- `date_transacted` (required) - Date transaction occurred (YYYY-MM-DD), must be today or up to 14 days in the past
- `amount` (required) - Transaction amount (can be negative)
- `description` (required) - Transaction description
- `iso_currency_code` (optional) - Currency code, defaults to USD

**Note:**

- You can create multiple transactions at once (up to 10 per request)
- This endpoint only works with Items created using `user_transactions_dynamic` test user

**Response:**

```json
{
  "transaction_id": "new_transaction_id",
  "request_id": "..."
}
```

After this call, Plaid will automatically:

1. Add the transaction to the account
2. Fire a `SYNC_UPDATES_AVAILABLE` webhook
3. Your webhook endpoint will receive the event and trigger a sync

## Method 2: Refresh Transactions (Dynamic Mode)

This method refreshes transactions and triggers webhooks if using `user_transactions_dynamic` test user.

### Refresh Transactions

```bash
curl -X POST https://sandbox.plaid.com/transactions/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "'"$PLAID_CLIENT_ID"'",
    "secret": "'"$PLAID_CLIENT_SECRET"'",
    "access_token": "'"$PLAID_ACCESS_TOKEN"'"
  }'
```

### Example with Hardcoded Values

```bash
curl -X POST https://sandbox.plaid.com/transactions/refresh \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "6923886e63de1f00215b725d",
    "secret": "YOUR_PLAID_SECRET",
    "access_token": "YOUR_ACCESS_TOKEN"
  }'
```

**Note:** This only works if you connected using a test user with `user_transactions_dynamic` mode. Regular sandbox users may not trigger webhooks with this method.

## Method 3: Fire Webhook Manually (Testing Webhook Handler)

To test your webhook handler directly without creating transactions:

```bash
curl -X POST http://localhost:8000/api/plaid/webhook \
  -H "Content-Type: application/json" \
  -d '{
    "webhook_type": "TRANSACTIONS",
    "webhook_code": "SYNC_UPDATES_AVAILABLE",
    "item_id": "mzLrA57erGhD7lBVRoQ3iMN6AeRR3ohLPkPQ4"
  }'
```

## Complete Test Script

A comprehensive test script is available: **`test-plaid-txn-sync.sh`**

### Quick Start

```bash
# Make sure you have PLAID_CLIENT_SECRET set
export PLAID_CLIENT_SECRET="your_secret_here"

# Run the test script
./test-plaid-txn-sync.sh
```

### What the Script Does

The script automatically:

1. ✅ Fetches Plaid credentials from your database
2. ✅ Validates configuration
3. ✅ Creates a test transaction in Plaid Sandbox
4. ✅ Waits for webhook to fire
5. ✅ Checks webhook logs
6. ✅ Verifies transaction was synced to database
7. ✅ Shows sync status and results

### Script Features

- **Automatic credential retrieval** - Gets access token and item_id from database
- **Error handling** - Validates each step and provides clear error messages
- **Colored output** - Easy to read success/error indicators
- **Database verification** - Confirms transaction was actually synced
- **Log analysis** - Shows relevant webhook and sync log entries
- **Optional webhook test** - Can test webhook endpoint directly first

### Manual Test Script (Simple Version)

If you prefer a simpler manual script:

```bash
#!/bin/bash

# Configuration
PLAID_CLIENT_ID="6923886e63de1f00215b725d"
PLAID_CLIENT_SECRET="your_secret_here"
PLAID_ACCESS_TOKEN="your_access_token_here"

# Step 1: Create a new transaction
echo "Creating new transaction..."
TRANSACTION_RESPONSE=$(curl -s -X POST https://sandbox.plaid.com/sandbox/transactions/create \
  -H "Content-Type: application/json" \
  -d '{
    "client_id": "'"$PLAID_CLIENT_ID"'",
    "secret": "'"$PLAID_CLIENT_SECRET"'",
    "access_token": "'"$PLAID_ACCESS_TOKEN"'",
    "transactions": [
      {
        "date_posted": "'"$(date +%Y-%m-%d)"'",
        "date_transacted": "'"$(date +%Y-%m-%d)"'",
        "amount": 25.50,
        "description": "Test Transaction - Restaurant"
      }
    ]
  }')

echo "Transaction created: $TRANSACTION_RESPONSE"

# Step 2: Wait a moment for webhook to fire
echo "Waiting 5 seconds for webhook..."
sleep 5

# Step 3: Check logs
echo "Checking Laravel logs..."
tail -50 backend/laravel/storage/logs/laravel.log | grep -i "webhook\|sync"
```

## Get Account Information

To get the access token and account_id from your database:

```bash
cd backend/laravel

# Get all accounts
php artisan tinker --execute="
\$accounts = \App\Models\PlaidBankAccount::all();
foreach (\$accounts as \$acc) {
    echo 'Item ID: ' . \$acc->plaid_item_id . PHP_EOL;
    echo 'Account ID: ' . \$acc->account_id . PHP_EOL;
    echo 'Access Token: ' . substr(\$acc->getDecryptedAccessToken(), 0, 30) . '...' . PHP_EOL;
    echo '---' . PHP_EOL;
}
"
```

## Verify Webhook Was Received

After creating a transaction, check your Laravel logs:

```bash
tail -f backend/laravel/storage/logs/laravel.log | grep -i "webhook\|sync"
```

You should see:

```
[timestamp] local.INFO: Plaid webhook received {"webhook_type":"TRANSACTIONS","webhook_code":"SYNC_UPDATES_AVAILABLE",...}
[timestamp] local.INFO: Webhook sync completed {"item_id":"...","sync_result":{...}}
```

## Troubleshooting

### Issue: "Invalid access_token"

**Solution:** Make sure you're using the correct access token from your database. Access tokens are encrypted, so use the `getDecryptedAccessToken()` method.

### Issue: "Webhook not firing"

**Solution:**

1. Verify webhook URL is set in Plaid dashboard
2. Check that webhook URL is publicly accessible (use ngrok for local testing)
3. Ensure you're using sandbox environment

### Issue: "Account not found"

**Solution:** Use the correct `item_id` and `account_id` from your database. The `item_id` is the Plaid Item ID, and `account_id` is the specific account within that item.

## Using ngrok for Local Testing

If testing locally, expose your local server:

```bash
# Install ngrok: https://ngrok.com/
ngrok http 8000

# Use the ngrok URL in Plaid dashboard:
# https://your-ngrok-url.ngrok.io/api/plaid/webhook
```

Then update your `.env`:

```env
PLAID_WEBHOOK_URL=https://your-ngrok-url.ngrok.io/api/plaid/webhook
```

## References

- [Plaid Sandbox Transactions Create](https://plaid.com/docs/api/sandbox/#sandboxtransactionscreate)
- [Plaid Transactions Refresh](https://plaid.com/docs/api/transactions/#transactionsrefresh)
- [Plaid Webhooks](https://plaid.com/docs/transactions/webhooks/)
