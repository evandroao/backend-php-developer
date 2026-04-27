FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql curl mbstring xml zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./

# RUN composer install --no-dev --optimize-autoloader
RUN composer install --optimize-autoloader --no-interaction

COPY . .

RUN mkdir -p /app/bootstrap/cache /app/storage \
    && chown -R www-data:www-data /app/bootstrap/cache /app/storage \
    && chmod -R 775 /app/bootstrap/cache /app/storage

# Otimizações do Laravel (config, rotas, views)
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Ferramentas para debug
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

EXPOSE 9012

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9012"]
