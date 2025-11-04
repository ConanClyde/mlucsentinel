#!/bin/bash

echo "Running post-deployment tasks..."

# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Rebuild config cache
php artisan config:cache

echo "Post-deployment tasks completed!"

