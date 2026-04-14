FROM php:8.1-apache

# Habilitar mod_rewrite para el .htaccess
RUN a2enmod rewrite

# Instalar extensiones necesarias para MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copiar archivos del proyecto al contenedor
COPY . /var/www/html/

# Asegurar permisos
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
