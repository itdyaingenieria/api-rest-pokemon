FROM webdevops/php-nginx:8.3-alpine

WORKDIR /app

ENV WEB_DOCUMENT_ROOT=/app/public

COPY composer.json ./

RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . /app

RUN composer dump-autoload --optimize \
    && chown -R application:application /app \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 80