#!/bin/bash
set -e

echo "=== Starting NetApp deployment ==="

# Ensure Laravel storage directory structure exists on the volume
mkdir -p /var/www/storage/app/public
mkdir -p /var/www/storage/framework/cache/data
mkdir -p /var/www/storage/framework/sessions
mkdir -p /var/www/storage/framework/testing
mkdir -p /var/www/storage/framework/views
mkdir -p /var/www/storage/logs
mkdir -p /var/www/storage/database

# Create SQLite database if it doesn't exist
DB_PATH="/var/www/storage/database/database.sqlite"
if [ ! -f "$DB_PATH" ]; then
    touch "$DB_PATH"
    echo "Created SQLite database at $DB_PATH"
fi

# Ensure proper ownership
chown -R www-data:www-data /var/www/storage
chmod -R 775 /var/www/storage

# Also ensure bootstrap/cache is writable
chown -R www-data:www-data /var/www/bootstrap/cache
chmod -R 775 /var/www/bootstrap/cache

cd /var/www

# Cache config, routes, views
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run migrations
echo "Running migrations..."
php artisan migrate --force || true

echo "=== Starting Supervisor (Nginx + PHP-FPM) ==="

# Start supervisor (runs both Nginx and PHP-FPM)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
