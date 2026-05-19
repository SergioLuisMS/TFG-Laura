# 1. Usar la imagen oficial de PHP con Apache
FROM php:8.2-apache

# 2. Instalar extensiones del sistema y PHP necesarias para Laravel y PostgreSQL
# 2. Instalar extensiones del sistema y PHP necesarias para Laravel y PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd
# 3. Habilitar el módulo rewrite de Apache (crucial para las rutas de Laravel)
RUN a2enmod rewrite

# 4. Instalar Composer (el gestor de paquetes de PHP)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 5. Instalar Node.js y NPM (necesarios para compilar Vite)
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# 6. Establecer el directorio de trabajo en el servidor
WORKDIR /var/www/html

# 7. Copiar todos los archivos del proyecto al contenedor
COPY . .

# 8. Modificar la configuración de Apache para que apunte a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 9. Instalar las dependencias de PHP y compilar el frontend con Vite
RUN composer install --no-dev --optimize-autoloader
RUN npm install
RUN npm run build

# 10. Dar los permisos correctos a las carpetas de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 11. Exponer el puerto por defecto de Render
EXPOSE 80

# 12. Comando para arrancar Apache en primer plano
CMD ["apache2-foreground"]