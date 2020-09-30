FROM chialab/php-dev:7.4-apache

ENV APACHE_DOCUMENT_ROOT /var/www/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN apt-get update && apt-get install -y gnupg

RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list

RUN apt-get update \
    && apt-get install -y apt-utils \
    gcc make autoconf \
    libc-dev pkg-config \
    libyaml-dev \
    mysql-common yarn \
    locales && echo fr_FR.UTF-8 UTF-8 > /etc/locale.gen && locale-gen

RUN pecl install yaml

RUN docker-php-ext-enable yaml

RUN touch /usr/local/etc/php/conf.d/upload.ini

RUN echo "file_uploads = On" > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "memory_limit = 256M"  > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "upload_max_filesize = 10240M"  > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "post_max_size = 10240M"  > /usr/local/etc/php/conf.d/upload.ini  && \
    echo "max_execution_time = 600"  > /usr/local/etc/php/conf.d/upload.ini

ARG SHELL_USER_ID
ARG SHELL_GROUP_ID

RUN groupadd -f -g $SHELL_GROUP_ID user
RUN useradd -u $SHELL_USER_ID -g $SHELL_GROUP_ID user

USER user

WORKDIR /var/www