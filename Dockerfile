# ============================================
# STAGE 1: COMPOSER BUILDER
# ============================================
FROM composer:2 AS composer-builder
RUN apk add --no-cache git
WORKDIR /app

ARG INSTALL_DEV_DEPENDENCIES=false

COPY composer.json composer.lock ./

RUN COMPOSER_INSTALL_FLAGS="--no-scripts --no-interaction --prefer-dist --optimize-autoloader"; \
    if [ "$INSTALL_DEV_DEPENDENCIES" != "true" ]; then \
    COMPOSER_INSTALL_FLAGS="$COMPOSER_INSTALL_FLAGS --no-dev"; \
    fi; \
    echo "Composer install flags: $COMPOSER_INSTALL_FLAGS"; \
    composer install --ignore-platform-reqs $COMPOSER_INSTALL_FLAGS

COPY . .

RUN DUMP_AUTOLOAD_FLAGS="--optimize"; \
    if [ "$INSTALL_DEV_DEPENDENCIES" != "true" ]; then \
    DUMP_AUTOLOAD_FLAGS="$DUMP_AUTOLOAD_FLAGS --no-dev"; \
    fi; \
    composer dump-autoload $DUMP_AUTOLOAD_FLAGS

# ============================================
# STAGE 2: NODE BUILDER
# ============================================
FROM node:24-alpine AS node-builder
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY resources ./resources
COPY vite.config.js ./
COPY postcss.config.js* ./
COPY tailwind.config.js* ./
COPY public ./public
RUN npm run build

# ============================================
# STAGE 3: PRODUCTION
# ============================================
FROM php:8.3-apache-bookworm AS final

# 1. Install System Dependencies
RUN set -ex; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
    libpq5 \
    libzip4 \
    dos2unix \
    postgresql-client \
    libjpeg62-turbo \
    libpng16-16 \
    libfreetype6 \
    libwebp7 \
    libjpeg-dev \
    libpng-dev \
    libfreetype-dev \
    libwebp-dev \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    git \
    unzip;

# 2. Configure and Install PHP Extensions
RUN set -ex; \
    docker-php-ext-configure gd \
    --with-freetype=/usr \
    --with-jpeg=/usr \
    --with-webp=/usr; \
    docker-php-ext-install -j$(nproc) \
    pdo pdo_pgsql mbstring exif pcntl bcmath gd zip intl; \
    php -m | grep -q gd || (echo "ERROR: GD extension failed to compile" && exit 1);

# 3. Install Redis PHP Extension via PECL
RUN pecl install redis && docker-php-ext-enable redis

# 4. Clean Up Build Dependencies
RUN set -ex; \
    apt-mark manual \
    libjpeg62-turbo \
    libpng16-16 \
    libfreetype6 \
    libwebp7 \
    postgresql-client; \
    apt-get purge -y --auto-remove \
    libjpeg-dev \
    libpng-dev \
    libfreetype-dev \
    libwebp-dev \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    git \
    unzip; \
    php -m | grep -q gd || (echo "ERROR: GD extension broken after cleanup" && exit 1); \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/*

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Configure Apache DocumentRoot
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf && \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html

# Copy built assets from builder stages
COPY --from=composer-builder /app/vendor ./vendor
COPY --from=node-builder /app/public/build ./public/build

ARG INSTALL_DEV_DEPENDENCIES=false
ENV INSTALL_DEV_DEPENDENCIES=$INSTALL_DEV_DEPENDENCIES

# Copy entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN dos2unix /usr/local/bin/docker-entrypoint.sh && \
    chmod +x /usr/local/bin/docker-entrypoint.sh

# Set permissions
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
