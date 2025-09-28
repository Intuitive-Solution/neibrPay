#!/bin/bash

echo "=== NEIBRPAY LARAVEL DEPLOYMENT ==="

# Exit on any error
set -e

echo "Environment: $APP_ENV"
echo "Port: $PORT"
echo "Current directory: $(pwd)"

# Create all necessary directories with full permissions
echo "Creating Laravel directories..."
mkdir -p bootstrap/cache
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

# Set full permissions (Railway container needs this)
echo "Setting permissions..."
chmod -R 777 bootstrap/cache
chmod -R 777 storage

# Create .env file if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file..."
    if [ -f railway.env.example ]; then
        cp railway.env.example .env
    elif [ -f .env.example ]; then
        cp .env.example .env
    else
        echo "No .env template found, creating basic one..."
        cat > .env << EOF
APP_NAME=NeibrPay
APP_ENV=production
APP_KEY=base64:WVwwWxyoRikY+HySw6rMLfbFzDRZOwwOxTlX+KBlT4w=
APP_DEBUG=false
APP_URL=https://neibrpay-backend-production.up.railway.app

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=mysql.railway.internal
DB_PORT=3306
DB_DATABASE=railway
DB_USERNAME=root
DB_PASSWORD=DZRvlEEcIkafnhGKboHqswSfPJvILdRN

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
EOF
    fi
    echo ".env file created"
else
    echo ".env file already exists"
fi

# Verify Laravel is working
echo "Testing Laravel..."
if php artisan --version; then
    echo "Laravel is working!"
else
    echo "Laravel test failed, but continuing..."
fi

# Start the PHP server
echo "Starting PHP server on 0.0.0.0:$PORT"
exec php -S 0.0.0.0:$PORT -t public
