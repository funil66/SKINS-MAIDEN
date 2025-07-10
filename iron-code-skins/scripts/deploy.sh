#!/bin/bash

# Deploy script for Iron Code Skins platform

# Set environment variables
export APP_ENV=production
export APP_DEBUG=false
export APP_KEY=$(php artisan key:generate --show)

# Navigate to the backend directory
cd backend

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Run database migrations
php artisan migrate --force

# Clear and cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Navigate to the frontend directory
cd ../frontend

# Install JavaScript dependencies
npm install --production

# Build the frontend assets
npm run build

# Navigate to the infrastructure directory
cd ../infrastructure/docker

# Build and run Docker containers
docker-compose up -d --build

# Output deployment status
echo "Deployment completed successfully!"