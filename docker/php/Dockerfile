FROM php:8.3-fpm

WORKDIR /var/www/html

# Installe extensions + git + unzip
RUN apt-get update && apt-get install -y \
    git unzip zip libpq-dev && \
    docker-php-ext-install pdo pdo_pgsql

# Installe Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

EXPOSE 9000
