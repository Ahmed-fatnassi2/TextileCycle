# =========================================================
# Stage 1: Build Vite frontend assets
# =========================================================
FROM node:20-alpine AS frontend

WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# =========================================================
# Stage 2: Composer dependencies
# =========================================================
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# =========================================================
# Stage 3: Runtime — FrankenPHP + Laravel Octane
# =========================================================
FROM dunglas/frankenphp:php8.2

# System + PHP extensions Laravel needs
RUN install-php-extensions \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        mbstring \
        zip \
        gd \
        intl \
        bcmath \
        opcache \
        pcntl

WORKDIR /var/www/html

# Copy app
COPY --chown=www-data:www-data . .

# Copy built assets and vendor from earlier stages
COPY --from=frontend --chown=www-data:www-data /app/public/build ./public/build
COPY --from=vendor   --chown=www-data:www-data /app/vendor       ./vendor

# Storage + cache perms
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# OPcache tuning for Octane
RUN echo "opcache.enable=1"               >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.validate_timestamps=0"  >> /usr/local/etc/php/conf.d/opcache.ini \
 && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini

EXPOSE 8000

# FrankenPHP server via Octane
CMD ["php", "artisan", "octane:frankenphp", "--host=0.0.0.0", "--port=8000"]