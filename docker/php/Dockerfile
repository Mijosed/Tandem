FROM php:8.3-fpm

# Définir le répertoire de travail
WORKDIR /var/www/html

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libcurl4-openssl-dev \
    curl \
    && docker-php-ext-install \
    pdo \
    pdo_pgsql \
    intl \
    opcache \
    xml \
    zip \
    && apt-get clean

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


# Exposer le port du serveur PHP-FPM
EXPOSE 9000
