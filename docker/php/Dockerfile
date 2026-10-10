# Gym — PHP-FPM 8.3 image (dev + base for prod)
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
      icu-dev libzip-dev oniguruma-dev libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
      git unzip bash curl mariadb-client $PHPIZE_DEPS \
 && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
 && docker-php-ext-install -j"$(nproc)" intl pdo_mysql opcache zip gd mbstring \
 && pecl install apcu \
 && docker-php-ext-enable apcu \
 && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/zz-gym.ini

WORKDIR /app

# Chromium for Panther (dev only)
FROM base AS dev
RUN apk add --no-cache chromium chromium-chromedriver
ENV PANTHER_CHROME_BINARY=/usr/bin/chromium-browser \
    PANTHER_CHROME_DRIVER_BINARY=/usr/bin/chromedriver \
    PANTHER_NO_SANDBOX=1 \
    PANTHER_CHROME_ARGUMENTS="--disable-dev-shm-usage --headless=new"
