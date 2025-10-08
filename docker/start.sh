#!/bin/sh

# Start script for Pokemon API container
echo "Starting Pokemon API container..."

# Start PHP-FPM in background
echo "Starting PHP-FPM..."
php-fpm -D

# Start Nginx in foreground (keeps container alive)
echo "Starting Nginx..."
nginx -g "daemon off;"