# Dockerfile ultra simple para Pokemon API
FROM webdevops/php-nginx:8.3-alpine

# Configurar directorio de trabajo
WORKDIR /app

# Configurar Nginx para servir desde /app/public
ENV WEB_DOCUMENT_ROOT=/app/public

# Copiar código fuente
COPY . /app

# Instalar dependencias y configurar permisos
RUN composer install --no-dev --optimize-autoloader \
    && chown -R application:application /app \
    && chmod -R 755 storage bootstrap/cache

EXPOSE 80