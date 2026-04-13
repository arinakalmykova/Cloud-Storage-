FROM php:8.3-fpm

# Устанавливаем Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Добавляем backports репозиторий для свежих пакетов
RUN echo "deb http://deb.debian.org/debian bookworm-backports main" >> /etc/apt/sources.list

# Устанавливаем базовые зависимости
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libwebp-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    libpq-dev \
    librdkafka-dev \
    imagemagick \
    libmagickwand-dev \
    wget \
    gnupg \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_pgsql sockets \
    && pecl install redis rdkafka imagick \
    && docker-php-ext-enable redis rdkafka imagick \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Устанавливаем libavif-bin из backports (содержит avifenc)
RUN apt-get update && apt-get install -y -t bookworm-backports libavif-bin \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --optimize-autoloader

RUN chmod -R 777 storage

EXPOSE 80
CMD ["php-fpm"]