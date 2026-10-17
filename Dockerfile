
FROM php:8.3-apache

RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod headers \
    && sed -i 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf \
    && mkdir -p /var/run/apache2 /var/lock/apache2 \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2

COPY app/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html

USER www-data

EXPOSE 8080
