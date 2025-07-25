# Use official PHP CLI image
FROM php:8.2-cli

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    curl \
    zip \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libmysqlclient-dev \
    default-mysql-client \
    npm \
    nodejs \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application source
COPY . /app

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN npm install && npm run build

# Laravel optimizations
RUN php artisan route:cache

# Copy and make start.sh executable
COPY start.sh /app/start.sh
RUN chmod +x /app/start.sh

# Set entrypoint
CMD ["sh", "/app/start.sh"]
