#!/bin/bash

echo "🔧 Running post-deployment tasks..."

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Ensure storage directories exist with proper permissions
echo "📁 Setting up storage directories..."
mkdir -p storage/framework/{sessions,views,cache}
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Seed database (only on first deployment - uncomment if needed)
# echo "🌱 Seeding database..."
# php artisan db:seed --force
# php artisan db:seed --class=UsersSeeder --force

# Clear all caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan event:clear

# Rebuild caches for production
echo "💾 Building production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo "✅ Post-deployment tasks completed!"

