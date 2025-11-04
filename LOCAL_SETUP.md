# Local Development Setup Guide

## 🚨 Quick Fix: Your .env is Configured for Railway

Your local environment is trying to connect to Railway's database. Follow these steps:

---

## ✅ Step-by-Step Fix:

### 1. Update Your `.env` File

Open `.env` in your project root and find these lines:

```env
# CHANGE THIS:
DB_HOST=mysql.railway.internal

# TO THIS:
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_mlucsentinel
DB_USERNAME=root
DB_PASSWORD=
```

**Also update:**
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_HOST=127.0.0.1
VITE_REVERB_PORT=8080
VITE_REVERB_SCHEME=http
```

### 2. Create Local Database

#### Option A: Using phpMyAdmin
1. Open http://localhost/phpmyadmin
2. Click "New" in the left sidebar
3. Database name: `db_mlucsentinel`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"

#### Option B: Using MySQL Workbench
1. Open MySQL Workbench
2. Connect to your local MySQL
3. Run this SQL:
```sql
CREATE DATABASE IF NOT EXISTS db_mlucsentinel 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;
```

#### Option C: Using HeidiSQL
1. Open HeidiSQL
2. Right-click your connection → Create new → Database
3. Name: `db_mlucsentinel`
4. Collation: `utf8mb4_unicode_ci`

### 3. Clear Laravel Config Cache

```bash
php artisan config:clear
```

### 4. Run Migrations and Seeds

```bash
php artisan migrate:fresh --seed
```

This will:
- ✅ Create all 33 database tables
- ✅ Seed map location types
- ✅ Seed sticker counters
- ✅ Create admin user

### 5. Login!

```
Email: ademesa.dev@gmail.com
Password: admin123
```

---

## 🔄 Complete Local Setup Script

Or run this script (does steps 3-4):

```bash
.\setup-local-db.bat
```

---

## 📋 Expected Database Tables (33 Total)

After migration, you should have:

### Core
- users, cache, cache_locks, sessions
- jobs, failed_jobs, migrations
- password_reset_codes, personal_access_tokens

### Admin & Reference
- admin_roles, global_administrators, administrators
- colleges, programs
- stakeholder_types, reporter_types, violation_types
- vehicle_types, fees, map_location_types

### User Types
- staff, stakeholders, reporters, students, security

### Operations
- vehicles, reports, report_history
- sticker_counters, payments
- notifications, map_locations, patrol_logs
- audit_logs, activity_logs, idempotency_keys

---

## 🐛 Troubleshooting

### "Access denied for user 'root'@'localhost'"
**Problem:** MySQL password is wrong  
**Solution:** Update `.env` with correct password
```env
DB_PASSWORD=your_mysql_password
```

### "Unknown database 'db_mlucsentinel'"
**Problem:** Database doesn't exist  
**Solution:** Create it using Step 2 above

### "SQLSTATE[HY000] [2002] No connection"
**Problem:** MySQL service not running  
**Solution:** 
- XAMPP: Start MySQL in control panel
- WAMP: Start all services
- Laragon: Start services

### Still seeing Railway errors?
**Problem:** Config cache still pointing to old settings  
**Solution:**
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## 🎯 Quick Commands Reference

```bash
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=UsersSeeder

# Check migration status
php artisan migrate:status

# Start local server
php artisan serve

# Start Reverb
php artisan reverb:start

# Start Vite
npm run dev
```

---

## 💡 Keeping Railway Config Separate

### Option 1: Use Different .env Files
- `.env` - Local development
- `.env.railway` - Railway config (don't commit!)
- Use Railway variables tab for production

### Option 2: Environment Detection
Laravel automatically detects environment. Your code already has:
```php
if ($this->app->environment('production')) {
    \URL::forceScheme('https');
}
```

---

## ✅ Final Checklist

After setup, verify:
- [ ] `.env` has `DB_HOST=127.0.0.1`
- [ ] Database `db_mlucsentinel` exists
- [ ] `php artisan migrate:fresh --seed` runs successfully
- [ ] Can login with ademesa.dev@gmail.com / admin123
- [ ] All 33 tables exist in database

---

**Need help?** Check your database settings and make sure MySQL is running!

