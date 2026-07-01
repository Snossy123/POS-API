#!/bin/bash
set -e

cd /var/www/html

until php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" > /dev/null 2>&1; do
  echo "Waiting for MySQL..."
  sleep 2
done

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
  php artisan key:generate --force
fi

php artisan migrate --force

mkdir -p storage/app/license
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

exec apache2-foreground
