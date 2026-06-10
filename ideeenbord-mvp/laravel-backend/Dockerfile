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

# Runtime image: nginx + php-fpm + supervisor. start.sh execs supervisord, so
# nginx/supervisor MUST be installed and their configs copied here — otherwise
# the container crash-loops with "supervisord: not found".
FROM php:8.2-fpm-alpine AS app

WORKDIR /var/www/html

RUN apk add --no-cache \
        bash \
        nginx \
        supervisor \
        icu-dev \
        libzip-dev \
        oniguruma-dev \
        sqlite-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" \
        intl \
        pdo \
        pdo_mysql \
        pdo_sqlite \
        zip \
        bcmath \
        opcache \
        pcntl \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . ./
COPY --from=composer_deps /app/vendor ./vendor
COPY --from=frontend_assets /app/public/build ./public/build

# Container configuration
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/start.sh /usr/local/bin/start-laravel

RUN chmod +x /usr/local/bin/start-laravel \
    && mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        /run/nginx \
    && php artisan package:discover --ansi \
    && chown -R www-data:www-data /var/www/html

# Production defaults (override per environment). RUN_QUEUE/RUN_SCHEDULER are
# referenced by supervisord.conf via %(ENV_...)s and must always be defined.
ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    RUN_QUEUE=true \
    RUN_SCHEDULER=true

EXPOSE 8000

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD wget -qO- http://127.0.0.1:8000/up >/dev/null 2>&1 || exit 1

CMD ["start-laravel"]
