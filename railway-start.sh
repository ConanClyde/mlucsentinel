#!/bin/bash

echo "🚀 Starting MLUC Sentinel on Railway..."

# Run post-deployment setup
echo "📦 Running deployment setup..."

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Run migrations
echo "📊 Running migrations..."
php artisan migrate --force

# Run seeders on first deploy
echo "🌱 Seeding database..."
php artisan db:seed --force
php artisan db:seed --class=UsersSeeder --force

# Clear and rebuild caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "💾 Building config cache..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Start Queue Worker in background
echo "⚙️ Starting queue worker..."
php artisan queue:work --daemon --tries=3 --timeout=90 &

# Start Reverb WebSocket server in background
echo "📡 Starting Reverb WebSocket server..."
php artisan reverb:start --host=0.0.0.0 --port=${REVERB_PORT:-8080} &

# Give background services time to start
sleep 2

# Start the web server
echo "🌐 Starting web server on port ${PORT:-8000}..."
php artisan serve --host=0.0.0.0 --port=${PORT:-8000}

