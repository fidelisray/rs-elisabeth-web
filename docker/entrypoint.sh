#!/bin/sh
set -e

case "$1" in
    *supervisord*|php-fpm)
        ;;
    *)
        exec "$@"
        ;;
esac
rm -f /tmp/app_healthy

echo "================================================================"
echo " RS Elisabeth Web — Container Bootstrap"
echo "================================================================"

echo "[1/8] Caching application configuration..."
php artisan config:cache

echo "[2/8] Caching routes..."
php artisan route:cache

echo "[3/8] Caching views..."
php artisan view:cache

echo "[4/8] Database migrations are a separate deployment step."

echo "[5/8] Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "[6/8] Fetch and Cache Doctor Data..."
if [ "${BOOTSTRAP_EXTERNAL_WARMUP:-false}" = "true" ]; then
    php artisan dokter:fetch-all
else
    echo "Doctor warm-up skipped; run dokter:fetch-all explicitly when required."
fi

echo "[7/8] Fetch and Cache Glossary Data..."
if [ "${BOOTSTRAP_GLOSSARY_WARMUP:-false}" = "true" ]; then
    php artisan glossary:refresh
else
    echo "Local glossary validation skipped."
fi

echo "[8/8] Syncing public assets to shared volume for Nginx..."
mkdir -p /var/www/html/public_shared
cp -rp /var/www/html/public/. /var/www/html/public_shared/

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

touch /tmp/app_healthy

echo "================================================================"
echo " Bootstrap complete! Starting Supervisord (PHP-FPM & Scheduler)..."
echo "================================================================"

exec "$@"
