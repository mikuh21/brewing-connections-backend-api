# Build the Vite frontend assets during the image build so production always serves
# the current resources/js and resources/css code instead of a stale committed bundle.
FROM node:22-alpine AS frontend

WORKDIR /frontend

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources

RUN npm run build


FROM php:8.4-cli

RUN apt-get update && apt-get install -y git curl libpq-dev libzip-dev zip unzip libxml2-dev libonig-dev && docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

COPY . .

# Replace any committed/stale frontend bundle with the bundle built from the
# current source during this Docker build.
COPY --from=frontend /frontend/public/build ./public/build

RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD ["/bin/sh", "-c", "php -S 0.0.0.0:${PORT:-8000} -t public"]
