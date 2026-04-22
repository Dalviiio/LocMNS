#!/bin/sh
set -e

# Copy public assets to shared volume (only if not already populated)
if [ -z "$(ls -A /var/www/html/public_volume 2>/dev/null)" ]; then
    cp -r /var/www/html/public/. /var/www/html/public_volume/
fi

# Warm up Symfony cache if not already done
if [ ! -d /var/www/html/var/cache/prod ]; then
    php bin/console cache:warmup --env=prod --no-debug
fi

# Fix permissions on var/
chown -R www-data:www-data /var/www/html/var

exec php-fpm
