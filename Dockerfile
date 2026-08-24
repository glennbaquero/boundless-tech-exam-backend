# syntax=docker/dockerfile:1

FROM php:8.4-cli-alpine AS base
WORKDIR /var/www/html
RUN apk add --no-cache sqlite-libs unzip
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

FROM base AS vendor
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --no-progress \
    --optimize-autoloader

FROM base AS runtime
COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .

RUN php artisan package:discover --ansi \
    && chmod -R u+rwX storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENV PHP_CLI_SERVER_WORKERS=4 \
    PORT=8000

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
