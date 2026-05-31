# ─────────────────────────────────────────────
# Stage 1: Build frontend assets (Node.js)
# ─────────────────────────────────────────────
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package*.json ./
RUN npm ci --ignore-scripts

COPY resources/ resources/
COPY vite.config.js ./
COPY public/ public/

RUN npm run build

# ─────────────────────────────────────────────
# Stage 2: Build PHP application
# ─────────────────────────────────────────────
FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libxml2-dev \
    mysql-client \
    icu-dev \
    libzip-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        zip \
        intl \
        opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy composer files first (for layer caching)
COPY composer.json composer.lock ./

# Install PHP dependencies — update to pick up new packages not in lock file
RUN composer update --no-dev --no-interaction --optimize-autoloader --no-scripts

# Copy application source
COPY . .

# Copy built frontend assets from Stage 1
COPY --from=frontend /app/public/build public/build/

# Copy config files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY start.sh /start.sh

RUN chmod +x /start.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Create nginx temp directories with proper permissions
RUN mkdir -p /var/lib/nginx/tmp/fastcgi \
    && chown -R www-data:www-data /var/lib/nginx \
    && chmod -R 755 /var/lib/nginx

# Create mPDF temp directory
RUN mkdir -p /var/www/html/storage/app/mpdf_tmp \
    && chown -R www-data:www-data /var/www/html/storage/app/mpdf_tmp \
    && chmod -R 775 /var/www/html/storage/app/mpdf_tmp

# Run composer post-install scripts
RUN composer dump-autoload --optimize

# ── تثبيت خط Noto Naskh Arabic لـ DomPDF ──────────────────────────────────
# 1. أنشئ مجلد الخطوط
RUN mkdir -p /var/www/html/storage/fonts

# 2. حمّل ملفات TTF
RUN curl -fsSL \
    "https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoNaskhArabic/NotoNaskhArabic-Regular.ttf" \
    -o /var/www/html/storage/fonts/NotoNaskhArabic-Regular.ttf \
 && curl -fsSL \
    "https://github.com/googlefonts/noto-fonts/raw/main/hinted/ttf/NotoNaskhArabic/NotoNaskhArabic-Bold.ttf" \
    -o /var/www/html/storage/fonts/NotoNaskhArabic-Bold.ttf

# 3. سجّل الخط في DomPDF عبر سكريبت PHP مباشر
RUN php -r "
    define('DOMPDF_AUTOLOAD', false);
    require_once '/var/www/html/vendor/autoload.php';

    \$fontDir   = '/var/www/html/storage/fonts';
    \$cacheFile = \$fontDir . '/dompdf_font_family_cache.php';

    // اقرأ الـ cache الحالي إن وُجد
    \$cache = [];
    if (file_exists(\$cacheFile)) {
        \$cache = include \$cacheFile;
        if (!is_array(\$cache)) \$cache = [];
    }

    // سجّل الخط
    \$cache['noto naskh arabic'] = [
        'normal'      => \$fontDir . '/NotoNaskhArabic-Regular.ttf',
        'bold'        => \$fontDir . '/NotoNaskhArabic-Bold.ttf',
        'italic'      => \$fontDir . '/NotoNaskhArabic-Regular.ttf',
        'bold_italic' => \$fontDir . '/NotoNaskhArabic-Bold.ttf',
    ];

    // اكتب الـ cache
    file_put_contents(\$cacheFile, '<?php return ' . var_export(\$cache, true) . ';');
    echo 'Font registered: noto naskh arabic' . PHP_EOL;
"

# 4. صلاحيات
RUN chown -R www-data:www-data /var/www/html/storage/fonts \
 && chmod -R 755 /var/www/html/storage/fonts
# ───────────────────────────────────────────────────────────────────────────

EXPOSE 8080

CMD ["/start.sh"]
