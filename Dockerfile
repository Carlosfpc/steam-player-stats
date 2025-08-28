# Dockerfile

# Base Image: PHP 8.3 FPM on Alpine Linux for a minimal production image.
FROM php:8.3-fpm-alpine

# Set the application's working directory.
WORKDIR /var/www/html

# Install system dependencies required for PHP extensions and common tools.
# --no-cache flag creates a smaller image by not storing the package index.
RUN apk update && apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    icu-dev \
    libzip-dev \
    zip \
    unzip \
    mariadb-client \
    oniguruma-dev

# Install PHP extensions commonly used by Laravel/Filament.
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd intl zip

# Install Composer from its official multi-stage build for security and correctness.
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# --- Docker Layer Caching Optimization ---
# 1. Copy only dependency manifests to leverage Docker's cache.
#    This layer only rebuilds if composer.json or composer.lock change.
COPY composer.json composer.lock ./

# 2. Install Composer dependencies.
#    --prefer-dist is faster and suitable for builds.
#    --no-autoloader/scripts prevents errors before the full codebase is present.
RUN composer install --no-interaction --no-plugins --no-scripts --no-autoloader --prefer-dist

# 3. Copy the application source code into the image.
#    (Ensure a .dockerignore file is present to exclude unnecessary files).
COPY . .
# --- End Caching Optimization ---

# Generate the Composer autoloader after all source code is available.
RUN composer dump-autoload --optimize

# Set correct permissions for the application files for the web server user.
RUN chown -R www-data:www-data /var/www/html

# --- Container Entrypoint Configuration ---
# Copy and set the entrypoint script to run on container startup.
COPY docker/app/entrypoint_mac.sh /usr/local/bin/entrypoint_mac.sh
RUN chmod +x /usr/local/bin/entrypoint_mac.sh
ENTRYPOINT ["/usr/local/bin/entrypoint_mac.sh"]

# Clear the default command; it will be managed by the entrypoint script.
CMD []

# Switch to a non-root user for better security.
USER www-data

# Expose port 9000 for PHP-FPM service.
EXPOSE 9000