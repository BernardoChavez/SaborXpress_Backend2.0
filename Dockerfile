# 1. Usamos PHP 8.3 con Apache (Ideal para Render)
FROM php:8.3-apache

# 2. Instalar dependencias del sistema y extensiones de PostgreSQL
RUN apt-get update && apt-get install -y \
    libpq-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# 3. Habilitar el módulo de reescritura de Apache (necesario para las rutas de Laravel)
RUN a2enmod rewrite

# 4. Configurar el DocumentRoot de Apache a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Copiar los archivos del proyecto al contenedor
WORKDIR /var/www/html
COPY . .

# 7. Instalar dependencias de PHP (Laravel)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 8. Dar permisos correctos a las carpetas de almacenamiento y caché
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Puerto que usa Render por defecto
EXPOSE 80

# 10. Comando de inicio: Migraciones + Apache
# El --force es obligatorio para que corra en producción
CMD sh -c "php artisan migrate --force && apache2-foreground"