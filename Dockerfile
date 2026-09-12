FROM php:8.4-cli
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libzip-dev libicu-dev libonig-dev libsqlite3-dev && docker-php-ext-install pdo_mysql pdo_sqlite mbstring zip intl pcntl && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker-php.ini /usr/local/etc/php/conf.d/follo.ini
WORKDIR /app
COPY . .
RUN composer install --no-interaction --prefer-dist
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
