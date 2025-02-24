# Use an official PHP image as the base image
FROM php:8.1-cli

# Install necessary extensions (e.g., for Composer)
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev git libzip-dev zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd zip

# Set working directory
WORKDIR /var/www

# Copy the composer.phar file to the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
