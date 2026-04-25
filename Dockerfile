FROM php:8.4-cli

RUN apt-get update && apt-get install -y git curl libpq-dev libzip-dev zip unzip libxml2-dev && docker-php-ext-install pdo pdo_pgsql pgsql zip mbstring

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
