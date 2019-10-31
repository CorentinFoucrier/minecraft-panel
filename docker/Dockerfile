FROM chialab/php-dev:7.3-apache

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

RUN touch /usr/local/etc/php/conf.d/upload.ini

RUN echo "file_uploads = On" > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "memory_limit = 256M"  > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "upload_max_filesize = 10240M"  > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "post_max_size = 10240M"  > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "max_execution_time = 600"  > /usr/local/etc/php/conf.d/upload.ini

WORKDIR /var/www