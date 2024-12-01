FROM composer:2.4.2 AS composer
FROM php:8.1-fpm

COPY ./extra.ini /usr/local/etc/php/conf.d/

# Update
RUN apt update -y && \
    apt install -y \
        git \
        libzip-dev \
        locales \
        nano \
        sudo \
        wget \
        zip && \
    rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*

# PECL Packages
RUN pecl install xdebug

RUN docker-php-ext-install \
        zip

# Install PHP extensions
RUN docker-php-ext-enable \
        xdebug

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
# Bash script with helper aliases
COPY ./.bashrc /root/.bashrc

CMD ["php-fpm"]
