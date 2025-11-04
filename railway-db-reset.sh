#!/bin/bash

echo "🗑️  Resetting Database - This will DROP ALL TABLES!"
echo "⚠️  This should only run ONCE on first deployment"
echo ""

# Drop all tables and re-run migrations with seeders
echo "📊 Running migrate:fresh (drops all tables and recreates)..."
php artisan migrate:fresh --force

echo ""
echo "🌱 Seeding database..."
php artisan db:seed --force
php artisan db:seed --class=UsersSeeder --force

echo ""
echo "✅ Database reset complete!"
echo ""
echo "🔑 Login credentials:"
echo "   Email: ademesa.dev@gmail.com"
echo "   Password: admin123"
echo ""

