# Используем PHP 8.3 с FPM
FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libpq-dev \
    curl \
    nginx \
    librdkafka-dev \ 
    && docker-php-ext-install pdo pdo_pgsql sockets \
    && pecl install rdkafka \
    && docker-php-ext-enable rdkafka \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN pecl install redis && docker-php-ext-enable redis
# Рабочая директория
WORKDIR /var/www

# Копируем код проекта
COPY . .

# Устанавливаем зависимости Laravel
RUN composer install --no-interaction --optimize-autoloader

# Открываем порт 80
EXPOSE 80

# Запускаем nginx и php-fpm
CMD service nginx start && php-fpm
