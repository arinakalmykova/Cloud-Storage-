# PHP-FPM
FROM php:8.3-fpm

# Установите системные зависимости
RUN apt-get update && apt-get install -y \
    git zip unzip libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql sockets pcntl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Копируем только файлы composer для кэширования
COPY composer.json composer.lock ./

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Устанавливаем зависимости
RUN composer install --no-interaction --optimize-autoloader --no-scripts

# Копируем остальной код
COPY . .

# Запускаем скрипты после копирования всего кода
RUN composer run-script post-autoload-dump

CMD ["php-fpm"]