FROM php:7.3-apache

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    nano \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    zlib1g-dev \
    libzip-dev \
    libicu-dev \
    libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/
RUN docker-php-ext-install gd
RUN docker-php-ext-install mbstring mysqli pdo pdo_mysql zip intl soap

RUN a2enmod rewrite

COPY docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf
COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Crear estructura necesaria del Laravel interno
RUN mkdir -p \
    /var/www/html/appsiel/storage/framework/cache \
    /var/www/html/appsiel/storage/framework/sessions \
    /var/www/html/appsiel/storage/framework/views \
    /var/www/html/appsiel/storage/logs \
    /var/www/html/appsiel/bootstrap/cache \
    /var/www/html/appsiel/config

# Script de arranque para corregir permisos cada vez
RUN printf '%s\n' \
    '#!/bin/sh' \
    'set -e' \
    'mkdir -p /var/www/html/appsiel/storage/framework/cache' \
    'mkdir -p /var/www/html/appsiel/storage/framework/sessions' \
    'mkdir -p /var/www/html/appsiel/storage/framework/views' \
    'mkdir -p /var/www/html/appsiel/storage/logs' \
    'mkdir -p /var/www/html/appsiel/bootstrap/cache' \
    'mkdir -p /var/www/html/appsiel/config' \
    'chown -R www-data:www-data /var/www/html/appsiel/storage /var/www/html/appsiel/bootstrap/cache /var/www/html/appsiel/config || true' \
    'chmod -R 775 /var/www/html/appsiel/storage /var/www/html/appsiel/bootstrap/cache /var/www/html/appsiel/config || true' \
    'find /var/www/html/appsiel/config -type f -exec chmod 664 {} \; || true' \
    'exec apache2-foreground' \
    > /usr/local/bin/start-laravel.sh \
    && chmod +x /usr/local/bin/start-laravel.sh

CMD ["/usr/local/bin/start-laravel.sh"]