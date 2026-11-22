FROM php:8.3-cli-alpine
RUN apk add --no-cache bash git unzip libzip-dev oniguruma-dev libxml2-dev \
    && docker-php-ext-install mbstring zip dom
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html
EXPOSE 8000
