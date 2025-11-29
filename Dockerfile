FROM php:8.4-fpm

# --- Install System Dependencies ---
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    librabbitmq-dev \
    libssh-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip mysqli bcmath

# --- Install Composer ---
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# --- Set Working Directory ---
WORKDIR /var/www/html

# --- Copy Laravel Source Code ---
COPY . .

# --- Install Dependencies ---
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader

# --- Laravel Setup ---
RUN php artisan key:generate --force || true

# Optimize for production
RUN php artisan config:clear || true && \
    php artisan route:clear || true && \
    php artisan view:clear || true && \
    php artisan config:cache || true && \
    php artisan route:cache || true

# --- Permissions ---
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose PHP-FPM port
EXPOSE 9000

CMD ["php-fpm"]
