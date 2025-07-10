FROM php:8.2-fpm

RUN apt-get update \
  && apt-get install -y libpq-dev \
  && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
  && docker-php-ext-install pdo_pgsql pgsql \
  && rm -rf /var/lib/apt/lists/*
