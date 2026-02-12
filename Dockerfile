FROM php:8.2-apache

# 1. Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_sqlite

# 2. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. Configurar directorio
WORKDIR /var/www/html

# 4. COPIAR ARCHIVOS (Aquí está el truco)
COPY . .
# Forzamos la copia del .env por si acaso se estaba ignorando
COPY .env .env

# 5. Instalar librerías de PHP (Vendor)
RUN composer install --no-dev --optimize-autoloader

# 6. Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80