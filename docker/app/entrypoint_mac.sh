#!/bin/sh
set -e

cd /var/www/html

# --- PASO 1: FIJAR PERMISOS ---
echo ">>> Setting ownership for storage and cache..."
chown -R www-data:www-data storage bootstrap/cache

# --- PASO 2: INSTALAR DEPENDENCIAS ---
if [ ! -d "vendor" ]; then
  echo ">>> Installing Composer dependencies..."
  composer install --no-interaction --no-progress --prefer-dist
fi

php artisan key:generate

echo ">>> Waiting for database..."
while ! mysqladmin ping -h"db" -u"${DB_USERNAME}" -p"${DB_PASSWORD}" --silent --ssl=0; do
    sleep 1
done
echo ">>> Database is ready."

php artisan migrate --force
php artisan optimize:clear

echo ">>> Setup complete. Starting PHP-FPM..."
exec php-fpm