FROM php:8.3-cli-alpine

RUN apk add --no-cache postgresql-libs postgresql-dev \
    && docker-php-ext-install pdo_pgsql bcmath \
    && apk del postgresql-dev

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN php artisan config:cache && php artisan route:cache

RUN chmod -R 777 storage bootstrap/cache

EXPOSE $PORT

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=$PORT
