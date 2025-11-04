# Database Setup & Management Guide

## 🚀 First Deployment (Automatic)

The system will **automatically detect** if this is the first deployment and run:
- ✅ `migrate:fresh` - Drop all tables and recreate
- ✅ Seed all reference data (location types, sticker counters)
- ✅ Create admin user

**No manual action needed!**

---

## 🔄 Force Database Reset

If you need to reset the database manually:

### Method 1: Using Environment Variable (Recommended)

1. Go to Railway → Your Service → **Variables**
2. Add: `DB_RESET=true`
3. Let Railway redeploy
4. **IMPORTANT:** After deployment completes, **remove** the `DB_RESET` variable
5. Redeploy again (without DB_RESET)

### Method 2: Using Railway CLI

```bash
# Install Railway CLI
npm i -g @railway/cli

# Login and link
railway login
railway link

# Reset database
railway run bash railway-db-reset.sh
```

### Method 3: Manual SQL Commands

Via Railway MySQL dashboard:
```sql
-- Drop all tables (CAUTION!)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS users, students, vehicles, reports, payments, etc;
SET FOREIGN_KEY_CHECKS = 1;
```

Then redeploy to recreate.

---

## 📋 Complete Table List

Your application requires these tables:

### Core Tables
- ✅ `users` - All user accounts
- ✅ `cache` - Application cache
- ✅ `cache_locks` - Cache locking
- ✅ `jobs` - Queue jobs
- ✅ `failed_jobs` - Failed queue jobs
- ✅ `sessions` - User sessions
- ✅ `password_reset_codes` - Password reset tokens
- ✅ `personal_access_tokens` - API tokens

### Admin & Access Control
- ✅ `admin_roles` - Admin role definitions
- ✅ `global_administrators` - Global admin records
- ✅ `administrators` - College-specific admins

### Reference Data
- ✅ `colleges` - College/department list
- ✅ `programs` - Academic programs
- ✅ `stakeholder_types` - Stakeholder categories
- ✅ `reporter_types` - Reporter categories
- ✅ `violation_types` - Violation categories
- ✅ `vehicle_types` - Vehicle type definitions
- ✅ `fees` - Fee structure
- ✅ `map_location_types` - Campus map location types

### User Types
- ✅ `staff` - Staff records
- ✅ `stakeholders` - Stakeholder records
- ✅ `reporters` - Reporter records
- ✅ `students` - Student records
- ✅ `security` - Security personnel records

### Operations
- ✅ `vehicles` - Registered vehicles
- ✅ `reports` - Violation reports
- ✅ `report_history` - Report status changes
- ✅ `sticker_counters` - Sticker number tracking
- ✅ `payments` - Payment records
- ✅ `notifications` - System notifications
- ✅ `map_locations` - Campus map locations
- ✅ `patrol_logs` - Security patrol check-ins
- ✅ `audit_logs` - System audit trail
- ✅ `activity_logs` - User activity logs
- ✅ `idempotency_keys` - Payment idempotency

---

## 🔑 Default Admin Credentials

After seeding, login with:

- **Email:** `ademesa.dev@gmail.com`
- **Password:** `admin123`

⚠️ **Change this password immediately after first login!**

---

## 🛠️ Common Database Commands

### Check Migration Status
```bash
railway run php artisan migrate:status
```

### Run Pending Migrations Only
```bash
railway run php artisan migrate --force
```

### Rollback Last Migration
```bash
railway run php artisan migrate:rollback --force
```

### Re-seed Without Dropping Tables
```bash
railway run php artisan db:seed --force
```

### View Database Tables
```bash
railway run php artisan tinker
# Then: DB::select('SHOW TABLES');
```

---

## 🔍 Troubleshooting

### "Table already exists" Error
**Cause:** Partial migration run  
**Solution:** Use Method 1 above (DB_RESET=true)

### "Base table or view not found"
**Cause:** Migration didn't complete  
**Solution:** Run `railway run php artisan migrate --force`

### "SQLSTATE[23000]: Integrity constraint violation"
**Cause:** Foreign key issues or missing parent records  
**Solution:** 
1. Check if all reference tables exist
2. Reset database using Method 1

### Can't Login with Admin Credentials
**Cause:** Seeder didn't run  
**Solution:** Run manually:
```bash
railway run php artisan db:seed --class=UsersSeeder --force
```

---

## 📊 Seeded Data

### Map Location Types
- Parking Zone (blue)
- Building (purple)
- Patrol Point (red)
- Gate/Entrance (green)
- Security Post (orange)
- Restricted Area (red)

### Sticker Counter Colors
- Blue, Green, Yellow, Pink, Orange, White, Maroon, Black

### Admin User
- Alvin de Mesa (Global Administrator)
- Email: ademesa.dev@gmail.com
- Password: admin123

---

## ⚡ Quick Reset Guide

**If your database is broken:**

1. Go to Railway Variables
2. Add `DB_RESET=true`
3. Wait for deployment
4. Remove `DB_RESET=true`
5. Login with admin credentials
6. Done! ✅

**All tables will be recreated and seeded automatically.**

