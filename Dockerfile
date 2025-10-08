# Imagen que ya tiene PHP + Nginx + extensiones comunes
FROM webdevops/php-nginx:8.3-alpine AS base

# Solo instalar lo que necesitamos adicional
RUN apk add --no-cache mysql-client git

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar y instalar dependencias de Composer
COPY composer.json ./
RUN composer install --no-dev --no-scripts --optimize-autoloader

# Copiar código fuente
COPY . .

# Configurar permisos
RUN chown -R application:application /app \
    && chmod -R 755 storage bootstrap/cache

# Configurar Nginx
ENV WEB_DOCUMENT_ROOT=/app/public

EXPOSE 80

# Etapa de desarrollo
FROM base AS development

# Instalar dependencias de desarrollo
RUN composer install --dev

# Instalar Xdebug (rápido con imagen precompilada)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

# Configurar git
RUN git config --global --add safe.directory /app

# Etapa de producción
FROM base AS production

# Cachear configuraciones de Laravel
RUN php artisan config:cache || true \
    && php artisan route:cache || true \
    && php artisan view:cache || true