# ==============================================================================
# STAGE 0: Node.js Builder — Build Frontend Assets (Vite)
# Menggunakan dedicated stage agar Node.js & node_modules TIDAK ikut
# masuk ke final image PHP, menghemat ratusan MB.
# ==============================================================================
FROM node:22.23.4-alpine AS node_builder

WORKDIR /app

# Copy manifest files dulu untuk memanfaatkan Docker layer caching.
# Layer ini hanya rebuild jika package.json atau package-lock.json berubah.
COPY package*.json ./
RUN npm ci --frozen-lockfile

# Salin sisa source code & build assets untuk production
COPY . .
RUN npm run build

# ==============================================================================
# STAGE 1: PHP-FPM & Laravel Scheduler Application Server (Debian 12 Bookworm)
# Menggunakan Debian 12 (glibc) agar selaras dengan dukungan resmi Tier-1
# Microsoft ODBC Driver 18 (msodbcsql18) untuk koneksi read-only SQL Server.
# ==============================================================================
FROM php:8.4-fpm-bookworm AS fpm_app

LABEL maintainer="RS Elisabeth" \
      description="PHP-FPM & Scheduler Application Image for RS Elisabeth Web Frontend"

ENV DEBIAN_FRONTEND=noninteractive \
    TZ=Asia/Jakarta

# 1. Install Debian system dependencies, Supervisor, dan build libraries
RUN apt-get update && apt-get install -y --no-install-recommends \
    supervisor \
    curl \
    gnupg2 \
    ca-certificates \
    apt-transport-https \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxml2-dev \
    libzip-dev \
    libpq-dev \
    libicu-dev \
    libonig-dev \
    tzdata \
    && rm -rf /var/lib/apt/lists/*

# 2. Install Microsoft ODBC Driver 18 for SQL Server (repo resmi Microsoft Debian 12)
RUN curl -fsSL https://packages.microsoft.com/keys/microsoft.asc | gpg --dearmor -o /usr/share/keyrings/microsoft-prod.gpg \
    && curl -fsSL https://packages.microsoft.com/config/debian/12/prod.list > /etc/apt/sources.list.d/mssql-release.list \
    && apt-get update \
    && ACCEPT_EULA=Y apt-get install -y --no-install-recommends \
        msodbcsql18 \
        unixodbc-dev \
    && rm -rf /var/lib/apt/lists/*

# 3. Configure & Install Core PHP Extensions + pdo_sqlsrv & redis (PHP 8.4 compatible)
# opcache: WAJIB untuk production, meningkatkan performa PHP secara signifikan
# phpredis: C-extension berkecepatan tinggi untuk in-memory caching dan session
# pdo_sqlsrv: Driver PDO resmi SQL Server untuk referensi read-only dbo.ref_poli_online
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        zip \
        opcache

COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_sqlsrv redis \
    && rm -f /usr/local/bin/install-php-extensions \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "session.upload_progress.enabled = 0" > "$PHP_INI_DIR/conf.d/security-hardening.ini"

# 4. Install Composer dari official image (akan dihapus dari image runtime setelah autoload selesai)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 5. Copy konfigurasi Supervisord (PHP-FPM + Laravel Scheduler)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN mkdir -p /var/log/supervisor

# 6. Set working directory
WORKDIR /var/www/html

# 7. OPTIMASI LAYER CACHE: Copy manifest Composer dulu.
#    Layer ini hanya rebuild jika composer.json atau composer.lock berubah,
#    bukan setiap kali ada perubahan file .php atau .blade.php.
COPY composer.json composer.lock ./

# 8. Install PHP dependencies (tanpa scripts artisan karena source belum di-copy)
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

# 9. Salin seluruh source code aplikasi
#    File yang ada di .dockerignore (vendor, node_modules, .env, dll) TIDAK disalin
COPY . .

# 10. Salin hasil build Vite dari Stage 0 (hanya folder public/build)
COPY --from=node_builder /app/public/build /var/www/html/public/build

# 11. Re-generate autoloader, jalankan package:discover dengan dummy APP_KEY, lalu hapus binary composer
#     APP_KEY dummy sebagai ARG (bukan ENV) agar tidak tersimpan di layer image
#     dan tidak memicu warning Docker Scout. Key ASLI di-inject via env_file saat runtime.
ARG BUILD_APP_K="base64:ZG9ja2VyYnVpbGRrZXkxMjM0NTY3ODkwYWJjZGVmZ2g="
RUN APP_KEY="${BUILD_APP_K}" composer dump-autoload --optimize \
    && APP_KEY="${BUILD_APP_K}" php artisan package:discover --ansi \
    && rm -f /usr/bin/composer

# 12. Set permissions untuk folder-folder krusial Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 13. Salin entrypoint, pastikan line-ending LF (anti CRLF Windows), dan set executable
COPY docker/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh && chmod +x /entrypoint.sh

# PHP-FPM listen di port 9000
EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

