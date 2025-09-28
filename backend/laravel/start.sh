#!/bin/bash

echo "=== LARAVEL STARTUP DEBUG ==="
echo "PORT: ${PORT:-8080}"
echo "PHP Version: $(php -v | head -n 1)"
echo "Current directory: $(pwd)"
echo "User: $(whoami)"

echo "=== CHECKING FILES ==="
echo "Files in current directory:"
ls -la

echo "Files in public directory:"
ls -la public/

echo "Checking .env file:"
if [ -f .env ]; then
    echo ".env file exists"
    echo "First few lines of .env:"
    head -5 .env
else
    echo ".env file NOT found"
fi

echo "Checking vendor directory:"
if [ -d vendor ]; then
    echo "vendor directory exists"
else
    echo "vendor directory NOT found"
fi

echo "=== TESTING PHP ==="
php -r "echo 'PHP is working' . PHP_EOL;"

echo "=== TESTING LARAVEL ==="
if php artisan --version; then
    echo "Laravel artisan is working"
else
    echo "Laravel artisan FAILED"
fi

echo "=== FIXING PERMISSIONS ==="
mkdir -p bootstrap/cache storage/app storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chmod -R 777 bootstrap storage
echo "Permissions fixed"

echo "=== STARTING SERVER ==="
echo "Starting PHP server on 0.0.0.0:${PORT:-8080}"
exec php -S 0.0.0.0:${PORT:-8080} -t public
