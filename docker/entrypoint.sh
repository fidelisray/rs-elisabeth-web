#!/bin/sh
# ==============================================================================
# docker/entrypoint.sh — RS Elisabeth Web Bootstrap Script
#
# Script ini dijalankan SETIAP KALI container start (sebelum php-fpm).
# Tugas: cache konfigurasi, migrasi database, dan buat symlink storage.
# ==============================================================================
set -e

echo "================================================================"
echo " RS Elisabeth Web — Container Bootstrap"
echo "================================================================"

# 1. Cache konfigurasi Laravel untuk performa optimal
echo "[1/8] Caching application configuration..."
php artisan config:cache

echo "[2/8] Caching routes..."
php artisan route:cache

echo "[3/8] Caching views..."
php artisan view:cache

# 2. Jalankan migrasi database (--force wajib untuk non-interactive/production)
echo "[4/8] Running database migrations..."
php artisan migrate --force

# 3. Buat symlink storage (ignore error jika sudah ada atau disk=s3)
echo "[5/8] Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

echo "[6/8] Fetch and Cache Doctor Data..."
php artisan dokter:fetch-all || true

echo "[7/8] Fetch and Cache Glossary Data..."
php artisan glossary:refresh || true

# 4. Sync seluruh isi public/ ke shared volume agar Nginx bisa menyajikannya.
#    Ini krusial agar file hasil build Vite (CSS/JS) tersedia untuk Nginx.
echo "[8/8] Syncing public assets to shared volume for Nginx..."
mkdir -p /var/www/html/public_shared
cp -rp /var/www/html/public/. /var/www/html/public_shared/

# Sentinel file: menandai bahwa bootstrap sudah selesai.
# Digunakan oleh healthcheck di docker-compose.yml agar Nginx baru
# dinyatakan siap setelah seluruh inisialisasi selesai.
touch /tmp/app_healthy

echo "================================================================"
echo " Bootstrap complete! Starting PHP-FPM..."
echo "================================================================"

# Gantikan proses ini dengan command yang diberikan (php-fpm)
exec "$@"
