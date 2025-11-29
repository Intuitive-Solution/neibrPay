# Plaid Bank Integration Setup Guide

This guide walks you through setting up the Plaid bank transaction integration for NeibrPay.

## Overview

The Plaid integration allows HOA managers to:

- Connect their HOA bank accounts via Plaid Link
- Automatically sync transactions hourly (via n8n)
- View all bank transactions in a read-only dashboard
- Filter and search transactions by date, account, and status

## Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                     Frontend (Vue 3)                             │
├─────────────────────────────────────────────────────────────────┤
│  Settings > Bank Tab        →  Connect Bank (Plaid Link)        │
│  New Menu: Transactions     →  View/Filter Transactions         │
└─────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────┐
│              Backend API (Laravel)                              │
├─────────────────────────────────────────────────────────────────┤
│  POST   /api/plaid/link-token       → Create Plaid Link token   │
│  POST   /api/plaid/exchange-token   → Connect bank account      │
│  GET    /api/plaid/bank-accounts    → List connected accounts   │
│  DELETE /api/plaid/bank-accounts/{id} → Disconnect account     │
│  GET    /api/plaid/transactions     → List transactions         │
│  POST   /api/plaid/sync-all         → Manual sync (API key)     │
└─────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────┐
│              n8n Workflow (Scheduled Sync)                       │
├─────────────────────────────────────────────────────────────────┤
│  Every Hour: Schedule Trigger → HTTP POST /api/plaid/sync-all   │
└─────────────────────────────────────────────────────────────────┘
                                    ↓
┌─────────────────────────────────────────────────────────────────┐
│              Plaid API                                           │
├─────────────────────────────────────────────────────────────────┤
│  - Get Link Token                                               │
│  - Exchange Public Token → Access Token                         │
│  - Fetch Transactions                                           │
└─────────────────────────────────────────────────────────────────┘
```

## Step 1: Plaid Account Setup

1. **Create a Plaid Account**:
   - Go to https://plaid.com/
   - Sign up for a Plaid account
   - Verify your email

2. **Create an Application**:
   - Dashboard → Applications → Create an Application
   - Name: `NeibrPay`
   - Environment: Select **Sandbox** for testing
   - Copy your credentials:
     - Client ID
     - Client Secret

3. **Configure Redirect URI**:
   - Settings → API → Redirect URIs
   - Add: `http://localhost:3000/settings` (development)
   - Add: `https://yourdomain.com/settings` (production)

## Step 2: Backend Configuration

### Update Environment Variables

Edit `.env`:

```env
# Plaid Configuration
PLAID_CLIENT_ID=your_plaid_client_id_here
PLAID_CLIENT_SECRET=your_plaid_client_secret_here
PLAID_ENVIRONMENT=sandbox
PLAID_REDIRECT_URI=http://localhost:3000/settings
PLAID_API_KEY=generate_secure_random_key_here

# Generate PLAID_API_KEY with:
# php artisan tinker
# > bin2hex(random_bytes(32))
```

### Run Database Migrations

```bash
cd backend/laravel

# Run migrations
php artisan migrate

# Verify tables created
php artisan tinker
> DB::table('plaid_bank_accounts')->get();
> DB::table('plaid_transactions')->get();
```

### Verify Backend Setup

```bash
# Test the API
curl http://localhost:8000/api/plaid/link-token \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"

# Should return: { "link_token": "...", "expiration": "...", ... }
```

## Step 3: Frontend Setup

### Install Plaid Link Script (Automatic)

The frontend automatically loads Plaid Link from CDN when connecting a bank account.

### Verify Frontend Routes

The following routes are automatically available:

- `/settings` → Settings page with new "Bank" tab
- `/transactions` → New transactions view (menu item added to sidebar)

## Step 4: n8n Workflow Setup

### Import the Workflow

1. **Access n8n**:
   - Go to http://localhost:5678

2. **Import Workflow**:
   - Click **Workflows** → **Import from file**
   - Select `backend/laravel/n8n-workflows/plaid-sync-transactions.json`
   - Click **Open**

3. **Configure Credentials**:
   - The workflow needs HTTP Header Auth credentials
   - n8n will prompt you to create them if missing
   - Create credential named: `Plaid Sync Header Auth`
   - Header Name: `X-N8N-API-Key`
   - Header Value: `{{ $env.PLAID_N8N_API_KEY }}` (use your generated key)

4. **Configure Environment Variables in n8n**:
   - n8n Settings → Environment Variables
   - Add:
     ```
     LARAVEL_API_URL = http://localhost:8000
     PLAID_N8N_API_KEY = your_generated_api_key_here
     ```

5. **Activate Workflow**:
   - Open the workflow
   - Click the **Activate** button (top right)
   - Status should show "Workflow is now active"

6. **Test the Workflow**:
   - Click **Execute Workflow**
   - Should complete successfully
   - Check Laravel logs: `tail -f storage/logs/laravel.log`
   - Should see sync request logged

## Step 5: Testing the Integration

### 1. Test Bank Connection (Settings Page)

```
1. Go to: http://localhost:3000/settings
2. Click "Bank" tab
3. Click "Connect Bank Account"
4. Plaid Link popup appears
5. Select "Sandbox" environment
6. Search for "Sandbox Bank"
7. Use demo credentials:
   - Username: user_good
   - Password: pass_good
8. Click "Connect"
9. Should see "Bank account connected successfully!"
```

### 2. Verify Database

```bash
php artisan tinker

# Check connected account
> DB::table('plaid_bank_accounts')->where('status', 'active')->first();

# Should return account details with encrypted access token
```

### 3. Manual Transaction Sync

```bash
# From API
curl -X POST http://localhost:8000/api/plaid/sync-all \
  -H "X-N8N-API-Key: your_api_key" \
  -H "Content-Type: application/json"

# Response should show:
# { "message": "Sync completed", "results": { "success_count": 1, ... } }
```

### 4. View Transactions

```
1. Go to: http://localhost:3000/transactions
2. Should see list of transactions
3. Test filters:
   - Date range
   - Bank account
   - Transaction status (Pending/Posted)
   - Search by name
```

## Usage Guide

### For HOA Admins

#### 1. Connect Bank Account

- Settings → Bank tab
- Click "Connect Bank Account"
- Follow Plaid Link onboarding
- Account appears in list immediately

#### 2. View Transactions

- New "Transactions" menu item in sidebar
- Click to view all bank transactions
- Use filters to narrow down results
- Transactions auto-sync every hour

#### 3. Transaction Details

- **Date**: Transaction date
- **Description**: Transaction name/merchant
- **Amount**: Transaction amount (green for deposits, red for withdrawals)
- **Status**: Pending (not yet posted) or Posted
- **Account**: Which bank account transaction came from

### For Developers

#### API Endpoints

**Create Link Token** (frontend)

```
POST /api/plaid/link-token
Response: { link_token, expiration, request_id }
```

**Exchange Public Token** (after Plaid onboarding)

```
POST /api/plaid/exchange-token
Body: { public_token }
Response: { bank_account { id, account_name, account_mask, institution_name } }
```

**List Bank Accounts**

```
GET /api/plaid/bank-accounts
Response: { bank_accounts [] }
```

**List Transactions**

```
GET /api/plaid/transactions?bank_account_id=1&start_date=2025-01-01&end_date=2025-12-31&page=1&per_page=20
Response: { data [], pagination {} }
```

**Disconnect Bank Account**

```
DELETE /api/plaid/bank-accounts/{id}
Response: { message }
```

**Manual Sync (n8n only)**

```
POST /api/plaid/sync-all
Header: X-N8N-API-Key: your_api_key
Response: { message, results { success_count, error_count, errors } }
```

## Troubleshooting

### Issue: "Failed to create Link token"

**Solution**:

- Check `PLAID_CLIENT_ID` and `PLAID_CLIENT_SECRET` in `.env`
- Verify Plaid API credentials are correct
- Check network connectivity to Plaid API

### Issue: "Unauthorized" on /api/plaid/sync-all

**Solution**:

- Verify `X-N8N-API-Key` header matches `PLAID_API_KEY`
- Check header name is exactly `X-N8N-API-Key` (case-sensitive)
- Regenerate API key if needed: `php artisan tinker` → `bin2hex(random_bytes(32))`

### Issue: No transactions showing

**Solution**:

- Verify bank account is `active` in database
- Manually trigger sync: curl command above
- Check Laravel logs for errors
- Ensure transaction sync start date is set correctly

### Issue: n8n workflow not executing

**Solution**:

- Check n8n is running: `npm run n8n start`
- Verify workflow is **Activated**
- Check environment variables in n8n Settings
- Review n8n worker logs: `pm2 logs n8n-worker`

### Issue: Plaid Link popup not appearing

**Solution**:

- Check browser console for JavaScript errors
- Verify Plaid Link script is loading (Network tab)
- Clear browser cache and try again
- Check redirect URI in Plaid dashboard

## Production Deployment

### 1. Switch to Production Environment

```env
# .env
PLAID_ENVIRONMENT=production

# Create new Plaid production application
# Get new Client ID and Secret from production app
PLAID_CLIENT_ID=prod_client_id
PLAID_CLIENT_SECRET=prod_client_secret
PLAID_REDIRECT_URI=https://yourdomain.com/settings
```

### 2. Update Plaid Settings

- Plaid Dashboard → Settings → API
- Add production redirect URI: `https://yourdomain.com/settings`

### 3. Secure API Key

- Use strong random key for `PLAID_API_KEY`
- Store in secure vault (AWS Secrets Manager, etc.)
- Rotate periodically

### 4. Database Encryption

- Ensure `APP_KEY` is strong in `.env`
- Access tokens are automatically encrypted before storing
- Only decrypt when needed for Plaid API calls

### 5. Monitor Production Sync

- Set up logging/monitoring for sync errors
- Alert on failed syncs
- Monitor API rate limits (Plaid has limits)

## Performance Optimization

### Database Indexes

The migrations automatically create indexes on:

- `tenant_id` (for multi-tenant isolation)
- `status` (for filtering active accounts)
- `date` (for transaction queries)
- `pending` (for status filtering)

### Query Optimization

Transactions view uses:

- Pagination (20 per page default)
- Indexed filtering
- Select specific columns
- Eager loading bank account relationship

### Sync Optimization

- Only active accounts are synced
- Parallel sync possible for multiple accounts
- Incremental updates (doesn't re-fetch old data)
- Transaction limit per sync: 500 (Plaid pagination handles rest)

## Security Considerations

### Multi-Tenant Isolation

- All queries scoped to `tenant_id`
- Bank accounts isolated by tenant
- Transactions isolated by tenant
- API key verified on sync endpoint

### Data Encryption

- Access tokens encrypted at rest
- Decrypted only when calling Plaid API
- Decryption key stored in `APP_KEY`

### API Security

- `/api/plaid/sync-all` requires API key header
- Other endpoints require authentication token
- All requests validated and sanitized

### Plaid Security

- Uses Plaid's official PHP SDK
- All communication over HTTPS
- OAuth2 flow for bank account connection
- No direct bank credentials stored

## Support & Resources

- [Plaid Documentation](https://plaid.com/docs/)
- [Plaid API Reference](https://plaid.com/docs/api/overview/)
- [n8n Documentation](https://docs.n8n.io/)
- [Laravel Documentation](https://laravel.com/docs)
- [NeibrPay API Documentation](./backend/laravel/API.md)

## Files Created

### Backend

- `app/Models/PlaidBankAccount.php` - Bank account model
- `app/Models/PlaidTransaction.php` - Transaction model
- `app/Services/PlaidService.php` - Plaid service
- `app/Http/Controllers/Api/PlaidController.php` - API endpoints
- `database/migrations/2025_11_25_000001_create_plaid_bank_accounts_table.php`
- `database/migrations/2025_11_25_000002_create_plaid_transactions_table.php`
- `config/services.php` - Updated with Plaid config
- `routes/api.php` - Updated with Plaid routes

### Frontend

- `packages/api-client/src/plaid.ts` - API client
- `packages/api-client/src/queryKeys.ts` - Updated query keys
- `apps/admin-web/src/views/Transactions.vue` - Transactions view
- `apps/admin-web/src/router/index.ts` - Updated routes
- `apps/admin-web/src/components/AppSidebar.vue` - Updated sidebar
- `apps/admin-web/src/views/Settings.vue` - Updated with Bank tab

### n8n

- `backend/laravel/n8n-workflows/plaid-sync-transactions.json` - Workflow
- `backend/laravel/n8n-workflows/README.md` - Workflow documentation

## Next Steps

1. ✅ Backend setup complete
2. ✅ Frontend setup complete
3. ⏳ Run database migrations
4. ⏳ Configure environment variables
5. ⏳ Set up n8n workflow
6. ⏳ Test bank connection
7. ⏳ Deploy to production

Good luck! 🎉
