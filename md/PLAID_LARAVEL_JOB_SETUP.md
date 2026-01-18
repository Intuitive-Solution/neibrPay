# Plaid Bank Sync - Laravel Job Setup

This guide explains how to use Laravel's built-in job scheduling for Plaid bank transaction syncing instead of n8n.

## Overview

The Laravel job approach:

- ✅ Simpler setup (no external services)
- ✅ Better integrated with your Laravel app
- ✅ Automatic retry on failure
- ✅ Built-in logging
- ✅ Queue support for background processing

## How It Works

```
Laravel Scheduler (runs every minute)
        ↓
    Checks if it's the top of the hour
        ↓
   Dispatches SyncPlaidTransactions Job
        ↓
    Job processes all active bank accounts
        ↓
   Fetches transactions from Plaid API
        ↓
   Saves transactions to database
```

## Setup Steps

### 1. Create the Job and Command

The following files have already been created:

- `app/Jobs/SyncPlaidTransactions.php` - The job that does the syncing
- `app/Console/Commands/SyncPlaidTransactionsCommand.php` - The command that dispatches the job
- Updated `app/Console/Kernel.php` - Scheduler configuration

### 2. Configure Your Queue Driver

Update `.env`:

```env
# For synchronous execution (testing)
QUEUE_CONNECTION=sync

# For background processing (production)
# QUEUE_CONNECTION=database
# QUEUE_CONNECTION=redis
```

### 3. Set Up the Scheduler

For the scheduler to work, you need to add a cron job to your server:

**For Local Development:**

```bash
# In one terminal, run the Laravel scheduler
php artisan schedule:work

# The scheduler will run every minute and dispatch the job
```

**For Production (Linux/Mac):**

```bash
# Add this to your crontab
crontab -e

# Add this line:
* * * * * cd /path/to/neibrpay && php artisan schedule:run >> /dev/null 2>&1
```

This runs the scheduler every minute. The scheduler checks if it's time to run `plaid:sync` (every hour).

### 4. Test the Setup

#### Test the Command Directly

```bash
cd backend/laravel

# Run the sync command once
php artisan plaid:sync

# You should see: "Plaid sync job dispatched successfully!"
```

#### Check the Logs

```bash
# View recent logs
tail -f storage/logs/laravel.log

# You should see messages like:
# "Starting Plaid transaction sync job"
# "Successfully synced X transactions for account Y"
# "Plaid sync job completed"
```

#### Test with Schedule:Work

In one terminal:

```bash
php artisan schedule:work
```

In another terminal:

```bash
# Wait for the scheduler to run
# After 1 minute, it will check if it's time to sync
# If it's the top of the hour, it will dispatch the job

# You can manually trigger it:
php artisan plaid:sync
```

## Monitoring

### View Job Logs

```bash
tail -f backend/laravel/storage/logs/laravel.log | grep -i plaid
```

### Check Database Jobs (if using database queue)

```bash
php artisan tinker

# List failed jobs
DB::table('failed_jobs')->get();

# List pending jobs
DB::table('jobs')->get();
```

### Manual Testing

Test syncing a specific account:

```bash
php artisan tinker

# Get an active account
$account = App\Models\PlaidBankAccount::where('status', 'active')->first();

# Sync it
app('App\Services\PlaidService')->syncTransactions($account);

# Should see: ["success" => true, "synced_count" => X, ...]
```

## Schedule Configuration Details

The schedule is configured in `app/Console/Kernel.php`:

```php
$schedule->command('plaid:sync')
    ->hourly()                          // Runs every hour
    ->withoutOverlapping()              // Prevents overlapping executions
    ->onFailure(function () { ... })    // Logs errors
    ->onSuccess(function () { ... });   // Logs success
```

### What Each Setting Does

| Setting                  | Purpose                                      |
| ------------------------ | -------------------------------------------- |
| `->hourly()`             | Run at the top of every hour                 |
| `->withoutOverlapping()` | Prevent multiple jobs running simultaneously |
| `->onFailure()`          | Callback if job fails                        |
| `->onSuccess()`          | Callback if job succeeds                     |

## Queue Drivers Explained

### QUEUE_CONNECTION=sync (Testing)

Jobs execute immediately in the same request.

**Pros:**

- Simple setup
- No external services needed
- Good for testing

**Cons:**

- Blocks the request until job completes
- No background processing

```bash
# Use for development
QUEUE_CONNECTION=sync
```

### QUEUE_CONNECTION=database (Production)

Jobs are stored in database and processed by a worker.

**Setup:**

```bash
# 1. Create jobs table
php artisan queue:table
php artisan migrate

# 2. Start a queue worker (in background)
php artisan queue:work

# 3. Set in .env
QUEUE_CONNECTION=database
```

**Pros:**

- Background processing
- Jobs persist across restarts
- Easy to set up

**Cons:**

- Requires running `queue:work` process

### QUEUE_CONNECTION=redis (Production - Recommended)

Jobs queued in Redis, processed by workers.

**Setup:**

```bash
# 1. Install Redis
brew install redis  # Mac
sudo apt-get install redis-server  # Ubuntu

# 2. Start Redis
redis-server

# 3. Set in .env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# 4. Start queue worker
php artisan queue:work redis
```

**Pros:**

- Fast
- Reliable
- Good for production

**Cons:**

- Requires Redis server

## Troubleshooting

### Job Not Running

1. **Check if scheduler is running:**

   ```bash
   ps aux | grep "schedule:work"
   ```

2. **Check if cron job is set up (production):**

   ```bash
   crontab -l | grep "schedule:run"
   ```

3. **Check logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

### Jobs Stuck in Queue

```bash
# Clear failed jobs
php artisan queue:flush

# Retry failed jobs
php artisan queue:retry all

# Work on specific queue
php artisan queue:work --queue=default
```

### Memory Issues

If worker uses too much memory:

```bash
# Restart worker after X jobs
php artisan queue:work --max-jobs=100

# Restart worker after X seconds
php artisan queue:work --max-time=3600
```

## Keeping Worker Running (Production)

Use a process manager like **Supervisor**:

```ini
# /etc/supervisor/conf.d/laravel-worker.conf
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/laravel/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/laravel/storage/logs/worker.log
```

Then:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## Comparison: Laravel Job vs n8n

| Feature     | Laravel Job  | n8n             |
| ----------- | ------------ | --------------- |
| Setup       | Simple       | Moderate        |
| Integration | Built-in     | External        |
| Monitoring  | Laravel logs | n8n dashboard   |
| Reliability | Native retry | Via workflow    |
| Scalability | Via workers  | Via n8n cluster |
| Cost        | Free         | Cloud pricing   |
| Control     | Full         | Limited         |

## Next Steps

1. **Set up the scheduler** - Use `schedule:work` for development
2. **Test it** - Run `php artisan plaid:sync`
3. **Monitor logs** - Check `storage/logs/laravel.log`
4. **For production** - Set up cron job and queue worker

## Support

For more info, see:

- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- [Laravel Scheduling Documentation](https://laravel.com/docs/scheduling)
- [Plaid API Documentation](https://plaid.com/docs/)

---

**Your Plaid integration with Laravel jobs is ready!** 🚀
