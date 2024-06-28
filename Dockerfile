# Use the official Symfony image as a base
FROM php:8.2-fpm

# Set working directory
WORKDIR /app

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application code
COPY . /app

# Install dependencies
RUN composer install --no-scripts --no-autoloader

# Run Composer scripts
RUN composer dump-autoload --optimize

# Expose port 8000
EXPOSE 8000

# Run database setup and migrations
# RUN php bin/console doctrine:database:create --if-not-exists --no-interaction
# RUN php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing

# Start the Symfony server
CMD ["symfony", "serve", "--no-tls"]