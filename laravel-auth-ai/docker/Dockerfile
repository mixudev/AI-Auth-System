# ============================================================
# Dockerfile untuk Laravel 11 — Production-ready
# Multi-stage build untuk meminimalkan ukuran image final.
# ============================================================

FROM php:8.3-fpm-alpine AS base

# -- Instalasi ekstensi PHP yang diperlukan
RUN apk update && apk add --no-cache \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        libwebp-dev \
        libzip-dev \
        oniguruma-dev \
        icu-dev \
        linux-headers \
        # php83-pecl-redis \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        zip \
        gd \
        intl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# -- Cleanup build dependencies
RUN apk del $PHPIZE_DEPS

# -- Konfigurasi PHP untuk production
COPY laravel-auth-ai/docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY laravel-auth-ai/docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

# ============================================================
# Stage: Composer dependencies
# ============================================================
FROM composer:2 AS deps

WORKDIR /app

COPY laravel-auth-ai/composer.json laravel-auth-ai/composer.lock ./

# Install dependencies tanpa dev packages dan tanpa autoloader
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# ============================================================
# Stage: Final production image
# ============================================================
FROM base AS production

WORKDIR /var/www/html

# Salin source code aplikasi DULU
COPY laravel-auth-ai/ .

# Salin vendor dari stage deps SETELAH source code
# (menimpa vendor yang mungkin ada di source dengan hasil composer install)
COPY --from=deps /app/vendor ./vendor

# -- Permission: storage dan bootstrap/cache harus writable
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# -- Catatan: artisan cache (config:cache, route:cache, dll.) TIDAK dijalankan
#    saat build karena membutuhkan koneksi DB dan .env yang lengkap.
#    Jalankan setelah container up via setup.sh atau docker compose exec.

# -- Jalankan sebagai www-data (bukan root)
USER www-data

EXPOSE 9000

CMD ["php-fpm"]