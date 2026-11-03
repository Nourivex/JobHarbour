# ==========================================
# Stage 1: Build Frontend Assets (Vite & Tailwind)
# ==========================================
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm install --include=dev
COPY . .
RUN npm run build

# ==========================================
# Stage 2: Install Composer Dependencies
# ==========================================
FROM composer:2 AS vendor
WORKDIR /app
COPY composer*.json ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ==========================================
# Stage 3: Final Production Runtime (PHP 8.4 + Nginx)
# ==========================================
FROM php:8.4-fpm-alpine
WORKDIR /var/www/html

# Pasang Nginx dan dependensi sistem
RUN apk add --no-cache \
    nginx \
    curl \
    git \
    libpng-dev \
    libzip-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    sqlite \
    sqlite-dev \
    postgresql-dev

# Kompilasi ekstensi PHP untuk Laravel dan Microservices
RUN docker-php-ext-configure intl && \
    docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        pdo_pgsql \
        mbstring \
        zip \
        bcmath \
        intl \
        opcache

# Konfigurasi PHP dan Opcache
RUN { \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.interned_strings_buffer=8'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
        echo 'opcache.enable_cli=1'; \
        echo 'upload_max_filesize=25M'; \
        echo 'post_max_size=30M'; \
        echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/laravel.ini

# Konfigurasi Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Salin kode sumber aplikasi
COPY . .

# Salin dependensi vendor dan aset frontend hasil build
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Autodiscovery package Laravel
RUN php artisan package:discover --ansi

# Setup permission folder storage dan cache
RUN mkdir -p /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/framework/cache \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Pasang script entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
