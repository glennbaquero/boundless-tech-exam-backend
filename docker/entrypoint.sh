#!/bin/sh
set -e

: "${DB_DATABASE:=/var/www/html/database/database.sqlite}"
: "${PORT:=8000}"

mkdir -p "$(dirname "$DB_DATABASE")"
touch "$DB_DATABASE"

php artisan config:cache
php artisan route:cache
php artisan migrate --force

exec php artisan serve --host=0.0.0.0 --port="$PORT" --no-reload
