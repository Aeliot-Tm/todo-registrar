ARG PHP_VERSION=8.2.7
ARG DEBIAN_RELEASE=bullseye

FROM php:${PHP_VERSION}-fpm-${DEBIAN_RELEASE}

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_NO_INTERACTION 1
ENV COMPOSER_VERSION 2.7.6
ENV DEBIAN_FRONTEND noninteractive
ENV DOCKER_PHP_DEPS \
    libxml2-dev \
    libzip-dev \
    unzip

RUN set -xe; \
    apt-get --allow-releaseinfo-change update && \
    apt-get install -y -qq ${DOCKER_PHP_DEPS} --no-install-suggests --no-install-recommends && \
    docker-php-ext-install -j$(nproc) xml && \
    docker-php-ext-install -j$(nproc) zip

RUN rm -rf /var/lib/apt/lists/* && \
    apt-get clean

WORKDIR /app

COPY . ./
RUN chmod +x ./docker-entrypoint.sh && \
    rm -rf ./.docker
