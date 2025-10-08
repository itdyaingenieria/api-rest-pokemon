#!/bin/bash

# Pokemon API - Setup ULTRA SIMPLE
echo "🚀 Pokemon API - Inicio rápido"

# Limpiar todo
echo "🛑 Parando contenedores..."
docker-compose -f docker-compose.simple.yml down > /dev/null 2>&1

# Limpiar imágenes para forzar rebuild
echo "🧹 Limpiando cache..."
docker system prune -f > /dev/null 2>&1

# Construir sin cache para asegurar vendor/
echo "⚡ Build completo..."
docker-compose -f docker-compose.simple.yml build --no-cache

# Levantar servicios
echo "🚀 Iniciando servicios..."
docker-compose -f docker-compose.simple.yml up -d

# Esperar que esté listo
echo "⏳ Esperando servicios..."
sleep 30

# Verificar si vendor existe, sino instalarlo
echo "🔧 Verificando dependencias..."
if ! docker-compose -f docker-compose.simple.yml exec -T app test -d vendor; then
    echo "📦 Instalando dependencias..."
    docker-compose -f docker-compose.simple.yml exec -T app composer install --no-dev --optimize-autoloader
fi

# Configurar Laravel
echo "🔑 Configurando Laravel..."
docker-compose -f docker-compose.simple.yml exec -T app php artisan key:generate --force

# Limpiar cache por si hay problemas de Redis
echo "🗑️ Limpiando cache de Laravel..."
docker-compose -f docker-compose.simple.yml exec -T app php artisan config:clear
docker-compose -f docker-compose.simple.yml exec -T app php artisan cache:clear
docker-compose -f docker-compose.simple.yml exec -T app php artisan route:clear

# Ejecutar migraciones
docker-compose -f docker-compose.simple.yml exec -T app php artisan migrate --force

echo ""
echo "✅ ¡LISTO!"
echo "🌐 API: http://localhost:8000"
echo "🗄️ phpMyAdmin: http://localhost:8080"
echo ""
echo "🧪 Probar API:"
echo "curl http://localhost:8000/api/status"