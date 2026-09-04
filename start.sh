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

php artisan serve --host=0.0.0.0 --port="$PORT"
