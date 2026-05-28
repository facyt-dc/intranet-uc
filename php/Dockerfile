FROM php:8.2.31-fpm 

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
  git \
  unzip \
  zip \
  libzip-dev \
  libicu-dev

RUN docker-php-ext-install pdo pdo_mysql zip intl

ENV COMPOSER_ALLOW_SUPERUSER=1

COPY --from=composer:2.8.4 /usr/bin/composer /usr/bin/composer

COPY ./src/composer.* ./

RUN composer install --prefer-dist --no-scripts --no-progress --no-interaction

COPY ./src .

RUN composer dump-autoload --optimize

RUN mkdir -p database && touch database/database.sqlite

RUN chown -R www-data:www-data storage bootstrap/cache database

RUN chmod -R 775 storage bootstrap/cache database