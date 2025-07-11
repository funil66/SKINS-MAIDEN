# Multi-stage build for Laravel backend
FROM php:8.2-fpm-alpine AS base

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    libzip-dev \
    postgresql-dev \
    && pecl install redis \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && docker-php-ext-enable redis

# Install Composer dependencies
FROM base AS composer_deps
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy dependency files and install
COPY ./backend/composer.json ./backend/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

# Development image
FROM base AS development
WORKDIR /var/www/html

# Copy application code
COPY . .

# Install development dependencies
RUN composer install --prefer-dist --no-scripts

# Expose port
EXPOSE 9000

# Default command
CMD ["php-fpm"]

# Optimized production image
FROM base AS production
WORKDIR /var/www/html

# Create non-root user for security
RUN addgroup -g 1000 -S appgroup && \
    adduser -u 1000 -S appuser -G appgroup
USER appuser

# Copy Composer dependencies from previous stage
COPY --from=composer_deps /var/www/html/vendor /var/www/html/vendor
# Copy application code
COPY ./backend .

# Optimize autoloader and Laravel cache
RUN composer dump-autoload --optimize && \
    php artisan config:cache && \
    php artisan route:cache && \
    php artisan view:cache

# Expose port
EXPOSE 9000

# Default command
CMD ["php-fpm"]
