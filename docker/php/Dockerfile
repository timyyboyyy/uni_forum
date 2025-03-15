FROM php:8.2-fpm-alpine

# Installieren von PHP-Erweiterungen
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Arbeitsverzeichnis setzen
WORKDIR /var/www/html

# Berechtigungen für www-data setzen
RUN chown -R www-data:www-data /var/www/html
