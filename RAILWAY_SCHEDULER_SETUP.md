# Railway Scheduler Setup Guide

## Overview

To run the Plaid sync scheduler on Railway, you need to set up a **background worker service** that continuously runs Laravel's scheduler, which will execute the `plaid:sync` command every Monday at 00:00 UTC.

---

## Option 1: Dedicated Worker Service (Recommended)

### Step 1: Create Scheduler Script

Create a file named `run-scheduler.sh` in your Laravel project root:

```bash
#!/bin/bash
set -e

# Run Laravel scheduler in foreground (required for Railway)
# This keeps the process running and visible to Railway
while true; do
    php artisan schedule:run --verbose
    sleep 60  # Check every minute if tasks need to run
done
```

Make it executable:

```bash
chmod +x run-scheduler.sh
```

### Step 2: Update `railway.json`

Create or update `railway.json` in your project root to define both services:

```json
{
  "services": {
    "api": {
      "startCommand": "php artisan serve --port=$PORT",
      "buildCommand": "composer install && npm install && npm run build",
      "variables": {
        "PORT": "8000"
      }
    },
    "scheduler": {
      "startCommand": "./run-scheduler.sh",
      "buildCommand": "true"
    }
  }
}
```

### Step 3: Deploy to Railway

1. **Login to Railway**

   ```bash
   railway login
   ```

2. **Link your project**

   ```bash
   railway link
   ```

3. **Add the Scheduler Service**

   ```bash
   railway service add scheduler
   ```

4. **Deploy**
   ```bash
   railway up
   ```

---

## Option 2: Using Railway Cron Jobs (Alternative)

If you prefer not to run a dedicated worker service, use Railway's built-in cron jobs.

### Step 1: Enable Cron Job

In your Railway dashboard:

1. Go to your project
2. Click **Variables** or **Settings**
3. Add environment variable (if needed):
   ```
   APP_ENV=production
   LOG_CHANNEL=stack
   ```

### Step 2: Create Cron Job

1. Navigate to **Cron** section in Railway dashboard
2. Create new cron job:
   - **Command**: `php artisan schedule:run --verbose`
   - **Schedule**: `0 0 * * 1` (Every Monday at 00:00 UTC)
   - **Service**: Select your Laravel service

### Step 3: Verify

Check the cron job logs in Railway dashboard.

---

## Option 3: Docker-Based Scheduler Service

If using Docker, create a `Dockerfile.scheduler`:

```dockerfile
FROM php:8.2-cli

WORKDIR /app

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Copy application
COPY . .

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-dev

# Run scheduler
CMD ["bash", "run-scheduler.sh"]
```

Then in `railway.json`:

```json
{
  "services": {
    "scheduler": {
      "dockerfile": "Dockerfile.scheduler",
      "startCommand": "bash run-scheduler.sh"
    }
  }
}
```

---

## Configuration

### Environment Variables

Ensure your scheduler service has the same `.env` variables as your API service:

```
APP_NAME=NeibrPay
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=your-db-host
DB_PORT=5432
DB_DATABASE=your-db
DB_USERNAME=your-user
DB_PASSWORD=your-password

PLAID_CLIENT_ID=your-client-id
PLAID_CLIENT_SECRET=your-client-secret
PLAID_ENVIRONMENT=production
PLAID_REDIRECT_URI=https://your-domain.com/plaid/callback

QUEUE_CONNECTION=sync
LOG_CHANNEL=stack
```

### Database Connection

Make sure the scheduler can connect to your database:

1. Link your PostgreSQL database to the scheduler service
2. Set `DB_HOST` to the database service name

---

## Verification

### Check Scheduler is Running

```bash
# SSH into Railway service
railway shell

# List scheduled commands
php artisan schedule:list

# Check scheduler log
tail -f storage/logs/laravel.log | grep -i schedule
```

### Monitor Plaid Sync Execution

```bash
# SSH into scheduler service
railway shell scheduler

# Watch for plaid sync
tail -f storage/logs/laravel.log | grep -i plaid

# Manually trigger sync (for testing)
php artisan plaid:sync --verbose
```

---

## Troubleshooting

### Scheduler Not Running

**Check 1: Service is active**

```bash
railway service list
# Should show scheduler service as running
```

**Check 2: Logs show execution**

```bash
railway logs --service scheduler
# Should show "schedule:run" executing
```

**Check 3: Environment variables**

```bash
railway shell scheduler
env | grep -i app
# Verify APP_ENV=production, etc.
```

### Plaid Sync Not Executing

**Check 1: Database connectivity**

```bash
railway shell scheduler
php artisan tinker
# Try: DB::connection()->getPdo()
# Should not throw an error
```

**Check 2: Queue/Job processing**

```bash
# Verify QUEUE_CONNECTION is set correctly
# For scheduler, use 'sync' (synchronous)
php artisan tinker
env('QUEUE_CONNECTION')
```

**Check 3: Cron expression**

```bash
# Verify your cron runs on Monday
# Check via: php artisan schedule:list
```

---

## Monitoring & Alerts

### Set Up Logging

Add to `config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single'],
        'ignore_exceptions' => false,
    ],
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => env('LOG_LEVEL', 'debug'),
    ],
],
```

### Monitor via Railway Logs

```bash
# Watch scheduler logs
railway logs --service scheduler --follow

# Filter for Plaid syncs
railway logs --service scheduler --follow | grep -i plaid
```

### Set Up Alerts (Optional)

In Railway dashboard:

1. Go to your project settings
2. Configure notifications for service failures
3. Get alerted if scheduler service crashes

---

## Database Migrations

### Run Migrations on Deploy

Update your API service start command:

```bash
php artisan migrate --force && php artisan serve --port=$PORT
```

Or in `railway.json`:

```json
{
  "services": {
    "api": {
      "startCommand": "php artisan migrate --force && php artisan serve --port=$PORT"
    }
  }
}
```

---

## Best Practices

### 1. Keep Scheduler Light

- Don't process heavy jobs in scheduler
- Use queued jobs for heavy processing
- Scheduler just triggers, queue processes

### 2. Monitor Execution

- Log every sync: ✅ (Already implemented)
- Set up alerts: Use Railway notifications
- Check logs regularly

### 3. Have a Rollback Plan

```bash
# If scheduler service fails, temporarily disable
railway down --service scheduler

# Then fix and redeploy
railway up --service scheduler
```

### 4. Test Before Production

```bash
# Test on staging first
# Connect to staging environment
railway link --existing staging-project

# Deploy scheduler
railway service add scheduler

# Verify logs
railway logs --service scheduler --follow
```

### 5. Handle Time Zones

- Scheduler uses UTC (as configured)
- Monday 00:00 UTC = specific time in your timezone
- Monitor for correct execution time

---

## Complete Deployment Checklist

### Pre-Deployment

- [ ] Create `run-scheduler.sh` script
- [ ] Update `railway.json` with scheduler service
- [ ] Set all environment variables
- [ ] Test locally: `bash run-scheduler.sh`

### Deployment

- [ ] Commit changes to git
- [ ] Push to your Railway repository
- [ ] Verify API service is running
- [ ] Add scheduler service to Railway
- [ ] Deploy scheduler service

### Post-Deployment

- [ ] Check scheduler service logs
- [ ] Verify `schedule:list` shows tasks
- [ ] Wait for next Monday 00:00 UTC
- [ ] Check logs for sync execution
- [ ] Verify transactions are synced

### Ongoing

- [ ] Monitor logs weekly
- [ ] Set up alerts for failures
- [ ] Review execution times
- [ ] Adjust if needed

---

## Common Issues & Solutions

| Issue                     | Cause                   | Solution                                 |
| ------------------------- | ----------------------- | ---------------------------------------- |
| Scheduler not running     | Service not started     | Check Railway dashboard, restart service |
| Wrong time execution      | Timezone mismatch       | Verify UTC timezone in config            |
| Database connection error | Wrong DB variables      | Check `railway shell`, verify DB_HOST    |
| Plaid sync failing        | API credentials wrong   | Check PLAID\_\* env variables            |
| Logs not showing          | Wrong log channel       | Set LOG_CHANNEL=stack                    |
| High memory usage         | Infinite loop in script | Update `run-scheduler.sh`, add sleep 60  |

---

## Alternative: Using supervisor (Advanced)

If you prefer supervisor (local development):

```ini
[program:scheduler]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan schedule:run --verbose
autostart=true
autorestart=true
numprocs=1
redirect_stderr=true
stdout_logfile=/path/to/storage/logs/scheduler.log
```

But **Railway's built-in scheduler is easier and recommended**.

---

## Resources

- [Railway Documentation](https://docs.railway.app)
- [Railway Cron Jobs](https://docs.railway.app/reference/cron-jobs)
- [Laravel Scheduler](https://laravel.com/docs/10.x/scheduling)
- [Railway Environment Variables](https://docs.railway.app/reference/variables)

---

## Summary

**Recommended Setup for NeibrPay:**

1. ✅ Create `run-scheduler.sh` in project root
2. ✅ Update `railway.json` with scheduler service
3. ✅ Deploy scheduler service to Railway
4. ✅ Verify logs show execution every Monday
5. ✅ Monitor for sync completion

**Expected Behavior:**

- Every Monday at 00:00 UTC, scheduler runs
- `plaid:sync` command executes
- All active bank accounts sync (30-day window)
- Logs show completion status
- Transactions update automatically

---

**Status**: Ready for Railway deployment  
**Next Step**: Create `run-scheduler.sh` and push to production
