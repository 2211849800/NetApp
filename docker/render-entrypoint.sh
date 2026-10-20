#!/bin/sh
set -e
cd /var/www/html

: "${PORT:=10000}"

# Fallback key so the app always boots (sessions reset on each restart if you don't set APP_KEY)
if [ -z "$APP_KEY" ]; then
  export APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
  echo "WARNING: APP_KEY not set, generated a temporary one."
fi

# Keep an explicitly configured database connection. SQLite is the demo default.
export DB_CONNECTION="${DB_CONNECTION:-sqlite}"
database_is_new=false

if [ "$DB_CONNECTION" = "sqlite" ]; then
  export DB_DATABASE="${DB_DATABASE:-/var/www/html/database/database.sqlite}"

  if [ ! -f "$DB_DATABASE" ]; then
    mkdir -p "$(dirname "$DB_DATABASE")"
    touch "$DB_DATABASE"
    database_is_new=true
  fi
elif [ "$DB_CONNECTION" = "pgsql" ]; then
  # Seed demo records only on the first boot of a newly provisioned database.
  if ! php artisan migrate:status --no-ansi 2>/dev/null | grep -q 'Ran'; then
    database_is_new=true
  fi
fi

php artisan config:clear
php artisan migrate --force

# Demo data is seeded once, only for a new database and only with an explicit password.
if [ "$database_is_new" = true ] && [ "${DEMO_SEED_ENABLED:-false}" = "true" ]; then
  if [ -n "${DEMO_PASSWORD:-}" ]; then
    php artisan db:seed --force
  else
    echo "Skipping demo seed: DEMO_PASSWORD is not configured."
  fi
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "=== NetApp demo listening on port $PORT ==="
exec php artisan serve --host=0.0.0.0 --port="$PORT" --no-reload
