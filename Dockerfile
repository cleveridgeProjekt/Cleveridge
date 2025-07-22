# Use official PHP image with Composer pre-installed
FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . /app

RUN composer install --no-dev --optimize-autoloader

# Run Laravel migrations & serve app
CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=10000
