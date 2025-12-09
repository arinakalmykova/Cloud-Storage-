# PHP-FPM
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql sockets

WORKDIR /var/www

COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer install --no-interaction --optimize-autoloader

CMD ["php-fpm"]
