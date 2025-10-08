#!/bin/bash

# Pokemon API - Setup ULTRA SIMPLE
echo "🚀 Pokemon API - Inicio rápido"

# Limpiar todo
docker-compose -f docker-compose.simple.yml down > /dev/null 2>&1

# Construir y levantar (super rápido)
echo "⚡ Build rápido..."
docker-compose -f docker-compose.simple.yml up --build -d

# Esperar y configurar
echo "⏳ Configurando..."
sleep 25
docker-compose -f docker-compose.simple.yml exec -T app php artisan key:generate --force
docker-compose -f docker-compose.simple.yml exec -T app php artisan migrate --force

echo "✅ ¡LISTO!"
echo "🌐 API: http://localhost:8000"
echo "🗄️ phpMyAdmin: http://localhost:8080"