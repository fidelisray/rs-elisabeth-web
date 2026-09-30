# ==============================================================================
# STAGE 0: Node.js Builder — Build Frontend Assets (Vite)
# Menggunakan dedicated stage agar Node.js & node_modules TIDAK ikut
# masuk ke final image PHP, menghemat ratusan MB.
# ==============================================================================
FROM node:20-alpine AS node_builder

WORKDIR /app

# Copy manifest files dulu untuk memanfaatkan Docker layer caching.
# Layer ini hanya rebuild jika package.json atau package-lock.json berubah.
COPY package*.json ./
RUN npm ci --frozen-lockfile

# Salin sisa source code & build assets untuk production
COPY . .
RUN npm run build

# ==============================================================================
# STAGE 1: PHP-FPM Application Server
# Menggunakan Alpine untuk image yang jauh lebih kecil vs Debian.
# ==============================================================================
FROM php:8.4-fpm-alpine AS fpm_app

LABEL maintainer="RS Elisabeth" \
      description="PHP-FPM Application Image for RS Elisabeth Web Frontend"

# 1. Install Alpine system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    zlib-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libwebp-dev \
    bash

# 2. Configure & Install PHP Extensions
# opcache: WAJIB untuk production, meningkatkan performa PHP secara signifikan
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install pdo_pgsql mbstring exif pcntl bcmath gd intl zip opcache \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# 3. Install Composer dari official image
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 4. Set working directory
WORKDIR /var/www/html

# 5. OPTIMASI LAYER CACHE: Copy manifest Composer dulu.
#    Layer ini hanya rebuild jika composer.json atau composer.lock berubah,
#    bukan setiap kali ada perubahan file .php atau .blade.php.
COPY composer.json composer.lock ./

# 6. Install PHP dependencies (tanpa scripts artisan — source belum di-copy)
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

# 7. Salin seluruh source code aplikasi
#    File yang ada di .dockerignore (vendor, node_modules, .env, dll) TIDAK disalin
COPY . .

# 8. Salin hasil build Vite dari Stage 0 (hanya folder public/build)
COPY --from=node_builder /app/public/build /var/www/html/public/build

# 9. Re-generate autoloader & jalankan artisan post-install scripts
#    APP_KEY dummy sebagai ARG (bukan ENV) agar tidak tersimpan di layer image
#    dan tidak memicu warning Docker Scout. Key ASLI di-inject via env_file saat runtime.
ARG BUILD_APP_K="base64:ZG9ja2VyYnVpbGRrZXkxMjM0NTY3ODkwYWJjZGVmZ2g="
RUN APP_K="${BUILD_APP_K}" composer dump-autoload --optimize \
    && APP_K="${BUILD_APP_K}" php artisan package:discover --ansi

# 10. Set permissions untuk folder-folder krusial Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# 11. Salin & set permission entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# PHP-FPM listen di port 9000
EXPOSE 9000

ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]

