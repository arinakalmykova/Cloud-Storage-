# Используем официальный PHP 8.3 FPM
FROM php:8.3-fpm

# Устанавливаем зависимости для Imagick и другие нужные пакеты
RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    unzip \
    zip \
    git \
    curl \
    libpq-dev \
    sqlite3 \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install pdo pdo_pgsql sockets \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Рабочая директория
WORKDIR /var/www

# Копируем код проекта
COPY . .

# Устанавливаем зависимости Laravel
RUN composer install --no-interaction --optimize-autoloader

# Открываем порт 80
EXPOSE 80

# Запуск PHP-FPM
CMD ["php-fpm"]
