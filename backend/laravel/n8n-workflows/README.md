# n8n Plaid Integration Workflows

This directory contains n8n workflows for integrating Plaid bank transaction syncing with NeibrPay.

## Plaid: Sync Bank Transactions Workflow

### Overview

This workflow automatically syncs all bank transactions from connected Plaid bank accounts on a scheduled basis.

**Schedule**: Every hour (configurable)

### Setup Instructions

#### 1. Environment Variables

Add these variables to your `.env` file:

```env
# Plaid Configuration
PLAID_CLIENT_ID=your_plaid_client_id
PLAID_CLIENT_SECRET=your_plaid_client_secret
PLAID_ENVIRONMENT=sandbox
PLAID_REDIRECT_URI=http://localhost:3000/settings
PLAID_API_KEY=your_n8n_api_key_here

# n8n Configuration
N8N_API_KEY=your_n8n_api_key_here
LARAVEL_API_URL=http://localhost:8000
```

#### 2. Create n8n Credentials

In the n8n UI, create HTTP Header Auth credentials:

1. Go to **Credentials** → **New Credential**
2. Select **HTTP Header Auth**
3. Name it: `Plaid Sync Header Auth`
4. Add header:
   - Name: `X-N8N-API-Key`
   - Value: `{{ $env.PLAID_N8N_API_KEY }}`

#### 3. Import Workflow

1. In n8n UI, go to **Workflows** → **Import**
2. Upload `plaid-sync-transactions.json`
3. Configure the following:
   - **Environment variables**: Ensure all env vars are set (see step 1)
   - **Credentials**: Select the `Plaid Sync Header Auth` credential you created
   - **Schedule**: Adjust frequency if needed (default: every hour)
4. **Save** the workflow
5. **Activate** the workflow (toggle switch in top right)

#### 4. Verify Setup

1. Click **Execute Workflow** to test
2. Check Laravel logs for API call: `tail -f storage/logs/laravel.log`
3. Verify transactions were synced in database:
   ```sql
   SELECT COUNT(*) FROM plaid_transactions;
   ```

### Workflow Details

**Nodes**:

1. **Schedule Trigger** - Runs every hour
2. **HTTP Request** - POST to `/api/plaid/sync-all`
3. **Check Response** - Validates HTTP 200 status
4. **Success/Error Logs** - Visual indicators

**API Endpoint Called**:

```
POST /api/plaid/sync-all
Header: X-N8N-API-Key: <your_api_key>
```

**Response Format**:

```json
{
  "message": "Sync completed",
  "results": {
    "success_count": 5,
    "error_count": 0,
    "errors": []
  },
  "timestamp": "2025-11-25T12:00:00Z"
}
```

### Troubleshooting

**Workflow not executing**:

- Check n8n worker is running: `npm run n8n start`
- Verify workflow is **Activated**
- Check n8n logs: `pm2 logs n8n-worker`

**API returns 401 (Unauthorized)**:

- Verify `PLAID_N8N_API_KEY` matches `config('services.plaid.api_key')` in Laravel
- Check `X-N8N-API-Key` header is being sent

**API returns 500 (Server Error)**:

- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Ensure Plaid credentials are valid in `.env`
- Verify database migrations were run: `php artisan migrate`

**No transactions synced**:

- Verify bank accounts are connected: Check `plaid_bank_accounts` table
- Ensure accounts have `status = 'active'`
- Check if transactions exist in Plaid: Use Plaid Dashboard

### Manual Testing

You can manually trigger the sync via curl:

```bash
curl -X POST http://localhost:8000/api/plaid/sync-all \
  -H "X-N8N-API-Key: your_api_key" \
  -H "Content-Type: application/json"
```

### Editing the Workflow

To modify the schedule frequency:

1. Double-click **Schedule Trigger** node
2. Change "Repeat Interval" or "Trigger at"
3. Save and re-activate

### Performance Considerations

- **Sync Time**: ~2-5 seconds per bank account
- **Max Accounts**: 50+ accounts per sync without issues
- **Transaction Limit**: 500 transactions per account per sync (Plaid pagination handles rest)
- **Data Storage**: ~1KB per transaction in database

### Related Documentation

- [Plaid API Docs](https://plaid.com/docs/)
- [n8n Docs](https://docs.n8n.io/)
- [Laravel API Documentation](../../API.md)
