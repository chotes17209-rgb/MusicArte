FROM php:8.2-fpm

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    nginx \
    && docker-php-ext-install \
    pdo \
    pdo_mysql \
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
RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Configuración de Nginx
COPY nginx.conf /etc/nginx/sites-available/default

EXPOSE 80

# Arrancar PHP-FPM y Nginx
CMD ["sh", "-c", "php-fpm -D && nginx -g 'daemon off;'"]