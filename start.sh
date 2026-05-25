#!/bin/sh

# Exit immediately if a command exits with a non-zero status
set -e

# Fail fast if DB_HOST is not set — avoids hanging in the wait loop below
if [ -z "$DB_HOST" ]; then
  echo "ERROR: DB_HOST is not set. Add a MySQL service to your Railway project and link the database variables to this service." >&2
  exit 1
fi

echo "Waiting for MySQL at $DB_HOST:${DB_PORT:-3306}..."
TIMEOUT=60
ELAPSED=0
until mysqladmin ping -h "$DB_HOST" -P "${DB_PORT:-3306}" --silent 2>/dev/null; do
  if [ "$ELAPSED" -ge "$TIMEOUT" ]; then
    echo "ERROR: MySQL did not become reachable at $DB_HOST after ${TIMEOUT}s. Aborting." >&2
    exit 1
  fi
  echo "  MySQL not ready yet — retrying in 2s (${ELAPSED}s elapsed)..."
  sleep 2
  ELAPSED=$((ELAPSED + 2))
done
echo "MySQL is ready."

echo "Caching Laravel config, routes, and views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force

echo "Running database seeders..."
php artisan db:seed --force

echo "Starting Supervisord (Nginx + PHP-FPM)..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
