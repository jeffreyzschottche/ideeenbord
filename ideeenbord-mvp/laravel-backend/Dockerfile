FROM composer:2 AS composer_deps

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --ignore-platform-req=php \
    --prefer-dist \
    --optimize-autoloader

FROM node:22-alpine AS frontend_assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN npm run build

FROM php:8.2-cli-alpine AS app

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite-dev \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . ./
COPY --from=composer_deps /app/vendor ./vendor
COPY --from=frontend_assets /app/public/build ./public/build
COPY docker/start.sh /usr/local/bin/start-laravel

RUN chmod +x /usr/local/bin/start-laravel \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data /var/www/html

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV LOG_CHANNEL=stderr
ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8000

CMD ["start-laravel"]
