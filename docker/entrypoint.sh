#!/bin/bash
set -e

cd /var/www/html

attempt=0
max_attempts=60

until php -r "
  try {
    new PDO(
      'mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
      getenv('DB_USERNAME'),
      getenv('DB_PASSWORD')
    );
    exit(0);
  } catch (Throwable \$e) {
    file_put_contents('php://stderr', \$e->getMessage() . PHP_EOL);
    exit(1);
  }
" 2>/tmp/mysql-wait.err; do
  attempt=$((attempt + 1))
  echo "Waiting for MySQL... (attempt ${attempt}/${max_attempts})"
  if [ -f /tmp/mysql-wait.err ] && [ -s /tmp/mysql-wait.err ]; then
    echo "  Error: $(cat /tmp/mysql-wait.err)"
  fi

  if [ "$attempt" -ge "$max_attempts" ]; then
    echo "Failed to connect to MySQL."
    echo "Check DB_HOST, DB_PASSWORD (use quotes if password contains # or \$), and docker network."
    exit 1
  fi

  sleep 2
done

echo "MySQL connection established."

if [ -z "${APP_KEY:-}" ] || [ "${APP_KEY}" = "base64:" ]; then
  GENERATED_KEY="$(php artisan key:generate --show)"
  export APP_KEY="${GENERATED_KEY}"
  if [ -f .env ]; then
    if grep -q '^APP_KEY=' .env; then
      sed -i "s|^APP_KEY=.*|APP_KEY=${GENERATED_KEY}|" .env
    else
      echo "APP_KEY=${GENERATED_KEY}" >> .env
    fi
  else
    echo "APP_KEY=${GENERATED_KEY}" > .env
  fi
  echo "Generated APP_KEY for this container."
fi

php artisan migrate --force

if [ "${RUN_DB_SEED:-false}" = "true" ]; then
  php artisan db:seed --force || true
fi

mkdir -p storage/app/license storage/framework storage/logs storage/app/public bootstrap/cache

for dir in storage/framework storage/logs storage/app/public bootstrap/cache; do
  chown -R www-data:www-data "$dir"
  chmod -R 775 "$dir"
done

exec apache2-foreground
