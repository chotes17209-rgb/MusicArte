FROM php:8.2-apache

# Extensiones PHP necesarias para Laravel + SQLite + PDF (dompdf)
RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql mbstring zip exif pcntl gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache: servir desde /public y habilitar mod_rewrite (rutas de Laravel)
RUN a2enmod rewrite
# mod_php requiere el MPM prefork; 
RUN a2dismod mpm_event 2>/dev/null; a2dismod mpm_worker 2>/dev/null; a2enmod mpm_prefork
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

# Instala dependencias PHP (por si el vendor/ subido no coincide con este entorno)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Permisos de storage y bootstrap/cache (Laravel escribe ahi)
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs database \
    && touch database/database.sqlite \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80
CMD ["/start.sh"]
