FROM php:8.3-fpm

RUN docker-php-ext-install pdo_mysql

RUN apt-get update && \
    apt-get install -y --no-install-recommends \
    zip \
    unzip \
    git \
    nano

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    php -r "unlink('composer-setup.php');"

WORKDIR /var/www/html
