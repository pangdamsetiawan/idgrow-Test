FROM php:8.2-apache

# Install ekstensi PHP yang dibutuhkan Laravel
RUN apt-get update && apt-get install -y \
    libzip-dev zip unzip git curl libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_mysql zip

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Salin file konfigurasi Apache agar support Laravel
COPY ./docker/vhost.conf /etc/apache2/sites-available/000-default.conf
