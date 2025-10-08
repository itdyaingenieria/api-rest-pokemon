@echo off
REM Pokemon API Docker Setup Script for Windows
REM Run this script to set up the Docker environment

echo 🐳 Setting up Pokemon API Docker environment...

REM Copy environment file if it doesn't exist
if not exist .env (
    echo 📁 Creating .env file from .env.docker template...
    copy .env.docker .env
    echo ✅ .env file created. Please edit it with your settings.
) else (
    echo ℹ️  .env file already exists.
)

REM Build and start containers
echo 🔨 Building Docker containers...
docker-compose build

echo 🚀 Starting containers...
docker-compose up -d

REM Wait for database to be ready
echo ⏳ Waiting for database to be ready...
timeout /t 10 /nobreak > nul

REM Install dependencies and set up Laravel
echo 📦 Installing Composer dependencies...
docker-compose exec app composer install

echo 🔑 Generating application key...
docker-compose exec app php artisan key:generate

echo 🔐 Generating JWT secret...
docker-compose exec app php artisan jwt:secret

echo 🗄️  Running database migrations...
docker-compose exec app php artisan migrate

echo 🧹 Clearing caches...
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan cache:clear

echo.
echo 🎉 Pokemon API Docker environment is ready!
echo.
echo 📍 Available endpoints:
echo    API: http://localhost:8000
echo    phpMyAdmin: http://localhost:8080
echo.
echo 🔧 Useful commands:
echo    docker-compose logs app     - View application logs
echo    docker-compose exec app bash - Access application container
echo    docker-compose down         - Stop all containers
echo.

pause