#!/bin/sh
set -e

APP_ENV="${APP_ENV:-prod}"
DB_HOST="${DB_HOST:-db}"
DB_USER="${DB_USER:-root}"
DB_PASSWORD="${DB_PASSWORD:-root}"
DB_NAME="${DB_NAME:-locmns}"

# Wait for MySQL to accept connections
echo "Waiting for MySQL..."
until mysqladmin ping -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl --silent 2>/dev/null; do
    sleep 1
done
echo "MySQL ready."

# Copy public assets to shared volume (only on first boot)
if [ -z "$(ls -A /var/www/html/public_volume 2>/dev/null)" ]; then
    cp -r /var/www/html/public/. /var/www/html/public_volume/
fi

# Run database migrations
php bin/console doctrine:migrations:migrate --no-interaction --env="${APP_ENV}"

# Seed demo data if database is empty
ROW_COUNT=$(mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl "${DB_NAME}" -sNe "SELECT COUNT(*) FROM utilisateur" 2>/dev/null || echo "0")
if [ "${ROW_COUNT}" = "0" ] && [ -f /var/www/html/docs/demo/seed.sql ]; then
    echo "Seeding demo data..."
    mysql -h"${DB_HOST}" -u"${DB_USER}" -p"${DB_PASSWORD}" --skip-ssl "${DB_NAME}" < /var/www/html/docs/demo/seed.sql
    echo "Seed done."
fi

# Warm up Symfony cache
if [ ! -d "/var/www/html/var/cache/${APP_ENV}" ]; then
    php bin/console cache:warmup --env="${APP_ENV}" --no-debug
fi

# Fix permissions
chown -R www-data:www-data /var/www/html/var

exec php-fpm
