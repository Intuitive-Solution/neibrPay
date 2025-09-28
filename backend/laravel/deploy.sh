#!/bin/bash
set -e

echo "Creating required directories..."
mkdir -p bootstrap/cache
mkdir -p storage/app
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs

echo "Setting permissions..."
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader --no-scripts

echo "Running Laravel optimizations..."
php artisan package:discover --ansi
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deployment completed successfully!"