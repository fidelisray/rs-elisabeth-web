#!/bin/sh
set -e

echo "================================================================"
echo " RS Elisabeth Web — Container Bootstrap"
echo "================================================================"

echo "[1/8] Caching application configuration..."
php artisan config:cache

echo "[2/8] Caching routes..."
php artisan route:cache

echo "[3/8] Caching views..."
php artisan view:cache

echo "[4/8] Running database migrations..."
php artisan migrate --force

echo "[5/8] Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "[6/8] Fetch and Cache Doctor Data..."
php artisan dokter:fetch-all || true

echo "[7/8] Fetch and Cache Glossary Data..."
php artisan glossary:refresh || true

echo "[8/8] Syncing public assets to shared volume for Nginx..."
mkdir -p /var/www/html/public_shared
cp -rp /var/www/html/public/. /var/www/html/public_shared/

touch /tmp/app_healthy

echo "================================================================"
echo " Bootstrap complete! Starting PHP-FPM..."
echo "================================================================"

exec "$@"
