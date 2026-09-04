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

# Hace que Apache escuche en el puerto que Railway asigna
sed -i "s/80/${PORT}/g" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

apache2-foreground