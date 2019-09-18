FROM chialab/php-dev:7.2-apache

ENV APACHE_DOCUMENT_ROOT /var/www/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && apt-get install -y \
    apt-utils \
    gcc make autoconf \
    libc-dev pkg-config \
    libyaml-dev \
    mysql-common \
    locales && echo fr_FR.UTF-8 UTF-8 > /etc/locale.gen && locale-gen

RUN pecl install yaml

RUN docker-php-ext-enable yaml

WORKDIR /var/www