FROM php:8.2-fpm

RUN apt update
RUN apt-get install -y libpq-dev 
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql pgsql

RUN usermod -u 1000 www-data