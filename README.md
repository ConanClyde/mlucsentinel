# MLUC Sentinel

> Campus Parking and Reporting Management System for Don Mariano Marcos Memorial State University - Mid La Union Campus

![Laravel](https://img.shields.io/badge/Laravel-12.35-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2.12-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.1.16-38B2AC?style=flat-square&logo=tailwind-css)

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [User Roles](#user-roles)
- [Architecture](#architecture)
- [Progressive Web App](#progressive-web-app)
- [Development](#development)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [License](#license)

## Overview

MLUC Sentinel is a comprehensive digital platform for campus parking management, violation reporting, and security patrol monitoring. The system replaces traditional paper-based parking permits with QR-enabled digital stickers and provides real-time violation reporting with evidence-based enforcement.

### Key Capabilities

- **Digital Parking Permits**: Automated vehicle registration with QR-enabled color-coded stickers
- **Smart Violation Reporting**: QR-based reporting with photo evidence and interactive map pinning
- **Security Patrol Tracking**: QR check-in system at strategic campus locations
- **Role-Based Access Control**: 8 administrator types with specific permissions
- **Real-Time Notifications**: WebSocket-powered instant updates for report status changes
- **Progressive Web App**: Installable app for mobile and desktop use
- **Payment Processing**: Sticker fee tracking and receipt generation

## Features

### Vehicle Management

- Vehicle registration for students, staff, security personnel, and stakeholders
- Automatic color-coded sticker assignment based on user type and plate number:
  - **Students**: Blue, Green, Yellow, Pink, or Orange (based on plate number last digit)
  - **Staff & Security**: Maroon
  - **Stakeholders**: White or Black (Guardian/Visitor vs Service Provider)
- QR-enabled SVG stickers linking to reporting page
- Batch sticker generation and download
- Vehicle limit enforcement (max 3 per user)
- Duplicate plate number validation

### Parking Sticker System

- Payment status tracking (Pending, Paid, Cancelled)
- PDF receipt generation with batch support
- Sticker request management for lost/damaged stickers
- Marketing admin-only sticker processing interface
- Transaction history and audit trail

### Violation Reporting

- QR code scanning for instant vehicle identification
- Manual entry option with sticker number search
- Photo evidence upload with file validation
- Interactive campus map with pin-drop location marking
- Violation type selection (multiple categories)
- Automatic administrator assignment:
  - Student violations: SAS (Student Affairs & Services) Admin
  - Non-student violations: Chancellor, Security, and Global Admins
- Status workflow: Pending → Approved/Rejected
- Real-time notifications for status changes
- Role-based reporter access:
  - SBO reporters can only report student violations
  - Faculty reporters can report all violations
  - VHE reporters can report all violations

### Security Patrol System

- QR-based location check-ins at patrol points
- GPS coordinate logging
- Patrol history with timestamps
- Security Admin monitoring dashboard
- Export functionality for patrol reports

### Interactive Campus Map

- SVG-based map with polygon location marking
- Drawing mode for adding new locations
- Short code assignment (max 5 characters)
- Location type categorization
- Map legend display
- QR sticker generation for patrol points
- Touch-enabled controls (pan, pinch-to-zoom)
- Responsive design with aspect-ratio based sizing

### User Management

- Comprehensive user management for 7 user types
- Role-based registration permissions:
  - Global Admin: Can register all user types including administrators
  - Security Admin: Can register students, staff, security, and stakeholders
  - SAS/DRRM Admin: Can register reporters only
- Role-based editing/deletion permissions (matching registration rules)
- Active/Inactive status management with real-time enforcement
- Automatic logout for deactivated users with modal notification
- Filter and search capabilities for all user lists
- CSV export functionality

### Administrator Dashboard

- Real-time statistics (users, vehicles, violations)
- Interactive heatmap of violation locations
- Violation trend visualization
- Report management interface
- User registration and management
- System configuration access

### Real-Time Features

- WebSocket-based notifications via Laravel Reverb
- Instant report status updates
- User status change notifications
- Broadcast channels for different data types
- Actor filtering (users don't see their own action notifications)
- Clickable notifications navigating to relevant pages

### Progressive Web App (PWA)

- Installable on mobile and desktop devices
- Offline fallback page
- Service worker for asset caching
- Custom app icon and splash screen
- Settings tab installation interface
- Works with existing Heroicons (inline SVG)

## System Requirements

### Server Requirements

- **PHP**: 8.2.12 or higher
- **MySQL**: 8.0 or higher
- **Node.js**: 18.x or higher
- **Composer**: 2.x
- **NPM**: 9.x or higher

### PHP Extensions

- BCMath PHP Extension
- Ctype PHP Extension
- Fileinfo PHP Extension
- JSON PHP Extension
- Mbstring PHP Extension
- OpenSSL PHP Extension
- PDO PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- GD Extension (optional, for server-side icon generation)

### Browser Requirements

- Modern browsers with WebSocket support (Chrome, Firefox, Safari, Edge)
- JavaScript enabled
- LocalStorage enabled for PWA features

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/your-org/mluc-sentinel.git
cd mluc-sentinel
```

### 2. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node dependencies
npm install
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure Database

Edit `.env` file:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mluc_sentinel
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Create the database:

```bash
mysql -u root -p
CREATE DATABASE mluc_sentinel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 5. Run Migrations and Seeders

```bash
php artisan migrate --seed
```

This will create all database tables and seed:
- One Global Administrator (Alvin de Mesa / ademesa.dev@gmail.com / admin123)
- Admin roles (Chancellor, DRRM, Planning, Security, Auxiliary Services, SAS, Marketing)
- Reporter types (Faculty, SBO, VHE)
- Stakeholder types (Guardian, Visitor, Service Provider)
- Colleges and programs
- Vehicle types
- Violation types
- Map location types
- Sticker counters

### 6. Link Storage

```bash
php artisan storage:link
```

### 7. Build Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

### 8. Start Development Services

```bash
# Option 1: All services at once (recommended)
composer run dev

# Option 2: Individual services in separate terminals
php artisan serve           # Web server (http://localhost:8000)
php artisan queue:work      # Queue worker
php artisan reverb:start    # WebSocket server (port 8080)
npm run dev                 # Vite dev server (port 5173)
```

## Configuration

### Broadcasting (WebSocket)

Configure Laravel Reverb in `.env`:

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=338257
REVERB_APP_KEY=xjybg4ttpkkoazcqudlb
REVERB_APP_SECRET=wz3y3d82d2g8s9ygz74z
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Vite Configuration
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

### Queue Configuration

```env
QUEUE_CONNECTION=database  # Use 'redis' for production
```

### Mail Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=mlucsentinel@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"
```

### Session Configuration

```env
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### File Storage

```env
FILESYSTEM_DISK=public
```

Ensure proper permissions:

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## Usage

### Default Login Credentials

After running the seeder, you can log in with:

- **Email**: ademesa.dev@gmail.com
- **Password**: admin123
- **Role**: Global Administrator

### Registering Users and Vehicles

1. Navigate to **Registration** menu (role-based visibility)
2. Select user type (Student, Staff, Security, Stakeholder, Reporter, Administrator)
3. Fill in required information:
   - Personal details (name, email)
   - User-specific details (student ID, staff ID, license number, etc.)
   - Vehicle information (up to 3 vehicles with plate numbers)
4. System automatically generates colored stickers with QR codes
5. Download and print stickers from the Stickers page (Marketing Admin only)

### Processing Sticker Payments

1. Marketing Admin navigates to **Stickers** page
2. View pending payment requests
3. Update payment status to "Paid" when payment is received
4. Generate and download receipt (PDF)
5. Provide sticker to user

### Reporting Violations

**Option 1: QR Code Scanning**
1. Security/Reporter navigates to **Report User** page
2. Scan vehicle parking sticker QR code
3. System auto-fills vehicle and user information
4. Add violation details, photo evidence, and map location
5. Submit report (auto-assigned to appropriate admin)

**Option 2: Manual Entry**
1. Security/Reporter navigates to **Report User** page
2. Enter sticker number in search field
3. Continue with step 3-5 from Option 1

### Managing Violations (Admin)

1. Navigate to **Reports** page (visible to Global, SAS, and Chancellor admins)
2. View list of reported violations
3. Click "View" to see details, evidence, and map location
4. Update status to "Approved" or "Rejected"
5. Status change triggers:
   - Real-time notification to reporter
   - Email notification to violator
   - Update in violation history

### Security Patrol Check-ins

1. Security personnel navigates to **Scan Patrol Point** page
2. Scan location QR code at patrol checkpoint
3. Optional: Add notes about patrol observation
4. Submit check-in (logs timestamp and GPS coordinates)
5. View patrol history to track coverage

### Managing Campus Map

1. Global Admin navigates to **Campus Map** page
2. Click "Add Location" button
3. Use drawing mode to mark polygons on map
4. Enter location details:
   - Name (e.g., "Engineering Building Parking")
   - Short code (max 5 characters, e.g., "ENGPK")
   - Location type (Parking, Building, Zone, etc.)
5. Save location
6. Download QR stickers for patrol check-in points

### Two-Factor Authentication (2FA)

1. Navigate to **Settings** → **Security** tab
2. Click "Enable Two-Factor Authentication"
3. Scan QR code with authenticator app (Google Authenticator, Authy, etc.)
4. Enter verification code to confirm
5. Save recovery codes in secure location
6. Future logins will require authenticator code

### Progressive Web App Installation

**Desktop (Chrome/Edge):**
1. Navigate to Settings → Appearance tab
2. Click "Install App" button
3. Confirm installation in browser prompt
4. Access app from desktop/start menu

**Mobile (Chrome/Safari):**
1. Visit the site in mobile browser
2. Tap browser menu (three dots or share icon)
3. Select "Install App" or "Add to Home Screen"
4. Access app from home screen like native app

## User Roles

### Post-Login Destinations

- **Global Administrator, Administrator**: Dashboard (`/dashboard`)
- **Reporter, Security**: Reporter Home (`/home`)
- **Student, Staff, Stakeholder**: Profile (`/profile`)

### Global Administrator

- Full system access without restrictions
- Can register, edit, and delete all user types
- Access to all pages and features
- System configuration and settings management
- Audit trail visibility

### Administrator Roles

The system includes 7 administrator types with specific permissions:

#### Security Administrator
- **Registration**: Students, Staff, Security, Stakeholders
- **Editing**: Students, Staff, Security, Stakeholders
- **Deletion**: Students, Staff, Security, Stakeholders
- **Special Access**: Patrol Monitor page
- **Cannot Access**: Reports page, Stickers page

#### SAS (Student Affairs & Services) Administrator
- **Registration**: Reporters only
- **Editing**: Reporters only
- **Deletion**: Reporters only
- **Special Access**: Reports page (student violations)
- **Cannot Access**: Patrol Monitor, Stickers page

#### DRRM Administrator
- **Registration**: Reporters only
- **Editing**: Reporters only
- **Deletion**: Reporters only
- **Cannot Access**: Reports, Patrol Monitor, Stickers pages

#### Chancellor Administrator
- **View-Only Access**: All user lists
- **Special Access**: Reports page
- **No Registration**: Cannot register any users
- **No Editing/Deletion**: Cannot modify users

#### Marketing Administrator
- **View-Only Access**: All user lists
- **Special Access**: Stickers page (exclusive access)
- **No Registration**: Cannot register any users
- **No Editing/Deletion**: Cannot modify users

#### Planning Administrator
- **View-Only Access**: Dashboard, Users, Vehicles, Campus Map
- **No Special Access**: No exclusive features
- **No Registration**: Cannot register any users
- **No Editing/Deletion**: Cannot modify users

#### Auxiliary Services Administrator
- **View-Only Access**: Dashboard, Users, Vehicles, Campus Map
- **No Special Access**: No exclusive features
- **No Registration**: Cannot register any users
- **No Editing/Deletion**: Cannot modify users

### Student

- Campus students enrolled in various colleges
- Can register up to 3 vehicles
- Sticker colors based on plate number last digit:
  - 1, 2: Blue
  - 3, 4: Green
  - 5, 6: Yellow
  - 7, 8: Pink
  - 9, 0: Orange
- No direct system access (registration done by admins)

### Staff

- University staff members
- Can register up to 3 vehicles
- Maroon stickers for all vehicles
- No direct system access

### Security

- Security personnel
- Can register up to 3 vehicles (Maroon stickers)
- Can report parking violations
- Can perform patrol check-ins
- Access to patrol scanner and history

### Reporter

Three types of reporters with different permissions:

#### Faculty Reporter
- Can report violations for all user types
- Access to My Reports page
- Cannot register vehicles

#### SBO (Student Body Organization) Reporter
- Can only report student violations
- Receives error modal when trying to report non-students
- Access to My Reports page

#### VHE (Volunteer for Higher Education) Reporter
- Can report violations for all user types
- Temporary role with expiration date (1 year)
- Access to My Reports page

### Stakeholder

Three types with different sticker colors:

#### Guardian
- Parents/guardians of students
- White stickers
- Up to 3 vehicles

#### Visitor
- Campus visitors
- White stickers
- Up to 3 vehicles

#### Service Provider
- Maintenance, delivery, contractors
- Black stickers
- Up to 3 vehicles

## Architecture

### Technology Stack

- **Backend Framework**: Laravel 12.35.1
- **PHP Version**: 8.2.12
- **Database**: MySQL 8.0
- **Frontend**: Blade Templates
- **CSS Framework**: Tailwind CSS 4.1.16
- **JavaScript Bundler**: Vite 7
- **Real-time**: Laravel Reverb + Echo
- **Queue System**: Database (configurable to Redis)
- **Mail**: SMTP (Gmail)
- **Code Formatter**: Laravel Pint 1.x
- **Testing**: PHPUnit 11.x

### Key Laravel Packages

- **laravel/reverb**: WebSocket broadcasting
- **laravel/sanctum**: API authentication
- **laravel/pint**: Code formatting
- **pragmarx/google2fa-qrcode**: Two-factor authentication
- **bacon/bacon-qr-code**: QR code generation
- **simplesoftwareio/simple-qrcode**: Additional QR utilities

### Database Schema

#### Core Tables
- `users`: Base user table with user_type enum
- `global_administrators`: Global admin records
- `administrators`: Role-based admins with role_id
- `students`: Student-specific data (student_id, college_id, program_id)
- `staff`: Staff-specific data (staff_id)
- `security`: Security personnel data (security_id)
- `reporters`: Reporter data with type_id
- `stakeholders`: Stakeholder data with type_id

#### Supporting Tables
- `vehicles`: Vehicle registrations linked to users
- `reports`: Violation reports with evidence and location
- `patrol_logs`: Security check-in records
- `map_locations`: Campus map polygon data
- `admin_roles`: Administrator role definitions
- `reporter_types`: Reporter type definitions
- `stakeholder_types`: Stakeholder type definitions
- `colleges`: Academic colleges
- `programs`: Academic programs per college
- `vehicle_types`: Vehicle type definitions
- `violation_types`: Violation category definitions
- `sticker_counters`: Auto-increment counters per color
- `payment_batches`: Sticker payment tracking
- `notifications`: In-app notification storage
- `sessions`: User session management

### Security Features

- **Authentication**: Laravel Sanctum with session-based auth
- **Two-Factor Authentication**: TOTP-based 2FA with recovery codes
- **Authorization**: Role-based access control with middleware
- **Password Hashing**: Bcrypt algorithm
- **CSRF Protection**: Built-in Laravel CSRF tokens
- **XSS Protection**: Blade template escaping
- **SQL Injection Protection**: Eloquent ORM and parameter binding
- **File Upload Security**: File type and size validation
- **Rate Limiting**: API and form submission throttling
- **Secure Headers Middleware**: Custom security headers
- **Active Status Enforcement**: Real-time user deactivation with auto-logout

### Real-Time Architecture

- **Broadcasting Driver**: Reverb (Laravel's built-in WebSocket server)
- **Client Library**: Laravel Echo with Pusher
- **Channels**:
  - `users`: User creation events
  - `vehicles`: Vehicle updates
  - `reports`: Violation report updates
  - `student-reports.{userId}`: Student-specific report updates
  - `non-student-reports.{userId}`: Non-student report updates
  - `user.{userId}`: User-specific status changes
- **Events**:
  - `UserStatusChanged`: User active/inactive status
  - `ReportStatusUpdated`: Report approval/rejection
  - `NotificationCreated`: New notifications
- **Actor Filtering**: Users don't receive notifications for their own actions

## Progressive Web App

### Features

- **Installable**: Add to home screen on mobile and desktop
- **Offline Support**: Service worker with cache-first strategy
- **App-Like Experience**: Fullscreen mode, custom splash screen
- **Push Notifications**: (Not yet implemented, infrastructure ready)
- **Background Sync**: (Not yet implemented, infrastructure ready)

### Files

- `public/manifest.json`: PWA manifest with app metadata
- `public/sw.js`: Service worker for caching and offline support
- `public/pwa-register.js`: Service worker registration script
- `resources/views/offline.blade.php`: Offline fallback page
- `public/images/icons/`: App icons (72x72 to 512x512)

### Installation Instructions

The PWA can be installed on:
- **Desktop**: Chrome, Edge, Opera
- **Android**: Chrome, Edge, Samsung Internet
- **iOS**: Safari (limited PWA support)

Users will see an "Install App" button in Settings → Appearance tab when the app is installable.

### Development Notes

- Icon generation: Use `public/generate-icons.html` to create icons from logo
- Service worker updates automatically on new deployments
- Cache version is incremented in `sw.js` for cache invalidation

## Development

### Code Style

This project uses Laravel Pint with default Laravel preset:

```bash
# Format all files
./vendor/bin/pint

# Format specific files
./vendor/bin/pint app/Http/Controllers

# Check for style issues without fixing
./vendor/bin/pint --test

# Format only changed files (Git)
./vendor/bin/pint --dirty
```

### Development Commands

```bash
# Start all development services
composer run dev

# Individual services
php artisan serve           # Development web server
php artisan queue:work      # Process queue jobs
php artisan reverb:start    # WebSocket server
npm run dev                 # Frontend dev server with HMR

# Restart services
php artisan queue:restart
php artisan reverb:restart

# View routes
php artisan route:list

# View registered commands
php artisan list
```

### Database Management

```bash
# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Reset and re-run all migrations
php artisan migrate:reset
php artisan migrate

# Seed specific seeder
php artisan db:seed --class=UsersSeeder
```

### Cache Management

```bash
# Clear all caches
php artisan optimize:clear

# Individual cache clear
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Debugging Tools

```bash
# Interactive PHP shell with Laravel
php artisan tinker

# View application configuration
php artisan about

# View environment
php artisan env

# Tail log files
tail -f storage/logs/laravel.log

# View failed queue jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

## Testing

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/MapLocationStickerTest.php

# Run specific test method
php artisan test --filter=test_generates_sticker_with_correct_qr_code

# Run with coverage
php artisan test --coverage

# Run parallel tests
php artisan test --parallel
```

### Test Structure

```
tests/
├── Feature/                    # Integration tests
│   ├── MapLocationStickerTest.php
│   ├── ReportAssignmentTest.php
│   └── StickerGenerationTest.php
└── Unit/                       # Unit tests
    ├── EnumTest.php
    └── ServiceTest.php
```

### Writing Tests

```php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StickerGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_vehicle_generates_correct_color(): void
    {
        $user = User::factory()->create(['user_type' => 'student']);
        $vehicle = Vehicle::factory()->create([
            'user_id' => $user->id,
            'plate_no' => 'ABC-1234', // Last digit 4 = Green
        ]);

        $this->assertEquals('green', $vehicle->color);
    }
}
```

## Deployment

### Production Environment Setup

See `deploy.txt` for comprehensive deployment guide to Sevalla or similar platforms.

### Quick Deployment Steps

1. **Prepare Repository**
   ```bash
   git add .
   git commit -m "Production release"
   git push origin main
   ```

2. **Server Setup**
   ```bash
   # Clone repository
   git clone https://github.com/your-org/mluc-sentinel.git
   cd mluc-sentinel

   # Install dependencies
   composer install --optimize-autoloader --no-dev
   npm ci --production

   # Build assets
   npm run build

   # Setup environment
   cp .env.example .env
   php artisan key:generate
   # Edit .env with production values

   # Run migrations and seeders
   php artisan migrate --seed --force

   # Link storage
   php artisan storage:link

   # Optimize application
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache

   # Set permissions
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

3. **Configure Services**
   - Queue Worker: `php artisan queue:work --daemon`
   - WebSocket: `php artisan reverb:start --host=0.0.0.0 --port=8080`
   - Use Supervisor or similar for process management

4. **Production Environment Variables**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com

   # Database
   DB_CONNECTION=mysql
   DB_HOST=production-host
   DB_PORT=3306
   DB_DATABASE=mluc_sentinel
   DB_USERNAME=production_user
   DB_PASSWORD=secure_password

   # Cache & Session
   CACHE_DRIVER=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis

   # Broadcasting (adjust host for production)
   REVERB_HOST=your-domain.com
   REVERB_SCHEME=https
   VITE_REVERB_HOST=your-domain.com
   VITE_REVERB_SCHEME=https

   # Security
   SESSION_SECURE_COOKIE=true
   ```

### SSL Configuration

Ensure your reverse proxy (Nginx/Apache) handles SSL and forwards WebSocket connections to Reverb port 8080.

## Troubleshooting

### Common Issues

#### Stickers Not Generating

```bash
# Check storage permissions
ls -la storage/app/public

# Fix permissions
chmod -R 775 storage/app/public

# Re-link storage
php artisan storage:link

# Check logs
tail -f storage/logs/laravel.log
```

#### WebSocket Connection Failed

```bash
# Verify Reverb is running
ps aux | grep reverb

# Check environment configuration
php artisan config:clear
php artisan config:cache

# Verify VITE variables match REVERB variables
grep REVERB .env
grep VITE_REVERB .env

# Check firewall (port 8080 must be open)
# For production, ensure reverse proxy forwards WebSocket connections
```

#### Queue Jobs Not Processing

```bash
# Check if queue worker is running
ps aux | grep "queue:work"

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Restart queue worker
php artisan queue:restart

# Check queue connection
php artisan tinker
>>> Queue::connection()->size();
```

#### Real-Time Notifications Not Appearing

- Verify Reverb server is running
- Check browser console for WebSocket errors
- Confirm user is authenticated
- Verify channel authorization in `routes/channels.php`
- Check that events implement `ShouldBroadcastNow`

#### Database Connection Error

```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check database exists
mysql -u root -p -e "SHOW DATABASES;"

# Verify credentials in .env
# Ensure database user has proper permissions
```

#### PWA Not Installing

- Ensure HTTPS is enabled (required for PWA)
- Check that all icon files exist in `public/images/icons/`
- Verify `manifest.json` is accessible
- Clear browser cache and service worker
- Check browser console for manifest errors

#### Session Issues / Logged Out Unexpectedly

- Check `SESSION_DRIVER` in `.env`
- Ensure `sessions` table exists (if using database driver)
- Verify `SESSION_LIFETIME` is set appropriately
- Check for active status changes (inactive users are auto-logged out)

### Debug Mode

For development only (NEVER enable in production):

```env
APP_DEBUG=true
APP_ENV=local
LOG_LEVEL=debug
```

### Getting Help

- Check `storage/logs/laravel.log` for detailed error messages
- Enable query logging for database issues
- Use `php artisan about` to view system information
- Review `.env` configuration
- Check file and directory permissions

## License

This project is proprietary software developed for Don Mariano Marcos Memorial State University - Mid La Union Campus.

Copyright 2025 Don Mariano Marcos Memorial State University - Mid La Union Campus. All rights reserved.

## Project Information

**Capstone Project**

A Digital Parking System developed by:
- Dulay, S.A.C.
- De Mesa, A.P.
- Marzan, J.V.R.
- Paz, D.G.F.
- Saltivan, G.A.A.

College of Information Technology
Don Mariano Marcos Memorial State University
Mid La Union Campus
2025

For technical inquiries, contact: ademesa.dev@gmail.com
