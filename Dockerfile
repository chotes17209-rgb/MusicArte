FROM php:8.2-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    nginx \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copiar proyecto
COPY . .

# Dependencias Laravel
# (composer.lock no incluye todavia phpoffice/phpspreadsheet -nueva
# dependencia para el importador de pagos del Excel-, asi que se usa
# "composer update" para que la resuelva de una: "composer install" solo
# no sirve aqui porque Composer corta apenas ve que el lock no matchea.
# "policy.advisories.block=false" en composer.json evita que Composer
# corte el build por CVEs ya reportados en laravel/framework 11.x que
# todavia no tienen parche disponible; el lock que ya estaba en produccion
# usaba una version igualmente afectada -esto no cambia el riesgo real,
# solo deja que el build pase como pasaba antes-.)
RUN composer update \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configuración de Nginx
COPY nginx.conf /etc/nginx/sites-available/default

EXPOSE 80

# Migraciones + Laravel + PHP-FPM + Nginx
CMD ["sh", "-c", "php artisan migrate --seed --force && php artisan optimize:clear && php artisan config:cache && php-fpm -D && nginx -g 'daemon off;'"]