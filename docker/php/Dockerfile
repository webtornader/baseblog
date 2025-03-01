FROM php:8.2-fpm-alpine

LABEL maintainer="web.tornader@gmail.com"

# Copy php config
COPY ./conf/php.ini /usr/local/etc/php/php.ini

# Packages
RUN apk update && apk upgrade
RUN apk add --no-cache curl git build-base zlib-dev oniguruma-dev autoconf icu-dev bash linux-headers

# Postgres
RUN apk update && apk add --no-cache \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Intl
RUN docker-php-ext-configure intl \
    && docker-php-ext-install intl

# OPCache (if you need it by any means)
# RUN docker-php-ext-install opcache

# XDebug
#ARG INSTALL_XDEBUG=true
#RUN if [ ${INSTALL_XDEBUG} = true ]; \
#  then \
#    pecl install xdebug && docker-php-ext-enable xdebug; \
#  fi;
RUN pecl install xdebug && docker-php-ext-enable xdebug;

# Xdebug config
COPY ./conf/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Non root user
ARG PUID=1000
ARG PGID=1000
RUN apk --no-cache add shadow && \
    groupmod -o -g ${PGID} www-data && \
    usermod -o -u ${PUID} -g www-data www-data

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Git config
RUN git config --global user.email "web.tornader@gmail.com" \
    && git config --global user.name "Web Tornader"

# Set user
USER www-data

# Files
COPY ./ /var/www
WORKDIR /var/www

# Run php-fpm
CMD php-fpm

# Expose port
EXPOSE 9000