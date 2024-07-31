FROM composer:latest AS composer
FROM php:8.0-cli

# Update
RUN apt update -y && \
    apt install -y \
        apt-utils \
        git \
        locales \
        nano \
        sudo \
        wget \
        zip

RUN pecl install phalcon

# Composer
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
