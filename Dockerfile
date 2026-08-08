# DigiStar Booking Suite — production image.
#
# Deliberately explicit rather than relying on build auto-detection: this app
# has a package.json (Vite) that no Blade view actually uses, so an inferred
# build would spend time on a JS bundle that is never loaded — and fail the
# deploy if that step errors. Everything the browser needs is either in
# public/assets or on a CDN.

FROM php:8.2-apache

# --- System libraries needed by the PHP extensions below -------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libonig-dev \
        libxml2-dev \
    && rm -rf /var/lib/apt/lists/*

# --- PHP extensions -------------------------------------------------------
# pdo_mysql: database. gd: dompdf image rendering for approval letters.
# mbstring/xml: Laravel + dompdf. zip: composer. bcmath/exif: framework deps.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        mbstring \
        gd \
        zip \
        bcmath \
        exif \
        opcache

# Laravel serves from public/, and needs mod_rewrite for its front controller.
RUN a2enmod rewrite \
    && sed -ri 's!DocumentRoot /var/www/html!DocumentRoot /var/www/html/public!g' \
        /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>\n' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install dependencies first so the layer caches when only app code changes.
# Artisan scripts are skipped here because .env does not exist yet at this point.
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

ENTRYPOINT ["entrypoint"]
