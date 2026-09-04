#!/bin/bash
set -e

PORT="${PORT:-8080}"

if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan migrate --force

# Update Apache port configuration at runtime
if [ -f /etc/apache2/ports.conf ]; then
    sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
fi

if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/g" /etc/apache2/sites-available/000-default.conf
fi

# Start Apache in foreground
exec apache2-foreground

