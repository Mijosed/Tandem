FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libpq-dev libzip-dev zip unzip git acl libicu-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql zip opcache intl xml

RUN pecl install redis && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

