#!/bin/bash
set -e

# --- Persistent disk layout (Render disk mounted at /data) ---
mkdir -p /data/storage/app/public \
         /data/storage/framework/cache/data \
         /data/storage/framework/sessions \
         /data/storage/framework/views \
         /data/storage/logs
if [ ! -f /data/database.sqlite ]; then
  touch /data/database.sqlite
fi

# --- Point the app at the persistent disk ---
rm -rf /var/www/html/storage
ln -s /data/storage /var/www/html/storage
mkdir -p /var/www/html/database
if [ ! -e /var/www/html/database/database.sqlite ]; then
  ln -s /data/database.sqlite /var/www/html/database/database.sqlite
fi
chown -R www-data:www-data /data

cd /var/www/html
php artisan storage:link || true
php artisan migrate --force
php artisan config:cache
php artisan route:cache || true
php artisan view:cache || true

# --- Apache listens on Render's PORT ---
PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

exec supervisord -n -c /etc/supervisor/supervisord.conf
