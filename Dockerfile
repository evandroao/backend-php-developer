FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libcurl4-openssl-dev \
    libonig-dev \
    libzip-dev \
    libxml2-dev \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql curl mbstring xml zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock* ./

RUN composer install --optimize-autoloader --no-interaction --no-scripts

COPY . .

RUN composer dump-autoload --optimize

# Entrypoint para garantir pastas de cache/permissões na inicialização
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Ferramentas para debug
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

EXPOSE 9012

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=9012"]
