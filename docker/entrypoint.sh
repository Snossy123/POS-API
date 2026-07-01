#!/bin/bash
set -e

cd /var/www/html

export COMPOSER_PROCESS_TIMEOUT=600

if [ ! -f vendor/autoload.php ]; then
  if ! composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --prefer-source; then
    composer update --no-dev --optimize-autoloader --no-interaction --no-scripts --prefer-source
  fi
fi

until php -r "new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD'));" > /dev/null 2>&1; do
  echo "Waiting for MySQL..."
  sleep 2
done

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  php artisan key:generate --force
fi

php artisan migrate --force
php artisan db:seed --force || true

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

exec apache2-foreground
