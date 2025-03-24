FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY ./public /var/www/html

WORKDIR /var/www/html/public

EXPOSE 80