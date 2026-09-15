# ---------------------------------------------------------------------------
# Portafolio de Reportes BI · imagen para la nube (Render, Railway, Fly.io…)
#
# Una sola imagen con PHP 8.4, las extensiones que usa Laravel y las
# dependencias ya instaladas. La base es SQLite dentro del contenedor: sirve
# para la demo en la nube; en producción real se apunta a MySQL por variables
# de entorno (DB_CONNECTION=mysql, DB_HOST…).
# ---------------------------------------------------------------------------
FROM php:8.4-cli-alpine

# Extensiones: SQLite para la demo, MySQL para producción, y las que pide Laravel.
# Los paquetes -dev solo hacen falta para compilar: se quitan al final.
RUN apk add --no-cache bash icu-libs libzip sqlite-libs \
    && apk add --no-cache --virtual .build-deps icu-dev libzip-dev sqlite-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_sqlite pdo_mysql intl zip opcache \
    && apk del .build-deps

# Composer desde su imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Primero solo composer.* para aprovechar la caché de capas
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && php artisan package:discover --ansi \
    && chmod +x docker/entrypoint.sh \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs database \
    && chown -R www-data:www-data storage bootstrap/cache database

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/app/database/database.sqlite \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    QUEUE_CONNECTION=sync \
    PHP_CLI_SERVER_WORKERS=8 \
    PORT=8080

EXPOSE 8080

ENTRYPOINT ["docker/entrypoint.sh"]
