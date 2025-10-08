@echo off
REM Pokemon API Docker Setup Script for Windows
REM Run this script to set up the Docker environment

echo 🐳 Setting up Pokemon API Docker environment...

REM Check if .env file exists and create if needed
if not exist .env (
    if exist .env.example (
        echo 📁 Creating .env file from .env.example...
        copy .env.example .env
        echo ⚠️  Please edit .env file with your database settings before continuing
        echo    Default settings should work for Docker environment
    ) else (
        echo ❌ No .env.example found. Please create .env file manually
        pause
        exit /b 1
    )
) else (
    echo ℹ️  .env file already exists
)

REM Stop any running containers
echo 🛑 Stopping any running containers...
docker-compose down

REM Build containers
echo 🔨 Building Docker containers...
docker-compose build --no-cache
if errorlevel 1 (
    echo ❌ Failed to build containers
    pause
    exit /b 1
)

REM Start database first
echo 🗄️  Starting database...
docker-compose up -d db

REM Wait for database to be ready
echo ⏳ Waiting for database to be ready...
timeout /t 20 /nobreak > nul

REM Start application
echo 🚀 Starting application...
docker-compose up -d app

REM Wait for app to fully start
echo ⏳ Waiting for application to fully start...
timeout /t 15 /nobreak > nul

REM Install dependencies and set up Laravel
echo 📦 Installing Composer dependencies...
docker-compose exec app composer install --no-interaction
if errorlevel 1 (
    echo ❌ Failed to install composer dependencies
    echo � Checking logs...
    docker compose logs app
    pause
    exit /b 1
)

echo �🔑 Generating application key...
docker-compose exec app php artisan key:generate --force

echo 🔐 Generating JWT secret...
docker-compose exec app php artisan jwt:secret --force

echo 🗄️  Running database migrations...
docker-compose exec app php artisan migrate --force

echo 🧹 Clearing caches...
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan cache:clear

REM Start phpMyAdmin
echo 🔧 Starting phpMyAdmin...
docker compose up -d phpmyadmin

echo.
echo 🎉 Pokemon API Docker environment is ready!
echo.
echo 📍 Available endpoints:
echo    🌐 API: http://localhost:8000
echo    🗄️  phpMyAdmin: http://localhost:8080
echo    📊 API Health: http://localhost:8000/api/health (if available)
echo.
echo 🔧 Useful commands:
echo    docker-compose logs app           - View application logs
echo    docker-compose logs db            - View database logs
echo    docker-compose exec app bash      - Access application container
echo    docker-compose down               - Stop all containers
echo    docker-compose ps                 - Show container status
echo.
echo 🐛 Troubleshooting:
echo    If containers keep restarting, check logs with: docker-compose logs
echo    To rebuild: docker-compose down && docker-compose build --no-cache && setup-docker.bat
echo.

pause