# syntax=docker/dockerfile:1
# ============================================================================
# Dockerfile para desplegar en Railway con MySQL.
# (NO afecta al `docker compose up -d` local, que construye su propia imagen
#  desde vendor/laravel/sail y NO usa este archivo.)
#
# El Dockerfile anterior (Render + PostgreSQL) quedo guardado como
# Dockerfile.render.old por si algun dia lo necesitas.
# ============================================================================

# ----------------------------------------------------------------------------
# Etapa 1: compilo los assets de frontend (CSS/JS) con Vite.
# ----------------------------------------------------------------------------
FROM node:20-bookworm-slim AS assets
WORKDIR /app

# Copio solo el manifiesto de npm primero para cachear la instalacion de dependencias.
# (No hay package-lock.json en el proyecto, por eso uso npm install y no npm ci.)
COPY package.json ./
RUN npm install

# Copio el resto del proyecto y compilo los assets a /app/public/build.
COPY . .
# Llamo a vite con node directamente para no depender del shim node_modules/.bin/vite.
RUN node node_modules/vite/bin/vite.js build

# ----------------------------------------------------------------------------
# Etapa 2: imagen final con PHP + Apache que sirve Laravel.
# ----------------------------------------------------------------------------
FROM php:8.3-apache AS app

# Extensiones de PHP necesarias para Laravel + MySQL.
RUN apt-get update && apt-get install -y --no-install-recommends \
        libonig-dev \
        libzip-dev \
        unzip \
        git \
        curl \
    && docker-php-ext-install pdo_mysql mbstring bcmath zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Composer (lo copio de su imagen oficial).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache: habilito rewrite/headers y apunto el DocumentRoot a public/.
RUN a2enmod rewrite headers
COPY docker/railway/000-default.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Instalo las dependencias PHP (sin las de desarrollo) en su propia capa para cachearlas.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# Copio el codigo y los assets ya compilados de la etapa 1.
COPY . .
COPY --from=assets /app/public/build ./public/build

# Genero el autoloader optimizado y descubro los paquetes de Laravel.
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi || true

# Me aseguro de que exista el esqueleto de storage (por si el .dockerignore dejo fuera
# alguna subcarpeta) y doy permisos: Apache corre como www-data y necesita escribir aqui.
RUN mkdir -p \
        storage/framework/views \
        storage/framework/cache \
        storage/framework/sessions \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Copio el entrypoint, le quito posibles saltos de linea de Windows (CRLF) y lo hago ejecutable.
COPY docker/railway/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh /etc/apache2/sites-available/000-default.conf \
    && chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
