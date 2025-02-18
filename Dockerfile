# Use an official PHP image as the base image
FROM php:8.2-fpm

# Install necessary extensions (e.g., for Composer)
RUN apt-get update && apt-get install -y libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd

# Set working directory
WORKDIR /var/www

# Copy the composer.phar file to the container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Expose PHP-FPM port
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]
