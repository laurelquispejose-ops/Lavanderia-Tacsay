# ---------- Build Stage ----------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ---------- App Stage ----------
FROM php:8.2-apache

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones necesarias
RUN docker-php-ext-install pdo pdo_sqlite

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Cambiar Apache a puerto 8080 (Cloud Run)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

# Configurar DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Copiar proyecto completo
COPY . .

# Copiar vendor desde la etapa anterior
COPY --from=vendor /app/vendor ./vendor

# Permisos
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8080
