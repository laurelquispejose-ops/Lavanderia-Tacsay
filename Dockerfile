# ---------- Build Stage ----------
FROM composer:2.7 AS vendor
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --ignore-platform-req=php


# ---------- Final App Stage ----------
FROM php:8.2-apache

# Dependencias del sistema
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libsqlite3-dev \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Extensiones de PHP
RUN docker-php-ext-install pdo pdo_sqlite

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Cambiar Apache para escuchar en 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

# Configurar DocumentRoot a public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copiar código
COPY . .

# Copiar vendor desde el stage de build
COPY --from=vendor /app/vendor ./vendor

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080
