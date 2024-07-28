ARG PHP_VERSION=8.2.7
ARG DEBIAN_RELEASE=bullseye

FROM php:${PHP_VERSION}-fpm-${DEBIAN_RELEASE}

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_NO_INTERACTION 1
ENV COMPOSER_VERSION 2.7.6
ENV DEBIAN_FRONTEND noninteractive
ENV DOCKER_PHP_DEPS \
    gnupg \
    libxml2-dev \
    libzip-dev \
    unzip
ENV XDEBUG_VERSION 3.2.1

RUN set -xe; \
    apt-get --allow-releaseinfo-change update && \
    apt-get install -y -qq ${DOCKER_PHP_DEPS} --no-install-suggests --no-install-recommends && \
    docker-php-ext-install -j$(nproc) xml && \
    docker-php-ext-install -j$(nproc) zip && \
    pecl install xdebug-${XDEBUG_VERSION} && docker-php-ext-enable xdebug

RUN rm -rf /var/lib/apt/lists/* && \
    apt-get clean

# Copy php configs
COPY .docker/php/conf.d/*.ini /usr/local/etc/php/conf.d/

# Install composer
RUN set -xe; \
    curl -sS https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/local/bin --version=${COMPOSER_VERSION} > /dev/null 2>&1 && \
    chmod +x /usr/local/bin/composer

# Install PHIVE
RUN set -xe; \
    curl -sSL -o phive.phar https://phar.io/releases/phive.phar && \
    curl -sSL -o phive.phar.asc https://phar.io/releases/phive.phar.asc && \
    gpg --keyserver hkps://keys.openpgp.org --recv-keys 0x9D8A98B29B2D5D79 && \
    gpg --verify phive.phar.asc phive.phar && \
    chmod +x phive.phar && \
    mv phive.phar /usr/local/bin/phive

WORKDIR /app

COPY composer.* ./

RUN set -xe; \
    composer install --no-autoloader --no-dev --no-interaction --no-progress --no-scripts --prefer-dist && \
    composer clear-cache && \
    rm -rf ./vendor/*/*/tests

COPY . ./
RUN chmod +x ./docker-entrypoint.sh && \
    rm -rf ./.docker

RUN composer dump-autoload --optimize
