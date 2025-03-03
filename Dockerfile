# Use an official PHP image as the base image
FROM php:8.1-cli

RUN apt-get update && \
    apt-get install -y \
      libpng-dev \
      libjpeg-dev \
      libfreetype6-dev \
      git \
      libzip-dev \
      zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip

RUN pecl install xdebug pcov && docker-php-ext-enable xdebug pcov

# Configure Xdebug for coverage
# RUN echo "xdebug.mode=coverage" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Set working directory
WORKDIR /var/www

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
