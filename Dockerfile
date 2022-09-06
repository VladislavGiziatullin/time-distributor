FROM php:7.2-fpm-alpine3.12

RUN apk update && \
    apk add --no-cache ${PHPIZE_DEPS}

RUN pecl install xdebug-2.6.1 && \
    docker-php-ext-enable xdebug

RUN apk del ${PHPIZE_DEPS}

COPY --from=composer:2.2.13 /usr/bin/composer /usr/bin/composer

ARG UID=1000

USER ${UID}
