FROM php:8.3-fpm-alpine AS base

# Install system dependencies and PHP extensions
RUN apk add --no-cache \
    curl \
    git \
    libpng-dev \
    libxml2-dev \
    mysql-client \
    nginx \
    oniguruma-dev \
    unzip \
    zip

RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json ./
RUN composer install --no-dev --no-scripts --no-autoloader --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache \
    && mkdir -p /var/log/supervisor \
    && composer dump-autoload --optimize

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/default.conf /etc/nginx/http.d/default.conf

COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]

FROM base AS development

RUN composer install --dev

# Enable Xdebug for development
RUN apk add --no-cache "$PHPIZE_DEPS" linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug

# Fix git ownership issue
RUN git config --global --add safe.directory /var/www/html

COPY docker/php-dev.ini /usr/local/etc/php/conf.d/99-dev.ini

FROM base AS production

RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true

COPY docker/php-prod.ini /usr/local/etc/php/conf.d/99-prod.ini