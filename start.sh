#!/bin/sh

set -e

if [ -z "$DB_HOST" ]; then
  echo "ERROR: DB_HOST is not set." >&2
  exit 1
fi

echo "Waiting for MySQL at $DB_HOST:${DB_PORT:-3306}..."
TIMEOUT=60
ELAPSED=0
until mysqladmin ping -h "$DB_HOST" -P "${DB_PORT:-3306}" --silent 2>/dev/null; do
  if [ "$ELAPSED" -ge "$TIMEOUT" ]; then
    echo "ERROR: MySQL did not become reachable after ${TIMEOUT}s." >&2
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

echo "Starting Supervisord (Nginx + PHP-FPM)..."
exec supervisord -c /etc/supervisor/conf.d/supervisord.conf
