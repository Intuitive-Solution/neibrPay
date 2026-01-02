# Railway Deployment - Quick Steps

## Prerequisites

- Railway CLI installed
- Railway account
- Git repository connected to Railway

---

## Step-by-Step Deployment

### 1. Prepare Files

Files created/needed in your project root:

```
✅ run-scheduler.sh          (Already created)
✅ railway.json              (Already created)
✅ .env.production           (You need to create this)
✅ composer.json             (Already exists)
✅ package.json              (Already exists)
```

### 2. Create/Update .env for Production

Create `.env.production` file in project root:

```bash
APP_NAME=NeibrPay
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=https://your-railway-app.up.railway.app

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_HOST=your-railway-db
DB_PORT=5432
DB_DATABASE=railway
DB_USERNAME=postgres
DB_PASSWORD=your-db-password

QUEUE_CONNECTION=sync

PLAID_CLIENT_ID=your-plaid-client-id
PLAID_CLIENT_SECRET=your-plaid-client-secret
PLAID_ENVIRONMENT=production
PLAID_REDIRECT_URI=https://your-railway-app.up.railway.app/auth/plaid/callback
PLAID_N8N_API_KEY=your-n8n-api-key
```

### 3. Make Script Executable

```bash
chmod +x run-scheduler.sh
```

### 4. Commit Changes

```bash
git add run-scheduler.sh railway.json .env.production
git commit -m "Add Railway scheduler configuration"
git push
```

### 5. Login to Railway

```bash
railway login
```

### 6. Link Your Project

If first time:

```bash
railway init
# Follow prompts to create project
```

Or if project already exists:

```bash
railway link
# Select your existing Railway project
```

### 7. Configure Services

#### View Current Setup

```bash
railway service list
```

You should see your main API service.

#### Add Scheduler Service

**Option A: Via CLI**

```bash
railway service add
# Select the same repository
# Name it: scheduler
```

**Option B: Via Railway Dashboard**

1. Go to your Railway project
2. Click "Add Service"
3. Select "GitHub" or "Existing Repository"
4. Add new service for scheduler

### 8. Set Environment Variables

#### For Scheduler Service

```bash
railway link scheduler

# Set the same environment variables as API
railway variable set APP_NAME=NeibrPay
railway variable set APP_ENV=production
railway variable set APP_DEBUG=false
railway variable set APP_KEY=base64:YOUR_KEY
railway variable set DB_HOST=your-db
railway variable set DB_PORT=5432
railway variable set DB_DATABASE=railway
railway variable set DB_USERNAME=postgres
railway variable set DB_PASSWORD=your-password
railway variable set PLAID_CLIENT_ID=your-id
railway variable set PLAID_CLIENT_SECRET=your-secret
railway variable set PLAID_ENVIRONMENT=production
railway variable set PLAID_REDIRECT_URI=https://your-app.up.railway.app/auth/plaid/callback
railway variable set QUEUE_CONNECTION=sync
railway variable set LOG_CHANNEL=stack
```

Or set all at once:

```bash
railway variable set APP_NAME=NeibrPay \
  APP_ENV=production \
  APP_DEBUG=false \
  DB_HOST=your-db \
  DB_PORT=5432 \
  DB_DATABASE=railway \
  DB_USERNAME=postgres \
  DB_PASSWORD=your-password \
  PLAID_CLIENT_ID=your-id \
  PLAID_CLIENT_SECRET=your-secret \
  PLAID_ENVIRONMENT=production \
  QUEUE_CONNECTION=sync
```

### 9. Link Database to Scheduler

```bash
# Make sure PostgreSQL is added as service
railway service list

# If not, add it:
railway service add postgres

# Link scheduler to use the database
railway link scheduler
railway variable set DATABASE_URL=$(railway variable get DATABASE_URL)
```

### 10. Deploy

```bash
# Push latest code
git push

# Or manually trigger deployment
railway up

# Watch deployment
railway logs --follow
```

### 11. Verify Deployment

```bash
# Check services
railway service list

# Should show:
# - api     (running)
# - scheduler (running)
# - postgres (running, if using RDS)

# Check scheduler logs
railway logs --service scheduler --follow

# Expected output:
# "Schedule Running" or similar messages
```

### 12. Test Scheduler

```bash
# SSH into scheduler service
railway shell scheduler

# Check scheduled tasks
php artisan schedule:list

# Should show: plaid:sync ... weekly ... Monday 00:00 UTC

# Manually trigger (for testing only)
php artisan plaid:sync --verbose

# Check logs
tail -f storage/logs/laravel.log | grep -i plaid
```

---

## Configuration Details

### railway.json Breakdown

```json
{
  "services": {
    "api": {
      // Run migrations on every deploy, then start server
      "startCommand": "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT",
      // Install PHP & Node dependencies, build assets
      "buildCommand": "composer install && npm install && npm run build",
      "variables": {
        "PORT": "8000"
      }
    },
    "scheduler": {
      // Make script executable and run
      "startCommand": "chmod +x ./run-scheduler.sh && ./run-scheduler.sh",
      // Only need composer (no frontend build needed)
      "buildCommand": "composer install --no-dev --no-scripts"
    }
  }
}
```

### run-scheduler.sh Breakdown

```bash
#!/bin/bash
set -e                          # Exit on error

cd project-root                 # Go to project directory

source .env                     # Load environment variables

while true; do
    # Run scheduler every minute
    php artisan schedule:run --verbose --no-interaction
    sleep 60                    # Wait 60 seconds before next check
done
```

---

## Monitoring

### Check Logs

```bash
# Real-time logs
railway logs --service scheduler --follow

# Last 100 lines
railway logs --service scheduler --tail 100

# Filter for Plaid
railway logs --service scheduler | grep plaid

# Specific date range
railway logs --service scheduler --since "2024-01-08T00:00:00Z"
```

### Expected Log Output

```
Running scheduled commands...
0 0 * * 1 plaid:sync
[UTC] Running: plaid:sync
Starting Plaid transaction sync job
Successfully synced X accounts
Sync completed successfully
```

### Check Database

```bash
railway shell scheduler
php artisan tinker

# Check if scheduler can connect to DB
>>> DB::connection()->getPdo();
# Should return a PDO connection object

# Check last sync
>>> \App\Models\PlaidBankAccount::orderBy('last_synced_at', 'desc')->first();
# Should show recent last_synced_at
```

---

## Troubleshooting

### Scheduler Service Not Starting

**Check 1: View error logs**

```bash
railway logs --service scheduler
# Look for error messages
```

**Check 2: Verify script is executable**

```bash
railway shell scheduler
ls -la run-scheduler.sh
# Should show: -rwxr-xr-x (with x = executable)
```

**Check 3: Check environment variables**

```bash
railway shell scheduler
env | grep APP_ENV
# Should show: APP_ENV=production
```

### Plaid Sync Not Running

**Check 1: Verify schedule list**

```bash
railway shell scheduler
php artisan schedule:list
# Should show plaid:sync with cron expression: 0 0 * * 1
```

**Check 2: Check database connection**

```bash
railway shell scheduler
php artisan migrate:status
# Should show all migrations as "Ran"
```

**Check 3: Manual test**

```bash
railway shell scheduler
php artisan plaid:sync --verbose
# Should complete without errors
```

### High Memory Usage

If scheduler uses too much memory:

1. Update `run-scheduler.sh` to add longer sleep:

```bash
sleep 300  # 5 minutes instead of 1 minute
```

2. Or use cron job instead of continuous loop

---

## Switching to Cron (Alternative)

If continuous scheduler uses too much memory, use Railway's built-in cron:

1. Remove `scheduler` service from `railway.json`
2. In Railway Dashboard:
   - Go to Cron Jobs
   - Add new job
   - Command: `php artisan schedule:run`
   - Schedule: `0 0 * * 1` (Monday 00:00 UTC)
   - Service: api

---

## Health Checks

Railway can automatically restart failed services.

Already configured in `railway.json`:

```json
"healthcheck": {
  "test": ["CMD", "ps", "aux"],
  "interval": 10,
  "timeout": 5,
  "retries": 3
}
```

This checks every 10 seconds if process is still running.

---

## Production Best Practices

✅ **Do:**

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Use strong `APP_KEY`
- Enable HTTPS (Railway does automatically)
- Monitor logs regularly
- Keep secrets in Railway variables (not in code)
- Set proper database backups

❌ **Don't:**

- Commit `.env` files to git
- Use `APP_DEBUG=true` in production
- Put secrets in code
- Run without database backups
- Ignore scheduler logs

---

## After Deployment Checklist

- [ ] Services deployed successfully
- [ ] No errors in logs
- [ ] Database migrations ran
- [ ] Can access your app
- [ ] Schedule list shows plaid:sync
- [ ] Manual sync test works
- [ ] Wait for Monday to verify automatic sync
- [ ] Check logs Monday morning

---

## Quick Reference Commands

```bash
# Login and setup
railway login
railway link

# Service management
railway service list
railway service add scheduler

# Environment variables
railway variable set KEY=VALUE
railway variable list

# Deployment
railway up
git push  # Triggers auto-deploy

# Monitoring
railway logs --follow
railway logs --service scheduler --tail 100
railway shell scheduler

# Database
railway shell scheduler
php artisan tinker
php artisan migrate:status

# Scheduler
php artisan schedule:list
php artisan plaid:sync --verbose
```

---

## Support

Need help? Check:

- Railway Dashboard Logs
- `RAILWAY_SCHEDULER_SETUP.md` (detailed guide)
- `SYNC_REFERENCE_CARD.txt` (quick reference)
- Railway Docs: https://docs.railway.app

---

**Status**: Ready for Railway Deployment  
**Next Step**: Follow steps 1-12 above to deploy
