# ---------- Build Stage ----------
FROM composer:2.7 AS vendor

WORKDIR /app

# Copiar TODO el proyecto para que composer pueda ejecutar artisan correctamente
COPY . .

# Instalar dependencias de PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# ---------- Final App Stage ----------
FROM php:8.2-apache

# Instalar extensiones y utilidades necesarias
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    sqlite3 \
    && rm -rf /var/lib/apt/lists/*

# Instalar PDO SQLite
RUN docker-php-ext-install pdo pdo_sqlite

# Activar mod_rewrite de Apache
RUN a2enmod rewrite

# Configurar Apache para escuchar en el puerto 8080
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf
RUN sed -i 's/:80/:8080/g' /etc/apache2/sites-available/000-default.conf

# Cambiar el DocumentRoot a /public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/000-default.conf \
    /etc/apache2/apache2.conf

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar el proyecto y los vendor desde la etapa de build
COPY . .
COPY --from=vendor /app/vendor ./vendor

# Dar permisos correctos a storage y bootstrap/cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Exponer el puerto 8080 para Cloud Run
EXPOSE 8080

# Comando para iniciar Apache
CMD ["apache2-foreground"]
