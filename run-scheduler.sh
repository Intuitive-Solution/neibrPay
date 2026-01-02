#!/bin/bash

# NeibrPay Plaid Sync Scheduler
# This script runs Laravel's scheduler in the background
# Required for Railway deployment to execute scheduled tasks
# 
# Make executable: chmod +x run-scheduler.sh
# Test locally: ./run-scheduler.sh

set -e

# Get the directory where this script is located
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Load environment
if [ -f .env ]; then
    set -a
    source .env
    set +a
fi

echo "🚀 Starting Laravel Scheduler..."
echo "App Environment: ${APP_ENV:=production}"
echo "Scheduler will run: plaid:sync every Monday at 00:00 UTC"
echo "---"

# Run the scheduler in a loop that checks every minute
# This keeps the process alive for Railway (required)
while true; do
    # Run Laravel's scheduler
    php artisan schedule:run --verbose --no-interaction
    
    # Sleep for 60 seconds before checking again
    # This reduces load while still checking every minute
    sleep 60
done


