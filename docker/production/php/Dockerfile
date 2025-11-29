FROM php:8.4-fpm

WORKDIR /var/www/html

COPY ./src /var/www/html

RUN apt-get update && apt-get install -y \
    zip unzip curl git && \
    docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer
RUN composer install --no-dev --optimize-autoloader
