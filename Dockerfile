FROM php:8.3-apache

ARG VERSION=1.0.20260920

LABEL org.opencontainers.image.version="${VERSION}"

RUN a2enmod rewrite headers

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
    && sed -ri -e 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

EXPOSE 80
