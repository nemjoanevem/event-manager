set -e

cd /var/www

if [ ! -d "vendor" ]; then
  composer install
fi

if [ ! -f ".env" ]; then
  cp .env.example .env
fi

if ! grep -q "^APP_KEY=" .env || grep -q "^APP_KEY=$" .env; then
  php artisan key:generate
fi

php artisan migrate --force
php artisan db:seed --force || true

exec "$@"
