<?php

use App\Http\Controllers\Admin\CollegeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\MapLocationController;
use App\Http\Controllers\Admin\MapLocationTypeController;
use App\Http\Controllers\Admin\MetricsController;
use App\Http\Controllers\Admin\PatrolHistoryController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\Registration\AdministratorController;
use App\Http\Controllers\Admin\Registration\ReporterController;
use App\Http\Controllers\Admin\Registration\SecurityController as AdminRegistrationSecurityController;
use App\Http\Controllers\Admin\Registration\StaffController as AdminRegistrationStaffController;
use App\Http\Controllers\Admin\Registration\StakeholderController;
use App\Http\Controllers\Admin\Registration\StudentController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\StakeholderTypeController;
use App\Http\Controllers\Admin\StickersController;
use App\Http\Controllers\Admin\Users\AdministratorsController;
use App\Http\Controllers\Admin\Users\ReportersController;
use App\Http\Controllers\Admin\Users\SecurityController as AdminUsersSecurityController;
use App\Http\Controllers\Admin\Users\StaffController;
use App\Http\Controllers\Admin\Users\StakeholdersController;
use App\Http\Controllers\Admin\Users\StudentsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\VehiclesController;
use App\Http\Controllers\Admin\VehicleTypeController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CampusMapController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reporter\HomeController as ReporterHomeController;
use App\Http\Controllers\Reporter\MyReportController;
use App\Http\Controllers\Reporter\ReportUserController;
use App\Http\Controllers\Security\PatrolCheckinController;
use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Broadcasting Authentication
Broadcast::routes(['middleware' => ['web', 'auth']]);

// Landing Page
Route::get('/', [AuthController::class, 'landing'])->name('landing');

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Registration - Rate limit: max 3 attempts per minute
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware('throttle:3,1')
        ->name('register.post');

    // Availability check routes for registration
    Route::post('/check-email-availability', [AuthController::class, 'checkEmailAvailability'])->name('check.email.availability');
    Route::post('/check-student-id-availability', [AuthController::class, 'checkStudentIdAvailability'])->name('check.student.id.availability');
    Route::post('/check-plate-no-availability', [AuthController::class, 'checkPlateNoAvailability'])->name('check.plate.availability');
    Route::post('/check-staff-id-availability', [AuthController::class, 'checkStaffIdAvailability'])->name('check.staff.id.availability');
    Route::post('/check-security-id-availability', [AuthController::class, 'checkSecurityIdAvailability'])->name('check.security.id.availability');

    // Login - Rate limit: max 5 attempts per minute
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');

    // Two-Factor Authentication
    Route::get('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'show'])->name('2fa.verify');
    Route::post('/2fa/verify', [App\Http\Controllers\Auth\TwoFactorController::class, 'verify'])->name('2fa.verify.post');

    // Password Reset - Rate limit: max 5 attempts per hour per email
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode'])
        ->middleware('throttle:5,60')
        ->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])
        ->middleware('throttle:5,60')
        ->name('password.update');
    Route::post('/validate-reset-code', [PasswordResetController::class, 'validateResetCode'])
        ->middleware('throttle:10,60')
        ->name('password.validate');
});

// Logout
Route::get('/logout', function () {
    // Handle GET requests to /logout with a friendly auto-submit form
    if (auth()->check()) {
        return view('auth.logout-confirm');
    }

    return redirect()->route('landing')->with('info', 'You are already logged out.');
})->name('logout.get');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// PWA Offline Page
Route::get('/offline', function () {
    return view('offline');
})->name('offline');

// Notification Routes (Protected)
Route::middleware('auth')->prefix('notifications')->name('notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    Route::delete('/clear-all', [NotificationController::class, 'clearAll'])->name('clear-all');
});

// Dashboard (Protected Route) - admin dashboard
Route::get('/dashboard', [AdminDashboardController::class, 'index'])->middleware(['auth', 'user.type:global_administrator,administrator'])->name('dashboard');

// Home Route (Protected Route) - role-based
Route::get('/home', function () {
    $user = auth()->user();

    // Redirect based on user type (using Enum)
    if (in_array($user->user_type, [\App\Enums\UserType::GlobalAdministrator, \App\Enums\UserType::Administrator])) {
        return app(AdminHomeController::class)->index();
    } elseif (in_array($user->user_type, [\App\Enums\UserType::Reporter, \App\Enums\UserType::Security])) {
        return app(ReporterHomeController::class)->index();
    } elseif (in_array($user->user_type, [\App\Enums\UserType::Student, \App\Enums\UserType::Staff, \App\Enums\UserType::Stakeholder])) {
        return app(\App\Http\Controllers\UserController::class)->home();
    } else {
        // For other user types, redirect to profile to avoid exposing admin home
        return redirect()->route('profile');
    }
})->middleware('auth')->name('home');

// User Dashboard Routes (Protected Route) - for students, staff, and stakeholders
Route::middleware(['auth'])->group(function () {
    // User dashboard routes with new URLs
    Route::get('/user/dashboard', [\App\Http\Controllers\UserController::class, 'home'])->name('user.home');
    Route::get('/history', [\App\Http\Controllers\UserController::class, 'reports'])->name('user.reports');
    Route::get('/history/{id}', [\App\Http\Controllers\UserController::class, 'getReportDetails'])->name('user.reports.details');
    Route::get('/requests', [\App\Http\Controllers\UserController::class, 'requests'])->name('user.requests');
    Route::get('/requests/{id}', [\App\Http\Controllers\UserController::class, 'showRequest'])->name('user.requests.show');
    Route::post('/requests', [\App\Http\Controllers\UserController::class, 'storeRequest'])->name('user.requests.store');
    Route::post('/requests/{id}/cancel', [\App\Http\Controllers\UserController::class, 'cancelRequest'])->name('user.requests.cancel');

    // Debug route to test CSRF
    Route::post('/test-csrf', function (\Illuminate\Http\Request $request) {
        return response()->json(['success' => true, 'data' => $request->all()]);
    })->name('test.csrf');

    // Campus Map (View Only) - for reporters, security, and users with vehicles
    Route::get('/map', [\App\Http\Controllers\CampusMapController::class, 'index'])
        ->middleware('user.type:reporter,security,student,staff,stakeholder')
        ->name('campus-map');
});

// Vehicle users (students, staff, stakeholders) - shared route with security
Route::middleware(['auth'])->group(function () {
    Route::get('/my-vehicles', function () {
        $user = auth()->user();

        // Route to appropriate controller based on user type
        if ($user->user_type === \App\Enums\UserType::Security || $user->user_type === \App\Enums\UserType::Reporter) {
            return app(\App\Http\Controllers\Reporter\MyVehiclesController::class)->index();
        } elseif (in_array($user->user_type, [\App\Enums\UserType::Student, \App\Enums\UserType::Staff, \App\Enums\UserType::Stakeholder])) {
            return app(\App\Http\Controllers\UserController::class)->vehicles();
        } else {
            abort(403, 'Access denied. User type: '.$user->user_type->value);
        }
    })->name('user.vehicles');
});

// CSRF Token Route (simple, following admin pattern)
Route::get('/csrf-token', function () {
    return response()->json(['csrf_token' => csrf_token()]);
})->middleware('auth');

// Profile Route (Protected Route) - available to all authenticated users
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth')->name('profile');
Route::post('/profile/update', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->middleware('auth')->name('profile.change-password');
Route::post('/profile/verify-password', [ProfileController::class, 'verifyPassword'])->middleware('auth')->name('profile.verify-password');
Route::delete('/profile/delete', [ProfileController::class, 'delete'])->middleware('auth')->name('profile.delete');

// Settings Routes (Protected Route) - available to all authenticated users
Route::middleware('auth')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('/settings/activity-logs', [SettingsController::class, 'getActivityLogs'])->name('settings.activity-logs');
    Route::post('/settings/2fa/enable', [SettingsController::class, 'enable2FA'])->name('settings.2fa.enable');
    Route::post('/settings/2fa/confirm', [SettingsController::class, 'confirm2FA'])->name('settings.2fa.confirm');

    // Admin Role Management (Global Admin only)
    Route::middleware('global.admin')->get('/settings/roles', [\App\Http\Controllers\Admin\AdminRoleController::class, 'index'])->name('settings.roles');
    Route::post('/settings/2fa/disable', [SettingsController::class, 'disable2FA'])->name('settings.2fa.disable');
    Route::post('/settings/2fa/recovery-codes', [SettingsController::class, 'getRecoveryCodes'])->name('settings.2fa.recovery-codes');
    Route::post('/settings/2fa/recovery-codes/regenerate', [SettingsController::class, 'regenerateRecoveryCodes'])->name('settings.2fa.recovery-codes.regenerate');
});

// Admin Routes
Route::middleware(['auth', 'user.type:global_administrator,administrator'])->group(function () {
    // Pending Registrations Routes (Global Admin only)
    Route::middleware('user.type:global_administrator')->group(function () {
        Route::get('/admin/pending-registrations', [\App\Http\Controllers\Admin\PendingRegistrationController::class, 'index'])->name('admin.pending-registrations');
        Route::get('/admin/pending-registrations/{pendingRegistration}', [\App\Http\Controllers\Admin\PendingRegistrationController::class, 'show'])->name('admin.pending-registrations.show');
        Route::post('/admin/pending-registrations/{pendingRegistration}/approve', [\App\Http\Controllers\Admin\PendingRegistrationController::class, 'approve'])->name('admin.pending-registrations.approve');
        Route::post('/admin/pending-registrations/{pendingRegistration}/reject', [\App\Http\Controllers\Admin\PendingRegistrationController::class, 'reject'])->name('admin.pending-registrations.reject');
        Route::delete('/admin/pending-registrations/{pendingRegistration}', [\App\Http\Controllers\Admin\PendingRegistrationController::class, 'destroy'])->name('admin.pending-registrations.destroy');
    });

    // Campus Map Routes
    Route::get('/campus-map', [MapLocationController::class, 'index'])->middleware('privilege:manage_campus_map')->name('admin.campus-map');
    Route::get('/campus-map/download-stickers', [MapLocationController::class, 'downloadAllStickers'])->middleware('privilege:manage_campus_map')->name('admin.campus-map.download-stickers');
    Route::get('/api/map-locations', [MapLocationController::class, 'getLocations'])->middleware('privilege:manage_campus_map')->name('api.map-locations.index');
    Route::get('/api/map-location-types', [MapLocationController::class, 'getTypes'])->middleware('privilege:manage_campus_map')->name('api.map-location-types.index');
    Route::post('/api/map-locations', [MapLocationController::class, 'store'])->middleware('privilege:manage_campus_map')->name('api.map-locations.store');
    Route::get('/api/map-locations/{location}', [MapLocationController::class, 'show'])->middleware('privilege:manage_campus_map')->name('api.map-locations.show');
    Route::put('/api/map-locations/{location}', [MapLocationController::class, 'update'])->middleware('privilege:manage_campus_map')->name('api.map-locations.update');
    Route::delete('/api/map-locations/{location}', [MapLocationController::class, 'destroy'])->middleware('privilege:manage_campus_map')->name('api.map-locations.destroy');
    Route::post('/api/map-locations/{location}/toggle-active', [MapLocationController::class, 'toggleActive'])->middleware('privilege:manage_campus_map')->name('api.map-locations.toggle-active');

    // API Routes - Rate limit: max 60 requests per minute per user
    Route::middleware('throttle:60,1')->group(function () {
        // College Routes
        Route::get('/api/colleges', [CollegeController::class, 'index'])->middleware('privilege:view_settings_college')->name('api.colleges.index');
        Route::post('/api/colleges', [CollegeController::class, 'store'])->middleware('privilege:view_settings_college')->name('api.colleges.store');
        Route::put('/api/colleges/{college}', [CollegeController::class, 'update'])->middleware('privilege:view_settings_college')->name('api.colleges.update');
        Route::delete('/api/colleges/{college}', [CollegeController::class, 'destroy'])->middleware('privilege:view_settings_college')->name('api.colleges.destroy');
        Route::get('/api/colleges/{college}/programs', [CollegeController::class, 'programs'])->middleware('privilege:view_settings_college')->name('api.colleges.programs');

        // Program Routes
        Route::get('/api/programs', [ProgramController::class, 'index'])->middleware('privilege:view_settings_program')->name('api.programs.index');
        Route::post('/api/programs', [ProgramController::class, 'store'])->middleware('privilege:view_settings_program')->name('api.programs.store');
        Route::put('/api/programs/{program}', [ProgramController::class, 'update'])->middleware('privilege:view_settings_program')->name('api.programs.update');
        Route::delete('/api/programs/{program}', [ProgramController::class, 'destroy'])->middleware('privilege:view_settings_program')->name('api.programs.destroy');

        // Fee Routes
        Route::get('/api/fees', [FeeController::class, 'index'])->middleware('privilege:view_settings_fees')->name('api.fees.index');

        // Vehicle Type Routes
        Route::get('/api/vehicle-types', [VehicleTypeController::class, 'index'])->middleware('privilege:view_settings_vehicle_type')->name('api.vehicle-types.index');
        Route::post('/api/vehicle-types', [VehicleTypeController::class, 'store'])->middleware('privilege:view_settings_vehicle_type')->name('api.vehicle-types.store');
        Route::put('/api/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'update'])->middleware('privilege:view_settings_vehicle_type')->name('api.vehicle-types.update');

        // Admin Role & Privilege Routes (Global Admin only)
        Route::middleware('global.admin')->prefix('api/admin-roles')->name('api.admin-roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminRoleController::class, 'getRoles'])->name('index');
            Route::get('/privileges', [\App\Http\Controllers\Admin\AdminRoleController::class, 'getPrivileges'])->name('privileges');
            Route::post('/', [\App\Http\Controllers\Admin\AdminRoleController::class, 'store'])->name('store');
            Route::put('/{role}', [\App\Http\Controllers\Admin\AdminRoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [\App\Http\Controllers\Admin\AdminRoleController::class, 'destroy'])->name('destroy');
        });

        // Reporter Role Routes (Global Admin only)
        Route::middleware('global.admin')->prefix('api/reporter-roles')->name('api.reporter-roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\ReporterRoleController::class, 'index'])->name('index');
            Route::get('/user-types', [\App\Http\Controllers\Admin\ReporterRoleController::class, 'getAvailableUserTypes'])->name('user-types');
            Route::post('/', [\App\Http\Controllers\Admin\ReporterRoleController::class, 'store'])->name('store');
            Route::put('/{role}', [\App\Http\Controllers\Admin\ReporterRoleController::class, 'update'])->name('update');
            Route::delete('/{role}', [\App\Http\Controllers\Admin\ReporterRoleController::class, 'destroy'])->name('destroy');
            Route::post('/{role}/toggle-active', [\App\Http\Controllers\Admin\ReporterRoleController::class, 'toggleActive'])->name('toggle-active');
        });
        Route::delete('/api/vehicle-types/{vehicleType}', [VehicleTypeController::class, 'destroy'])->middleware('privilege:view_settings_vehicle_type')->name('api.vehicle-types.destroy');

        // Map Location Type Routes
        Route::get('/api/map-location-types', [MapLocationTypeController::class, 'index'])->middleware('privilege:view_settings_location_type')->name('api.map-location-types.index');
        Route::post('/api/map-location-types', [MapLocationTypeController::class, 'store'])->middleware('privilege:view_settings_location_type')->name('api.map-location-types.store');
        Route::put('/api/map-location-types/{mapLocationType}', [MapLocationTypeController::class, 'update'])->middleware('privilege:view_settings_location_type')->name('api.map-location-types.update');
        Route::delete('/api/map-location-types/{mapLocationType}', [MapLocationTypeController::class, 'destroy'])->middleware('privilege:view_settings_location_type')->name('api.map-location-types.destroy');

        // Stakeholder Types & Sticker Config (Global Admin only)
        Route::middleware('global.admin')->group(function () {
            // Stakeholder Types
            Route::get('/api/stakeholder-types', [StakeholderTypeController::class, 'index'])->name('api.stakeholder-types.index');
            Route::post('/api/stakeholder-types', [StakeholderTypeController::class, 'store'])->name('api.stakeholder-types.store');
            Route::put('/api/stakeholder-types/{stakeholderType}', [StakeholderTypeController::class, 'update'])->name('api.stakeholder-types.update');
            Route::delete('/api/stakeholder-types/{stakeholderType}', [StakeholderTypeController::class, 'destroy'])->name('api.stakeholder-types.destroy');

            // Sticker Configuration (rules + expiration)
            Route::get('/api/settings/sticker-config', [SettingsController::class, 'getStickerConfig'])->name('api.settings.sticker-config');
            Route::put('/api/settings/sticker-config', [SettingsController::class, 'updateStickerConfig'])->name('api.settings.sticker-config.update');

            // Sticker Palette CRUD
            Route::get('/api/settings/sticker-palette', [SettingsController::class, 'getStickerPalette'])->name('api.settings.sticker-palette');
            Route::post('/api/settings/sticker-palette', [SettingsController::class, 'addStickerColor'])->name('api.settings.sticker-palette.store');
            Route::put('/api/settings/sticker-palette/{key}', [SettingsController::class, 'updateStickerColor'])->name('api.settings.sticker-palette.update');
            Route::delete('/api/settings/sticker-palette/{key}', [SettingsController::class, 'deleteStickerColor'])->name('api.settings.sticker-palette.destroy');
        });
    });

    // Fee Update Route - Rate limit: max 10 updates per minute per admin
    Route::put('/api/fees/{fee}', [FeeController::class, 'update'])
        ->middleware(['throttle:10,1', 'privilege:manage_fees'])
        ->name('api.fees.update');

    Route::get('/users', [UsersController::class, 'index'])->middleware('privilege:view_students')->name('admin.users');

    // Bulk User Operations (requires edit privileges)
    Route::prefix('api/bulk/users')->middleware('privilege:edit_students')->name('api.bulk.users.')->group(function () {
        Route::post('/import', [\App\Http\Controllers\Admin\BulkUsersController::class, 'import'])->name('import');
        Route::post('/update', [\App\Http\Controllers\Admin\BulkUsersController::class, 'bulkUpdate'])->name('update');
        Route::post('/delete', [\App\Http\Controllers\Admin\BulkUsersController::class, 'bulkDelete'])->name('delete');
        Route::post('/status', [\App\Http\Controllers\Admin\BulkUsersController::class, 'bulkStatusUpdate'])->name('status');
    });

    Route::get('/vehicles', [VehiclesController::class, 'index'])->middleware('privilege:view_vehicles')->name('admin.vehicles');
    Route::get('/vehicles/data', [VehiclesController::class, 'data'])->middleware('privilege:view_vehicles')->name('admin.vehicles.data');
    Route::delete('/vehicles/{vehicle}', [VehiclesController::class, 'destroy'])->middleware('privilege:delete_vehicles')->name('admin.vehicles.destroy');

    // Bulk Vehicle Operations (requires edit/delete vehicle privileges)
    Route::prefix('api/bulk/vehicles')->middleware('privilege:edit_vehicles')->name('api.bulk.vehicles.')->group(function () {
        Route::post('/update', [\App\Http\Controllers\Admin\BulkVehiclesController::class, 'bulkUpdate'])->name('update');
        Route::post('/delete', [\App\Http\Controllers\Admin\BulkVehiclesController::class, 'bulkDelete'])->name('delete');
        Route::post('/status', [\App\Http\Controllers\Admin\BulkVehiclesController::class, 'bulkStatusUpdate'])->name('status');
    });
    Route::get('/reports', [ReportsController::class, 'index'])->middleware('privilege:manage_reports')->name('admin.reports');
    Route::get('/reports/export', [ReportsController::class, 'export'])->middleware('privilege:manage_reports')->name('admin.reports.export');
    Route::put('/reports/{report}/status', [ReportsController::class, 'updateStatus'])->middleware('privilege:manage_reports')->name('admin.reports.status');

    // Dashboard Export Route
    Route::get('/dashboard/export', [AdminDashboardController::class, 'export'])->middleware('privilege:export_dashboard')->name('admin.dashboard.export');

    // Stickers Routes - Privilege-based
    Route::middleware('privilege:view_stickers')->group(function () {
        Route::get('/stickers', [StickersController::class, 'index'])->name('admin.stickers');
        Route::get('/stickers/data', [StickersController::class, 'data'])->name('admin.stickers.data');
        Route::get('/stickers/issued', [StickersController::class, 'getIssuedStickers'])->name('admin.stickers.issued');
        Route::get('/stickers/download-filtered', [StickersController::class, 'downloadFilteredStickers'])->name('admin.stickers.download-filtered');
        Route::get('/stickers/vehicle/{vehicle}/download', [StickersController::class, 'downloadSticker'])->name('admin.stickers.download');
        Route::get('/stickers/search-users', [StickersController::class, 'searchUsers'])->name('admin.stickers.search-users');
        Route::get('/stickers/requests-data', [StickersController::class, 'getRequestsData'])->name('admin.stickers.requests-data');
        Route::get('/stickers/requests/{id}', [StickersController::class, 'showRequest'])->name('admin.stickers.requests.show');
        Route::post('/stickers/request', [StickersController::class, 'createRequest'])->name('admin.stickers.request');
        Route::patch('/stickers/requests/{id}/approve', [StickersController::class, 'approveRequest'])->name('admin.stickers.requests.approve');
        Route::patch('/stickers/requests/{id}/reject', [StickersController::class, 'rejectRequest'])->name('admin.stickers.requests.reject');
        Route::patch('/stickers/{payment}/pay', [StickersController::class, 'markAsPaid'])->name('admin.stickers.pay');
        Route::patch('/stickers/{payment}/cancel', [StickersController::class, 'cancel'])->name('admin.stickers.cancel');
        Route::delete('/stickers/{payment}', [StickersController::class, 'destroy'])->name('admin.stickers.destroy');
        Route::get('/stickers/{payment}/receipt', [StickersController::class, 'downloadReceipt'])->name('admin.stickers.receipt');
    });

    // User Types Routes
    Route::get('/users/students', [StudentsController::class, 'index'])->middleware('privilege:view_students')->name('admin.users.students');
    Route::get('/users/students/data', [StudentsController::class, 'data'])->middleware('privilege:view_students')->name('admin.users.students.data');
    Route::delete('/users/students/{student}', [StudentsController::class, 'destroy'])->middleware('privilege:delete_students')->name('admin.users.students.destroy');
    Route::get('/users/staff', [StaffController::class, 'index'])->middleware('privilege:view_staff')->name('admin.users.staff');
    Route::get('/users/staff/data', [StaffController::class, 'data'])->middleware('privilege:view_staff')->name('admin.users.staff.data');
    Route::delete('/users/staff/{staff}', [StaffController::class, 'destroy'])->middleware('privilege:delete_staff')->name('admin.users.staff.destroy');
    Route::get('/users/security', [AdminUsersSecurityController::class, 'index'])->middleware('privilege:view_security')->name('admin.users.security');
    Route::get('/users/security/data', [AdminUsersSecurityController::class, 'data'])->middleware('privilege:view_security')->name('admin.users.security.data');
    Route::delete('/users/security/{security}', [AdminUsersSecurityController::class, 'destroy'])->middleware('privilege:delete_security')->name('admin.users.security.destroy');
    Route::get('/users/reporters', [ReportersController::class, 'index'])->middleware('privilege:view_reporters')->name('admin.users.reporters');
    Route::delete('/users/reporters/{reporter}', [ReportersController::class, 'destroy'])->middleware('privilege:delete_reporters')->name('admin.users.reporters.destroy');

    Route::get('/users/stakeholders', [StakeholdersController::class, 'index'])->middleware('privilege:view_stakeholders')->name('admin.users.stakeholders');
    Route::get('/users/stakeholders/data', [StakeholdersController::class, 'data'])->middleware('privilege:view_stakeholders')->name('admin.users.stakeholders.data');
    Route::delete('/users/stakeholders/{stakeholder}', [StakeholdersController::class, 'destroy'])->middleware('privilege:delete_stakeholders')->name('admin.users.stakeholders.destroy');
    Route::get('/users/administrators', [AdministratorsController::class, 'index'])->middleware('privilege:view_administrators')->name('admin.users.administrators');
    Route::post('/users/administrators', [AdministratorsController::class, 'store'])->middleware('privilege:register_administrators')->name('admin.users.administrators.store');
    Route::put('/users/administrators/{administrator}', [AdministratorsController::class, 'update'])->middleware('privilege:edit_administrators')->name('admin.users.administrators.update');
    Route::delete('/users/administrators/{administrator}', [AdministratorsController::class, 'destroy'])->middleware('privilege:delete_administrators')->name('admin.users.administrators.destroy');

    // User Management Update Routes - Privilege-based
    Route::put('/users/students/{student}', [StudentsController::class, 'update'])->middleware('privilege:edit_students')->name('admin.users.students.update');
    Route::put('/users/staff/{staff}', [StaffController::class, 'update'])->middleware('privilege:edit_staff')->name('admin.users.staff.update');
    Route::put('/users/security/{security}', [AdminUsersSecurityController::class, 'update'])->middleware('privilege:edit_security')->name('admin.users.security.update');
    Route::put('/users/stakeholders/{stakeholder}', [StakeholdersController::class, 'update'])->middleware('privilege:edit_stakeholders')->name('admin.users.stakeholders.update');

    // User Management Update Routes - Reporters
    Route::put('/users/reporters/{reporter}', [ReportersController::class, 'update'])->middleware('privilege:edit_reporters')->name('admin.users.reporters.update');

    // Registration Routes - Privilege-based (Stakeholders, Security, Staff, Students)
    Route::middleware('privilege:register_students')->group(function () {
        Route::get('/registration/student', [StudentController::class, 'index'])->name('admin.registration.student');
        Route::post('/registration/student', [StudentController::class, 'store'])
            ->middleware('file.upload.security')
            ->name('admin.registration.student.store');
        Route::post('/registration/student/check-email', [StudentController::class, 'checkEmail'])->name('admin.registration.student.check-email');
        Route::post('/registration/student/check-student-id', [StudentController::class, 'checkStudentId'])->name('admin.registration.student.check-student-id');
        Route::post('/registration/student/check-license-no', [StudentController::class, 'checkLicenseNo'])->name('admin.registration.student.check-license-no');
        Route::post('/registration/student/check-plate-no', [StudentController::class, 'checkPlateNo'])->name('admin.registration.student.check-plate-no');
    });

    Route::middleware('privilege:register_staff')->group(function () {
        Route::get('/registration/staff', [AdminRegistrationStaffController::class, 'index'])->name('admin.registration.staff');
        Route::post('/registration/staff', [AdminRegistrationStaffController::class, 'store'])
            ->middleware('file.upload.security')
            ->name('admin.registration.staff.store');
        Route::post('/registration/staff/check-email', [AdminRegistrationStaffController::class, 'checkEmail'])->name('admin.registration.staff.check-email');
        Route::post('/registration/staff/check-staff-id', [AdminRegistrationStaffController::class, 'checkStaffId'])->name('admin.registration.staff.check-staff-id');
        Route::post('/registration/staff/check-license-no', [AdminRegistrationStaffController::class, 'checkLicenseNo'])->name('admin.registration.staff.check-license-no');
        Route::post('/registration/staff/check-plate-no', [AdminRegistrationStaffController::class, 'checkPlateNo'])->name('admin.registration.staff.check-plate-no');
    });

    Route::middleware('privilege:register_security')->group(function () {
        Route::get('/registration/security', [AdminRegistrationSecurityController::class, 'index'])->name('admin.registration.security');
        Route::post('/registration/security', [AdminRegistrationSecurityController::class, 'store'])
            ->middleware('file.upload.security')
            ->name('admin.registration.security.store');
        Route::post('/registration/security/check-email', [AdminRegistrationSecurityController::class, 'checkEmail'])->name('admin.registration.security.check-email');
        Route::post('/registration/security/check-security-id', [AdminRegistrationSecurityController::class, 'checkSecurityId'])->name('admin.registration.security.check-security-id');
        Route::post('/registration/security/check-license-no', [AdminRegistrationSecurityController::class, 'checkLicenseNo'])->name('admin.registration.security.check-license-no');
        Route::post('/registration/security/check-plate-no', [AdminRegistrationSecurityController::class, 'checkPlateNo'])->name('admin.registration.security.check-plate-no');
    });

    Route::middleware('privilege:register_stakeholders')->group(function () {
        Route::get('/registration/stakeholder', [StakeholderController::class, 'index'])->name('admin.registration.stakeholder');
        Route::post('/registration/stakeholder', [StakeholderController::class, 'store'])
            ->middleware('file.upload.security')
            ->name('admin.registration.stakeholder.store');
        Route::post('/registration/stakeholder/check-email', [StakeholderController::class, 'checkEmail'])->name('admin.registration.stakeholder.check-email');
        Route::post('/registration/stakeholder/check-license-no', [StakeholderController::class, 'checkLicenseNo'])->name('admin.registration.stakeholder.check-license-no');
        Route::post('/registration/stakeholder/check-plate-no', [StakeholderController::class, 'checkPlateNo'])->name('admin.registration.stakeholder.check-plate-no');
    });

    // Registration Routes - Reporters
    Route::middleware('privilege:register_reporters')->group(function () {
        Route::get('/registration/reporter', [ReporterController::class, 'index'])->name('admin.registration.reporter');
        Route::post('/registration/reporter', [ReporterController::class, 'store'])
            ->name('admin.registration.reporter.store');
        Route::post('/registration/reporter/check-email', [ReporterController::class, 'checkEmail'])->name('admin.registration.reporter.check-email');
    });

    // Administrator Registration Routes (Global Admin only)
    Route::middleware(['global.admin'])->group(function () {
        Route::get('/registration/administrator', [AdministratorController::class, 'index'])->name('admin.registration.administrator');
        Route::post('/registration/administrator', [AdministratorController::class, 'store'])
            ->name('admin.registration.administrator.store');
        Route::post('/registration/administrator/check-email', [AdministratorController::class, 'checkEmail'])->name('admin.registration.administrator.check-email');
    });

    // Patrol History Routes - Privilege-based
    Route::middleware('privilege:view_patrol_monitor')->group(function () {
        Route::get('/patrol-history', [PatrolHistoryController::class, 'index'])->name('admin.patrol-history');
        Route::get('/patrol-history/export', [PatrolHistoryController::class, 'export'])->name('admin.patrol-history.export');
    });

    // Metrics API Routes
    Route::prefix('api/metrics')->name('api.metrics.')->group(function () {
        Route::get('/overview', [MetricsController::class, 'overview'])->name('overview');
        Route::get('/violations-per-day', [MetricsController::class, 'violationsPerDay'])->name('violations-per-day');
        Route::get('/payments-monthly', [MetricsController::class, 'paymentsMonthly'])->name('payments-monthly');
        Route::get('/patrol-24h', [MetricsController::class, 'patrolStats24h'])->name('patrol-24h');
    });
});

// Reporter Routes
Route::middleware(['auth', 'user.type:reporter,security'])->group(function () {
    Route::get('/report-user', [ReportUserController::class, 'index'])->name('reporter.report-user');
    Route::get('/report-user/{vehicle}', [ReportUserController::class, 'showReportForm'])->name('reporter.report-form');
    Route::post('/report-user/search', [ReportUserController::class, 'searchVehicle'])->name('reporter.search-vehicle');
    Route::post('/report-user/submit', [ReportUserController::class, 'store'])
        ->middleware('file.upload.security')
        ->name('reporter.report-submit');
    Route::get('/my-reports', [MyReportController::class, 'index'])->name('reporter.my-reports');
});

// Security-only Routes
Route::middleware(['auth', 'user.type:security'])->group(function () {
    // My vehicles route is now handled by the consolidated route above

    // Patrol Routes
    Route::get('/security/patrol-scanner', [PatrolCheckinController::class, 'scanner'])->name('security.patrol-scanner');
    Route::get('/security/patrol-checkin', [PatrolCheckinController::class, 'show'])->name('security.patrol-checkin.show');
    Route::post('/security/patrol-checkin', [PatrolCheckinController::class, 'store'])->name('security.patrol-checkin.store');
    Route::get('/security/patrol-history', [PatrolCheckinController::class, 'history'])->name('security.patrol-history');
});

// Test Admin Dashboard Route
Route::get('/test-admin', function () {
    return view('admin.test', [
        'pageTitle' => 'Test Admin Dashboard',
    ]);
})->middleware('auth');

// Template Demo Route
Route::get('/app', function () {
    return view('layouts.app');
});

Route::get('/template', function () {
    $vehicleTypes = \App\Services\StaticDataCacheService::getVehicleTypes();

    return view('layouts.template', [
        'vehicleTypes' => $vehicleTypes,
    ]);
});
