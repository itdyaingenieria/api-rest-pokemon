#!/bin/bash

# Pokemon API Docker Setup Script
# Run this script to set up the Docker environment

echo "🐳 Setting up Pokemon API Docker environment..."

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📁 Creating .env file from .env.docker template..."
    cp .env.docker .env
    echo "✅ .env file created. Please edit it with your settings."
else
    echo "ℹ️  .env file already exists."
fi

# Build and start containers
echo "🔨 Building Docker containers..."
docker-compose build

echo "🚀 Starting containers..."
docker-compose up -d

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Install dependencies and set up Laravel
echo "📦 Installing Composer dependencies..."
docker-compose exec app composer install

echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

echo "🔐 Generating JWT secret..."
docker-compose exec app php artisan jwt:secret

echo "🗄️  Running database migrations..."
docker-compose exec app php artisan migrate

echo "🧹 Clearing caches..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan cache:clear

echo ""
echo "🎉 Pokemon API Docker environment is ready!"
echo ""
echo "📍 Available endpoints:"
echo "   API: http://localhost:8000"
echo "   phpMyAdmin: http://localhost:8080"
echo ""
echo "🔧 Useful commands:"
echo "   docker-compose logs app     # View application logs"
echo "   docker-compose exec app bash # Access application container"
echo "   docker-compose down         # Stop all containers"
echo ""