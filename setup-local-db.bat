@echo off
echo ========================================
echo Local Database Setup
echo ========================================
echo.

echo Step 1: Checking .env configuration...
echo.
echo Please manually update your .env file:
echo.
echo   DB_HOST=127.0.0.1
echo   DB_PORT=3306
echo   DB_DATABASE=db_mlucsentinel
echo   DB_USERNAME=root
echo   DB_PASSWORD=
echo.

echo Step 2: Creating database (via phpMyAdmin or MySQL client)...
echo.
echo Open phpMyAdmin or MySQL Workbench and run:
echo   CREATE DATABASE IF NOT EXISTS db_mlucsentinel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
echo.

pause

echo Step 3: Clearing Laravel config cache...
php artisan config:clear

echo Step 4: Running migrations and seeds...
php artisan migrate:fresh --seed

echo.
echo Step 5: Creating admin user...
php artisan db:seed --class=UsersSeeder --force

echo.
echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Login Credentials:
echo   Email: ademesa.dev@gmail.com
echo   Password: admin123
echo.
pause

