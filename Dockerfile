FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install \
        pdo_mysql \
        opcache \
        intl \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.interned_strings_buffer=8"; \
    echo "opcache.max_accelerated_files=10000"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.save_comments=1"; \
} > /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/html

COPY composer.json composer.lock* ./

RUN composer install --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p var/cache var/log public_volume \
    && chown -R www-data:www-data var

COPY docker/app/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
