# Use PHP 8.2 as the base image
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    default-mysql-client

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install application dependencies
RUN composer install

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create wait-for-db script
RUN echo '#!/bin/sh\n\
set -e\n\
host="$1"\n\
shift\n\
cmd="$@"\n\
until mysql -h "$host" -u "$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1"; do\n\
  >&2 echo "MySQL is unavailable - sleeping"\n\
  sleep 1\n\
done\n\
>&2 echo "MySQL is up - executing command"\n\
exec $cmd' > /usr/local/bin/wait-for-db.sh \
    && chmod +x /usr/local/bin/wait-for-db.sh

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
