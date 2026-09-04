FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring zip exif pcntl gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Disable conflicting MPMs - do this FIRST before any other apache changes
RUN a2dismod mpm_event mpm_worker 2>/dev/null || true

# Now that we have a clean state, enable mpm_prefork and rewrite
RUN a2enmod mpm_prefork rewrite

# Apply Apache configuration changes
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf && \
    sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 8080
CMD ["/start.sh"]

