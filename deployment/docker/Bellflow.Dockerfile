FROM composer:2 AS composer_deps

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . /app
RUN composer dump-autoload --optimize --classmap-authoritative

FROM node:20-alpine AS frontend_assets

WORKDIR /app

COPY package.json package-lock.json* ./
RUN npm install

COPY . /app
RUN npm run build

FROM invoiceninja/invoiceninja:latest

WORKDIR /var/www/app

COPY . /var/www/app
COPY --from=composer_deps /app/vendor /var/www/app/vendor
COPY --from=frontend_assets /app/public/build /var/www/app/public/build

RUN php artisan optimize:clear || true
