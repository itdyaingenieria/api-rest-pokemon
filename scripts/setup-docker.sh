#!/bin/bash

# Pokemon API Docker Setup Script
# Run this script to set up the Docker environment

echo "🐳 Setting up Pokemon API Docker environment..."

# Function to check if container is running
check_container() {
    local container_name=$1
    local max_attempts=30
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if docker-compose ps | grep -q "${container_name}.*running"; then
            echo "✅ ${container_name} is running"
            return 0
        fi
        echo "⏳ Waiting for ${container_name}... (attempt ${attempt}/${max_attempts})"
        sleep 2
        attempt=$((attempt + 1))
    done
    
    echo "❌ ${container_name} failed to start"
    return 1
}

# Function to execute command with retry
exec_with_retry() {
    local cmd=$1
    local description=$2
    local max_attempts=3
    local attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        echo "🔧 ${description} (attempt ${attempt}/${max_attempts})"
        if eval $cmd; then
            echo "✅ ${description} completed successfully"
            return 0
        fi
        echo "⚠️  ${description} failed, retrying..."
        sleep 5
        attempt=$((attempt + 1))
    done
    
    echo "❌ ${description} failed after ${max_attempts} attempts"
    return 1
}

# Check if .env file exists and create if needed
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "📁 Creating .env file from .env.example..."
        cp .env.example .env
        echo "⚠️  Please edit .env file with your database settings before continuing"
        echo "   Default settings should work for Docker environment"
    else
        echo "❌ No .env.example found. Please create .env file manually"
        exit 1
    fi
else
    echo "ℹ️  .env file already exists"
fi

# Stop any running containers
echo "🛑 Stopping any running containers..."
docker-compose down

# Build containers
echo "🔨 Building Docker containers..."
if ! docker-compose build --no-cache; then
    echo "❌ Failed to build containers"
    exit 1
fi

# Start database first
echo "🗄️  Starting database..."
docker-compose up -d db

# Wait for database to be ready
if ! check_container "pokemon-mysql"; then
    echo "❌ Database failed to start"
    exit 1
fi

# Wait additional time for MySQL to initialize
echo "⏳ Waiting for MySQL to initialize..."
sleep 15

# Start application
echo "🚀 Starting application..."
docker-compose up -d app

# Wait for app to be ready
if ! check_container "pokemon-api"; then
    echo "❌ Application failed to start"
    echo "📋 Checking logs..."
    docker-compose logs app
    exit 1
fi

# Wait for app to fully start
echo "⏳ Waiting for application to fully start..."
sleep 10

# Install dependencies and set up Laravel
exec_with_retry "docker-compose exec app composer install --no-interaction" "Installing Composer dependencies"

exec_with_retry "docker-compose exec app php artisan key:generate --force" "Generating application key"

exec_with_retry "docker-compose exec app php artisan jwt:secret --force" "Generating JWT secret"

exec_with_retry "docker-compose exec app php artisan migrate --force" "Running database migrations"

# Clear caches
echo "🧹 Clearing caches..."
docker-compose exec app php artisan config:clear || true
docker-compose exec app php artisan route:clear || true
docker-compose exec app php artisan cache:clear || true

# Start phpMyAdmin
echo "🔧 Starting phpMyAdmin..."
docker-compose up -d phpmyadmin

echo ""
echo "🎉 Pokemon API Docker environment is ready!"
echo ""
echo "📍 Available endpoints:"
echo "   🌐 API: http://localhost:8000"
echo "   🗄️  phpMyAdmin: http://localhost:8080"
echo "   📊 API Health: http://localhost:8000/api/health (if available)"
echo ""
echo "🔧 Useful commands:"
echo "   docker-compose logs app           # View application logs"
echo "   docker-compose logs db            # View database logs"
echo "   docker-compose exec app bash      # Access application container"
echo "   docker-compose exec app php artisan tinker  # Laravel tinker"
echo "   docker-compose down               # Stop all containers"
echo "   docker-compose ps                 # Show container status"
echo ""
echo "🐛 Troubleshooting:"
echo "   If containers keep restarting, check logs with: docker-compose logs"
echo "   To rebuild from scratch: docker-compose down && docker-compose build --no-cache && ./scripts/setup-docker.sh"
echo ""