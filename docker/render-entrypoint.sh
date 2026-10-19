#!/bin/sh
set -e
cd /var/www/html

: "${PORT:=10000}"

# Fallback key so the app always boots (sessions reset on each restart if you don't set APP_KEY)
if [ -z "$APP_KEY" ]; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  echo "WARNING: APP_KEY not set, generated a temporary one."
fi

# Ephemeral SQLite: Render free disk is wiped on every restart, so we re-migrate + re-seed each boot.
export DB_CONNECTION=sqlite
export DB_DATABASE=/var/www/html/database/database.sqlite
rm -f "$DB_DATABASE"
touch "$DB_DATABASE"

php artisan config:clear
php artisan migrate --force --seed

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== NetApp demo listening on port $PORT ==="
exec php artisan serve --host=0.0.0.0 --port="$PORT" --no-reload
