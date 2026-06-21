#!/usr/bin/env sh
set -eu

if [ ! -f .env ]; then
  cp .env.example .env
fi

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache

if [ -z "${APP_KEY:-}" ] && ! grep -q '^APP_KEY=base64:' .env; then
  php artisan key:generate --force --ansi
fi

php artisan config:clear --ansi
php artisan route:clear --ansi
php artisan migrate --force --ansi

exec php artisan serve --host=0.0.0.0 --port=8000
