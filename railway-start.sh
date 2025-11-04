#!/bin/bash

echo "🚀 Starting MLUC Sentinel on Railway..."

# Run post-deployment setup
echo "📦 Running deployment setup..."

# Create storage link if it doesn't exist
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Check if DB_RESET=true is set (for manual reset)
if [ "$DB_RESET" = "true" ]; then
    echo "🗑️  DB_RESET=true detected - Resetting database..."
    php artisan migrate:fresh --force --seed
    php artisan db:seed --class=UsersSeeder --force
    echo "✅ Database reset complete!"
    echo "   Email: ademesa.dev@gmail.com"
    echo "   Password: admin123"
    echo "⚠️  IMPORTANT: Remove DB_RESET environment variable after this deployment!"
else
    # Check if this is first deployment (no migrations table or empty)
    MIGRATION_COUNT=$(php artisan migrate:status --no-ansi 2>/dev/null | grep -c "Ran" || echo "0")
    
    if [ "$MIGRATION_COUNT" = "0" ] || [ -z "$MIGRATION_COUNT" ]; then
        echo "🆕 First deployment detected - Running migrate:fresh with seed..."
        php artisan migrate:fresh --force --seed
        php artisan db:seed --class=UsersSeeder --force
        echo "✅ Database initialized with admin user!"
        echo "   Email: ademesa.dev@gmail.com"
        echo "   Password: admin123"
    else
        echo "📊 Running migrations..."
        php artisan migrate --force
        echo "✅ Migrations complete"
    fi
fi

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

