#!/bin/bash

echo "Starting Laravel application..."
echo "PORT: ${PORT:-8080}"
echo "PHP Version: $(php -v | head -n 1)"
echo "Current directory: $(pwd)"
echo "Files in public directory:"
ls -la public/

# Start PHP server
exec php -S 0.0.0.0:${PORT:-8080} -t public
