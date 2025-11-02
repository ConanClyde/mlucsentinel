# 🚗 MLUC Sentinel

> Campus Vehicle Tracking & Violation Management System for Maria Luisa University of Cebu

![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=flat-square&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-4.x-38B2AC?style=flat-square&logo=tailwind-css)

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [User Roles](#user-roles)
- [Architecture](#architecture)
- [Development](#development)
- [Testing](#testing)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

MLUC Sentinel is a comprehensive campus management system designed to streamline vehicle registration, parking violation reporting, and security patrol monitoring at Maria Luisa University of Cebu. The system provides:

- **Automated vehicle sticker generation** with QR codes
- **Real-time violation reporting** with evidence upload
- **Interactive campus map** with location management
- **Security patrol tracking** with check-in system
- **Role-based access control** for 7 user types
- **Real-time notifications** via WebSockets

## ✨ Features

### 🚗 Vehicle Management
- Vehicle registration with automatic sticker assignment
- Color-coded stickers based on user type and plate numbers
- QR-enabled stickers linking to reporting page
- Batch sticker generation and printing
- Vehicle ownership tracking and history

### 📍 Interactive Campus Map
- SVG-based interactive campus map
- Polygon-based location marking
- QR stickers for patrol check-in points
- Real-time violation pin plotting
- Location type management (parking, buildings, zones)

### 🚨 Violation Reporting
- QR code-based quick reporting
- Evidence upload (photos)
- Automatic admin assignment by violator type
- Status workflow (Pending → Approved/Rejected)
- Report history and audit trail
- Rate limiting protection (10 reports/min per user)

### 🛡️ Security Patrol System
- QR-based location check-ins
- GPS coordinate logging
- Patrol history tracking
- Real-time monitoring dashboard
- Export functionality for reports

### 🎫 Sticker Management
- Payment tracking (Pending/Paid/Cancelled)
- Receipt generation (PDF)
- Batch payment processing
- Downloadable SVG stickers
- Marketing admin-only access

### 📊 Dashboard & Analytics
- Real-time statistics
- User and vehicle counts
- Violation trend analysis
- Chart.js visualizations
- Export functionality

### 🔔 Real-time Notifications
- WebSocket-based updates (Laravel Reverb)
- Violation status notifications
- In-app notification center
- Email notifications for critical events

## 💻 System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher
- **Node.js**: 18.x or higher
- **Composer**: 2.x
- **NPM**: 9.x or higher

### PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD or Imagick (for image processing)

## 🚀 Installation

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
CREATE DATABASE mluc_sentinel;
EXIT;
```

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Seed Database (Optional)
```bash
php artisan db:seed
```

### 7. Link Storage
```bash
php artisan storage:link
```

### 8. Build Assets
```bash
npm run build
```

### 9. Start Development Server
```bash
# Option 1: All services at once (recommended)
composer run dev

# Option 2: Individual services
php artisan serve           # Web server
php artisan queue:work      # Queue worker
php artisan reverb:start    # WebSocket server
npm run dev                 # Vite dev server
```

## ⚙️ Configuration

### Broadcasting (WebSocket)
Configure Laravel Reverb in `.env`:
```env
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your_app_id
REVERB_APP_KEY=your_app_key
REVERB_APP_SECRET=your_app_secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Queue Configuration
```env
QUEUE_CONNECTION=database  # or redis for production
```

### Mail Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@mlucsentinel.edu
MAIL_FROM_NAME="${APP_NAME}"
```

### File Storage
Ensure proper permissions:
```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

## 📖 Usage

### Creating Your First Admin User
```bash
php artisan tinker
```

```php
use App\Models\User;
use App\Models\GlobalAdministrator;
use App\Enums\UserType;

$user = User::create([
    'first_name' => 'Admin',
    'last_name' => 'User',
    'email' => 'admin@mluc.edu.ph',
    'password' => bcrypt('password'),
    'user_type' => UserType::GlobalAdministrator,
    'is_active' => true,
]);

GlobalAdministrator::create(['user_id' => $user->id]);
```

### Registering Vehicles
1. Navigate to **Users** → Select user type
2. Register user with required details
3. Add vehicle information with plate number
4. System automatically generates colored sticker
5. Download and print sticker

### Reporting Violations
1. Scan vehicle QR code or search by sticker number
2. Fill report form with violation details
3. Upload evidence photo
4. Pin location on campus map
5. Submit report (auto-assigned to appropriate admin)

### Security Patrol
1. Access patrol scanner from security dashboard
2. Scan location QR code
3. Confirm check-in with optional notes
4. System logs GPS coordinates and timestamp
5. View patrol history and coverage

## 👥 User Roles

### Global Administrator
- Full system access
- User management across all types
- System configuration
- Audit log access

### Administrator (Role-Based)
- **SAS Admin**: Student Affairs & Services
- **Chancellor Admin**: Administrative oversight
- **Marketing Admin**: Sticker management only
- **Security Admin**: Patrol monitoring access

### Student
- Campus students with vehicles
- Color-coded stickers based on plate number

### Staff
- University staff members
- Maroon stickers

### Security
- Security personnel
- Can report violations
- Patrol check-in access
- Maroon stickers

### Reporter
- External reporters
- Can report violations
- No vehicle registration

### Stakeholder
- Visitors, Guardians, Service Providers
- White or black stickers based on type

## 🏗️ Architecture

### Tech Stack
- **Backend**: Laravel 12 (PHP 8.2)
- **Database**: MySQL 8.0
- **Frontend**: Blade Templates + Tailwind CSS 4
- **Real-time**: Laravel Reverb + Echo
- **Queue**: Database/Redis
- **Assets**: Vite 7

### Key Design Patterns
- **Repository Pattern**: Service classes for business logic
- **Observer Pattern**: Model observers for cache invalidation
- **Factory Pattern**: Database factories for testing
- **Event-Driven**: Events and listeners for notifications

### Directory Structure
```
app/
├── Console/Commands/       # Artisan commands
├── Enums/                  # Enum classes (UserType, ReportStatus, etc.)
├── Events/                 # Event classes
├── Http/
│   ├── Controllers/        # Request handlers
│   ├── Middleware/         # Custom middleware
│   └── Requests/          # Form request validation
├── Jobs/                   # Queue jobs
├── Models/                 # Eloquent models
├── Notifications/          # Notification classes
├── Observers/              # Model observers
├── Policies/               # Authorization policies
├── Services/               # Business logic services
└── Rules/                  # Custom validation rules

database/
├── factories/              # Model factories
├── migrations/             # Database migrations
└── seeders/               # Database seeders

resources/
├── css/                    # Stylesheets
├── js/                     # JavaScript
└── views/                 # Blade templates

routes/
├── channels.php            # Broadcasting channels
├── console.php            # Console routes
└── web.php                # Web routes

tests/
├── Feature/               # Feature tests
└── Unit/                  # Unit tests
```

## 🔧 Development

### Code Style
This project uses Laravel Pint for code formatting:
```bash
# Format all files
./vendor/bin/pint

# Check for style issues
./vendor/bin/pint --test
```

### Running Tests
```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter=MapLocationStickerTest

# Run with coverage
php artisan test --coverage
```

### Database Management
```bash
# Fresh migration
php artisan migrate:fresh

# With seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Reset database
php artisan migrate:reset
```

### Queue Management
```bash
# Process queue jobs
php artisan queue:work

# Process specific queue
php artisan queue:work --queue=high,default

# Clear failed jobs
php artisan queue:flush
```

### Cache Management
```bash
# Clear all cache
php artisan cache:clear

# Clear config cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear
```

## 🧪 Testing

### Test Coverage Areas
- Vehicle sticker generation
- Report auto-assignment logic
- Patrol check-in validation
- Map location sticker generation
- User authentication and authorization

### Writing Tests
```php
// Example: tests/Feature/StickerGenerationTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vehicle;
use App\Services\StickerGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StickerGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_vehicle_gets_correct_color(): void
    {
        $vehicle = Vehicle::factory()->create([
            'plate_no' => 'ABC-1234',
        ]);

        $service = new StickerGenerator();
        $color = $service->determineStickerColor('student', null, 'ABC-1234');

        $this->assertEquals('yellow', $color);
    }
}
```

## 🚢 Deployment

### Production Checklist
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://sentinel.mluc.edu.ph

# Security
SESSION_SECURE_COOKIE=true
SANCTUM_STATEFUL_DOMAINS=sentinel.mluc.edu.ph

# Performance
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Broadcasting
BROADCAST_CONNECTION=reverb
```

### Deployment Steps
1. **Update dependencies**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm ci --production
   ```

2. **Build assets**
   ```bash
   npm run build
   ```

3. **Optimize application**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Run migrations**
   ```bash
   php artisan migrate --force
   ```

5. **Set permissions**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chown -R www-data:www-data storage bootstrap/cache
   ```

6. **Start services**
   ```bash
   # Queue worker
   php artisan queue:work --daemon

   # WebSocket server
   php artisan reverb:start --host=0.0.0.0 --port=8080
   ```

### Server Requirements
- **Web Server**: Nginx or Apache
- **Process Manager**: Supervisor (for queue workers)
- **SSL Certificate**: Let's Encrypt or commercial
- **Reverse Proxy**: For Reverb WebSocket server

## 🐛 Troubleshooting

### Common Issues

#### Stickers Not Generating
```bash
# Check storage permissions
chmod -R 775 storage/app/public

# Re-link storage
php artisan storage:link

# Check logs
tail -f storage/logs/laravel.log
```

#### WebSocket Not Connecting
```bash
# Check Reverb is running
php artisan reverb:start

# Verify environment variables
php artisan config:clear
php artisan config:cache

# Check firewall rules (port 8080)
```

#### Queue Jobs Not Processing
```bash
# Restart queue worker
php artisan queue:restart

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

#### Database Connection Issues
```bash
# Test database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check credentials in .env
# Verify database exists
```

### Debug Mode
Enable debug mode temporarily (NEVER in production):
```env
APP_DEBUG=true
APP_ENV=local
```

## 🤝 Contributing

### Development Workflow
1. Create feature branch from `main`
2. Make changes with descriptive commits
3. Write/update tests
4. Run code formatter: `./vendor/bin/pint`
5. Run tests: `php artisan test`
6. Submit pull request

### Commit Message Format
```
type(scope): description

[optional body]

[optional footer]
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

Example:
```
feat(patrol): add GPS coordinate logging

- Add latitude/longitude fields to patrol_logs
- Update check-in controller to capture coordinates
- Add map visualization for patrol coverage
```

## 📄 License

This project is proprietary software developed for Maria Luisa University of Cebu.

© 2025 Maria Luisa University of Cebu. All rights reserved.

## 📞 Support

For technical support or questions:
- **Email**: support@mluc.edu.ph
- **Issue Tracker**: [GitHub Issues](https://github.com/your-org/mluc-sentinel/issues)
- **Documentation**: [Wiki](https://github.com/your-org/mluc-sentinel/wiki)

---

Built with ❤️ by the MLUC IT Team
