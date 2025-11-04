# 🔄 MLUC Sentinel - Backup & Restore Documentation

Complete guide for backing up and restoring your MLUC Sentinel application data.

---

## 📋 Table of Contents

1. [Overview](#overview)
2. [Backup Commands](#backup-commands)
3. [Automated Backups](#automated-backups)
4. [Restore Procedures](#restore-procedures)
5. [Backup Locations](#backup-locations)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## 🎯 Overview

The MLUC Sentinel backup system provides:
- **Database backups** (MySQL dumps)
- **File backups** (user uploads, stickers, receipts)
- **Automated daily backups** (scheduled at 2:00 AM)
- **Backup rotation** (30-day retention + monthly archives)
- **Easy restore** functionality
- **Comprehensive logging** of all backup operations

### What Gets Backed Up?

**Database:**
- All tables and data
- User accounts, vehicles, payments
- Reports, violations, patrol logs
- Complete database structure

**Files:**
- `storage/app/public/` - User uploads, stickers, receipts, QR codes
- `public/storage/` - Public storage symlink files

---

## 💾 Backup Commands

### 1. Database Backup

Create a manual database backup:

```bash
php artisan backup:database
```

Create a named backup:

```bash
php artisan backup:database --name=before-update
```

**Output:**
```
Starting database backup...
✓ Database backup created successfully!
  Location: E:\Users\Conan\Documents\system\mluc-sentinel\storage\app\backups\database\backup-2024-11-02_14-30-00.sql
  Size: 2.45 MB
```

---

### 2. Files Backup

Create a manual files backup:

```bash
php artisan backup:files
```

Create a named files backup:

```bash
php artisan backup:files --name=files-before-update
```

**Output:**
```
Starting files backup...
Backing up: storage/app/public
Backing up: public/storage
✓ Files backup created successfully!
  Location: E:\Users\Conan\Documents\system\mluc-sentinel\storage\app\backups\files\files-backup-2024-11-02_14-35-00.zip
  Files: 1523
  Size: 156.78 MB
```

---

### 3. Cleanup Old Backups

Remove backups older than 30 days:

```bash
php artisan backup:cleanup
```

Custom retention period (e.g., 60 days):

```bash
php artisan backup:cleanup --days=60
```

Keep monthly archives:

```bash
php artisan backup:cleanup --days=30 --keep-monthly
```

**Output:**
```
Starting backup cleanup...
Cleaning database backups (older than 30 days)...
Cleaning files backups (older than 30 days)...
✓ Cleanup completed!
  Deleted: 15 old backups
  Kept: 30 recent backups
  Monthly archives: 3
```

---

## ⏰ Automated Backups

Backups run automatically every day via Laravel's task scheduler:

| Time | Command | Description |
|------|---------|-------------|
| **2:00 AM** | `backup:database` | Daily database backup |
| **2:30 AM** | `backup:files` | Daily files backup |
| **3:00 AM** | `backup:cleanup` | Remove old backups (30+ days, keep monthly) |

### Starting the Scheduler

**For Development (Manual):**
```bash
php artisan schedule:work
```

**For Production (Windows Task Scheduler):**

1. Open **Task Scheduler**
2. Create a new task:
   - **Name:** Laravel Scheduler - MLUC Sentinel
   - **Trigger:** Daily at 1:55 AM
   - **Action:** Start a program
     - **Program:** `C:\xampp\php\php.exe`
     - **Arguments:** `E:\Users\Conan\Documents\system\mluc-sentinel\artisan schedule:run`
   - **Start in:** `E:\Users\Conan\Documents\system\mluc-sentinel`

**For Production (Linux Cron):**
```bash
* * * * * cd /path/to/mluc-sentinel && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 Restore Procedures

### ⚠️ CRITICAL: Before Restoring

1. **Stop your application** (close browser, stop queue workers)
2. **Backup current state** (in case restore fails)
3. **Verify backup file** integrity
4. **Note the database backup password** (from `.env`)

---

### Restore Database

**Step 1: List available backups**

```bash
php artisan backup:restore
```

This will show available backups if no filename is provided:
```
Backup file not found: 
Available backups:
  - backup-2024-11-02_02-00-00.sql (2.45 MB) - 2024-11-02 02:00:15
  - backup-2024-11-01_02-00-00.sql (2.43 MB) - 2024-11-01 02:00:12
  - backup-2024-10-31_02-00-00.sql (2.41 MB) - 2024-10-31 02:00:18
```

**Step 2: Restore from backup**

```bash
php artisan backup:restore backup-2024-11-02_02-00-00.sql
```

**Step 3: Confirm restore**

```
WARNING: This will replace your current database with the backup!
Backup file: backup-2024-11-02_02-00-00.sql
Size: 2.45 MB
 Do you want to continue? (yes/no) [no]:
 > yes

Restoring database...
✓ Database restored successfully from backup-2024-11-02_02-00-00.sql
```

---

### Restore Files

Files backups are ZIP archives. To restore:

**Step 1: Locate backup**
```
E:\Users\Conan\Documents\system\mluc-sentinel\storage\app\backups\files\
```

**Step 2: Extract manually**
1. Navigate to backup folder
2. Extract the ZIP file (e.g., `files-backup-2024-11-02_02-30-00.zip`)
3. Copy extracted folders to your project root:
   - `storage/app/public/` → Project `storage/app/public/`
   - `public/storage/` → Project `public/storage/`

**Step 3: Set permissions** (if needed)
```bash
chmod -R 755 storage/app/public
```

---

## 📁 Backup Locations

### Directory Structure

```
storage/
└── app/
    └── backups/
        ├── database/
        │   ├── backup-2024-11-02_02-00-00.sql
        │   ├── backup-2024-11-01_02-00-00.sql
        │   └── ...
        └── files/
            ├── files-backup-2024-11-02_02-30-00.zip
            ├── files-backup-2024-11-01_02-30-00.zip
            └── ...
```

### Backup File Naming

- **Database:** `backup-YYYY-MM-DD_HH-MM-SS.sql`
- **Files:** `files-backup-YYYY-MM-DD_HH-MM-SS.zip`
- **Named:** `your-custom-name.sql` or `your-custom-name.zip`

---

## 📌 Best Practices

### 1. **Regular Backups**
- ✅ Keep automated backups enabled
- ✅ Create manual backup before major updates
- ✅ Test backups periodically

### 2. **Off-Site Storage**
- ❗ Copy critical backups to external drive or cloud storage
- ❗ Use Google Drive, Dropbox, or dedicated backup services
- ❗ Never rely on single location

### 3. **Retention Policy**
- ✅ Keep daily backups for 30 days
- ✅ Keep monthly archives indefinitely
- ✅ Store backups of major milestones separately

### 4. **Security**
- ❗ Backup files contain sensitive data
- ❗ Encrypt backup files if storing externally
- ❗ Limit access to backup directories

### 5. **Verification**
- ✅ Periodically test restore procedures
- ✅ Verify backup file sizes (should not be 0 bytes)
- ✅ Check logs for backup failures

---

## 🔧 Troubleshooting

### ❌ "mysqldump not found"

**Problem:** MySQL tools not in PATH

**Solution:**
1. Find your MySQL installation:
   - XAMPP: `C:\xampp\mysql\bin\`
   - WAMP: `C:\wamp64\bin\mysql\mysql8.0.XX\bin\`
2. Add to Windows PATH environment variable, or
3. Update path in `BackupDatabase.php` → `findMysqldump()` method

---

### ❌ "ZipArchive extension is not installed"

**Problem:** PHP ZIP extension not enabled

**Solution:**
1. Open `php.ini` (e.g., `C:\xampp\php\php.ini`)
2. Find line: `;extension=zip`
3. Remove semicolon: `extension=zip`
4. Restart Apache/Web Server

---

### ❌ Backup file is 0 bytes or very small

**Problem:** Backup command failed silently

**Solution:**
1. Check logs: `storage/logs/security.log`
2. Verify database credentials in `.env`
3. Test MySQL connection manually
4. Check disk space

---

### ❌ "Database restore failed"

**Problem:** Restore command errors

**Common Causes:**
1. Wrong backup file
2. Database credentials changed
3. MySQL not running
4. Insufficient permissions

**Solution:**
1. Check MySQL service is running
2. Verify `.env` database credentials
3. Try restoring to empty database first
4. Check restore logs: `storage/logs/security.log`

---

### ❌ Automated backups not running

**Problem:** Scheduler not running

**Solution:**

**For Development:**
```bash
php artisan schedule:work
```

**For Production:**
- Verify Windows Task Scheduler task is enabled
- Check task history for errors
- Manually run: `php artisan schedule:run`
- Check Laravel logs: `storage/logs/laravel.log`

---

## 📊 Monitoring Backups

### Check Last Backup

View security logs:
```bash
tail -50 storage/logs/security.log
```

Look for entries:
```
[2024-11-02 02:00:15] security.INFO: Database backup created {"filename":"backup-2024-11-02_02-00-00.sql"...}
[2024-11-02 02:30:22] security.INFO: Files backup created {"filename":"files-backup-2024-11-02_02-30-00.zip"...}
```

### Backup Size Monitoring

Check backup directory sizes:
```bash
# Windows PowerShell
Get-ChildItem -Path "storage\app\backups" -Recurse | Measure-Object -Property Length -Sum
```

---

## 🆘 Emergency Recovery

### If All Backups Are Lost

1. **Check:**
   - External drives
   - Cloud storage
   - Development machines
   - Other team members

2. **Database Recovery:**
   - Contact hosting provider for server-level backups
   - Check MySQL binary logs (if enabled)

3. **Prevention:**
   - Enable off-site backups immediately
   - Set up backup monitoring alerts

---

## 📞 Support

For backup-related issues:
1. Check this documentation
2. Review logs: `storage/logs/security.log`
3. Contact system administrator

---

## 🔐 Security Notes

- ✅ Backups are logged in `storage/logs/security.log`
- ✅ All database operations use environment credentials
- ✅ Restore operations require confirmation
- ⚠️ Keep `.env` file secure (contains database password)
- ⚠️ Restrict access to `storage/app/backups/` directory

---

**Last Updated:** November 2, 2024  
**Version:** 1.0  
**System:** MLUC Sentinel - Backup & Restore System

