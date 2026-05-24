#!/bin/sh

# Exit immediately if a command exits with a non-zero status
set -e

echo "Creating SQLite database if not exists..."
if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

echo "Caching Laravel config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force

echo "Starting Supervisord (Nginx + PHP-FPM)..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
