FROM php:8.4-fpm

# Extensions + outils
RUN apt-get update && apt-get install -y \
    git unzip nginx \
    && docker-php-ext-install pdo pdo_mysql opcache \
    && rm -rf /var/lib/apt/lists/*

# OPcache optimisé
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.revalidate_freq=0'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.fast_shutdown=1'; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dépendances en premier (layer cache Docker)
COPY composer.json composer.lock symfony.lock ./
RUN COMPOSER_MEMORY_LIMIT=-1 composer install \
    --no-interaction \
    --no-scripts \
    --no-dev \
    --optimize-autoloader \
    --prefer-dist

# Reste du code
COPY . .

# Config nginx
COPY docker/nginx.conf /etc/nginx/sites-enabled/default

RUN mkdir -p var/cache/prod var/log \
    && chmod -R 777 var

EXPOSE 80

CMD ["sh", "-c", "chmod -R 777 /var/www/html/var && php-fpm -D && nginx -g 'daemon off;'"]
