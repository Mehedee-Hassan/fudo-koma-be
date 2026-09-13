FROM php:8.4-fpm AS php-base
RUN apt-get update && apt-get install -y --no-install-recommends libonig-dev libzip-dev libicu-dev unzip \
    && docker-php-ext-install pdo_mysql mbstring intl zip pcntl opcache \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /app

FROM php-base AS build
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY resources ./resources
COPY routes ./routes
COPY public ./public
COPY artisan ./artisan
RUN mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && composer dump-autoload --no-dev --optimize --no-interaction \
    && ln -s /app/storage/app/public public/storage

FROM php-base AS app
COPY --from=build --chown=www-data:www-data /app /app
COPY deploy/vps/php.ini /usr/local/etc/php/conf.d/zz-follo.ini
COPY deploy/vps/fpm.conf /usr/local/etc/php-fpm.d/zz-follo.conf
COPY --chmod=755 deploy/vps/entrypoint.sh /usr/local/bin/follo-entrypoint
USER www-data
ENTRYPOINT ["follo-entrypoint"]
CMD ["php-fpm", "-F"]

FROM nginx:stable-alpine AS web
COPY deploy/vps/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=build /app/public /app/public
