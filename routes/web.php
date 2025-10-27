<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\Registration\AdministratorController;
use App\Http\Controllers\Admin\Registration\ReporterController;
use App\Http\Controllers\Admin\Registration\SecurityController as AdminRegistrationSecurityController;
use App\Http\Controllers\Admin\Registration\StaffController as AdminRegistrationStaffController;
use App\Http\Controllers\Admin\Registration\StakeholderController;
use App\Http\Controllers\Admin\Registration\StudentController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\StickersController;
use App\Http\Controllers\Admin\Users\AdministratorsController;
use App\Http\Controllers\Admin\Users\ReportersController;
use App\Http\Controllers\Admin\Users\SecurityController as AdminUsersSecurityController;
use App\Http\Controllers\Admin\Users\StaffController;
use App\Http\Controllers\Admin\Users\StakeholdersController;
use App\Http\Controllers\Admin\Users\StudentsController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\VehiclesController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Reporter\HomeController as ReporterHomeController;
use App\Http\Controllers\Reporter\MyReportController;
use App\Http\Controllers\Reporter\MyVehiclesController;
use App\Http\Controllers\Reporter\ReportUserController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [AuthController::class, 'landing'])->name('landing');

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login - Rate limit: max 5 attempts per minute
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:5,1')
        ->name('login.post');

    // Register
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');

    // Password Reset
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetCode'])->name('password.email');
    Route::get('/reset-password', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
    Route::post('/validate-reset-code', [PasswordResetController::class, 'validateResetCode'])->name('password.validate');
});

// Logout
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

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

    // Redirect based on user type
    if (in_array($user->user_type, ['global_administrator', 'administrator'])) {
        return app(AdminHomeController::class)->index();
    } elseif (in_array($user->user_type, ['reporter', 'security'])) {
        return app(ReporterHomeController::class)->index();
    } else {
        // For other user types, redirect to admin home as fallback
        return app(AdminHomeController::class)->index();
    }
})->middleware('auth')->name('home');

// Profile Route (Protected Route) - available to all authenticated users
Route::get('/profile', [ProfileController::class, 'index'])->middleware('auth')->name('profile');
Route::post('/profile/update', [ProfileController::class, 'update'])->middleware('auth')->name('profile.update');
Route::post('/profile/change-password', [ProfileController::class, 'changePassword'])->middleware('auth')->name('profile.change-password');
Route::post('/profile/verify-password', [ProfileController::class, 'verifyPassword'])->middleware('auth')->name('profile.verify-password');
Route::delete('/profile/delete', [ProfileController::class, 'delete'])->middleware('auth')->name('profile.delete');

// Admin Routes
Route::middleware(['auth', 'user.type:global_administrator,administrator'])->group(function () {
    Route::get('/users', [UsersController::class, 'index'])->name('admin.users');
    Route::get('/vehicles', [VehiclesController::class, 'index'])->name('admin.vehicles');
    Route::get('/vehicles/data', [VehiclesController::class, 'data'])->name('admin.vehicles.data');
    Route::delete('/vehicles/{vehicle}', [VehiclesController::class, 'destroy'])->name('admin.vehicles.destroy');
    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports');
    Route::put('/reports/{report}/status', [ReportsController::class, 'updateStatus'])->name('admin.reports.status');

    // Stickers Routes - Marketing Admin Only
    Route::middleware(['marketing.admin'])->group(function () {
        Route::get('/stickers', [StickersController::class, 'index'])->name('admin.stickers');
        Route::get('/stickers/data', [StickersController::class, 'data'])->name('admin.stickers.data');
        Route::get('/stickers/search-users', [StickersController::class, 'searchUsers'])->name('admin.stickers.search-users');
        Route::post('/stickers/request', [StickersController::class, 'createRequest'])->name('admin.stickers.request');
        Route::patch('/stickers/{payment}/pay', [StickersController::class, 'markAsPaid'])->name('admin.stickers.pay');
        Route::patch('/stickers/{payment}/cancel', [StickersController::class, 'cancel'])->name('admin.stickers.cancel');
    });

    // User Types Routes
    Route::get('/users/students', [StudentsController::class, 'index'])->name('admin.users.students');
    Route::get('/users/students/data', [StudentsController::class, 'data'])->name('admin.users.students.data');
    Route::put('/users/students/{student}', [StudentsController::class, 'update'])->name('admin.users.students.update');
    Route::delete('/users/students/{student}', [StudentsController::class, 'destroy'])->name('admin.users.students.destroy');
    Route::get('/users/staff', [StaffController::class, 'index'])->name('admin.users.staff');
    Route::get('/users/staff/data', [StaffController::class, 'data'])->name('admin.users.staff.data');
    Route::put('/users/staff/{staff}', [StaffController::class, 'update'])->name('admin.users.staff.update');
    Route::delete('/users/staff/{staff}', [StaffController::class, 'destroy'])->name('admin.users.staff.destroy');
    Route::get('/users/security', [AdminUsersSecurityController::class, 'index'])->name('admin.users.security');
    Route::get('/users/security/data', [AdminUsersSecurityController::class, 'data'])->name('admin.users.security.data');
    Route::put('/users/security/{security}', [AdminUsersSecurityController::class, 'update'])->name('admin.users.security.update');
    Route::delete('/users/security/{security}', [AdminUsersSecurityController::class, 'destroy'])->name('admin.users.security.destroy');
    Route::get('/users/reporters', [ReportersController::class, 'index'])->name('admin.users.reporters');
    Route::put('/users/reporters/{reporter}', [ReportersController::class, 'update'])->name('admin.users.reporters.update');
    Route::delete('/users/reporters/{reporter}', [ReportersController::class, 'destroy'])->name('admin.users.reporters.destroy');

    Route::get('/users/stakeholders', [StakeholdersController::class, 'index'])->name('admin.users.stakeholders');
    Route::get('/users/stakeholders/data', [StakeholdersController::class, 'data'])->name('admin.users.stakeholders.data');
    Route::put('/users/stakeholders/{stakeholder}', [StakeholdersController::class, 'update'])->name('admin.users.stakeholders.update');
    Route::delete('/users/stakeholders/{stakeholder}', [StakeholdersController::class, 'destroy'])->name('admin.users.stakeholders.destroy');
    Route::get('/users/administrators', [AdministratorsController::class, 'index'])->name('admin.users.administrators');
    Route::post('/users/administrators', [AdministratorsController::class, 'store'])->name('admin.users.administrators.store');
    Route::put('/users/administrators/{administrator}', [AdministratorsController::class, 'update'])->name('admin.users.administrators.update');
    Route::delete('/users/administrators/{administrator}', [AdministratorsController::class, 'destroy'])->name('admin.users.administrators.destroy');

    // Registration Routes
    Route::get('/registration/student', [StudentController::class, 'index'])->name('admin.registration.student');
    Route::post('/registration/student', [StudentController::class, 'store'])->name('admin.registration.student.store');
    Route::post('/registration/student/check-email', [StudentController::class, 'checkEmail'])->name('admin.registration.student.check-email');
    Route::post('/registration/student/check-student-id', [StudentController::class, 'checkStudentId'])->name('admin.registration.student.check-student-id');
    Route::post('/registration/student/check-license-no', [StudentController::class, 'checkLicenseNo'])->name('admin.registration.student.check-license-no');
    Route::post('/registration/student/check-plate-no', [StudentController::class, 'checkPlateNo'])->name('admin.registration.student.check-plate-no');
    Route::get('/registration/staff', [AdminRegistrationStaffController::class, 'index'])->name('admin.registration.staff');
    Route::post('/registration/staff', [AdminRegistrationStaffController::class, 'store'])->name('admin.registration.staff.store');
    Route::post('/registration/staff/check-email', [AdminRegistrationStaffController::class, 'checkEmail'])->name('admin.registration.staff.check-email');
    Route::post('/registration/staff/check-staff-id', [AdminRegistrationStaffController::class, 'checkStaffId'])->name('admin.registration.staff.check-staff-id');
    Route::post('/registration/staff/check-license-no', [AdminRegistrationStaffController::class, 'checkLicenseNo'])->name('admin.registration.staff.check-license-no');
    Route::post('/registration/staff/check-plate-no', [AdminRegistrationStaffController::class, 'checkPlateNo'])->name('admin.registration.staff.check-plate-no');
    Route::get('/registration/security', [AdminRegistrationSecurityController::class, 'index'])->name('admin.registration.security');
    Route::post('/registration/security', [AdminRegistrationSecurityController::class, 'store'])->name('admin.registration.security.store');
    Route::post('/registration/security/check-email', [AdminRegistrationSecurityController::class, 'checkEmail'])->name('admin.registration.security.check-email');
    Route::post('/registration/security/check-security-id', [AdminRegistrationSecurityController::class, 'checkSecurityId'])->name('admin.registration.security.check-security-id');
    Route::post('/registration/security/check-license-no', [AdminRegistrationSecurityController::class, 'checkLicenseNo'])->name('admin.registration.security.check-license-no');
    Route::post('/registration/security/check-plate-no', [AdminRegistrationSecurityController::class, 'checkPlateNo'])->name('admin.registration.security.check-plate-no');
    Route::get('/registration/reporter', [ReporterController::class, 'index'])->name('admin.registration.reporter');
    Route::post('/registration/reporter', [ReporterController::class, 'store'])->name('admin.registration.reporter.store');
    Route::post('/registration/reporter/check-email', [ReporterController::class, 'checkEmail'])->name('admin.registration.reporter.check-email');
    Route::get('/registration/stakeholder', [StakeholderController::class, 'index'])->name('admin.registration.stakeholder');
    Route::post('/registration/stakeholder', [StakeholderController::class, 'store'])->name('admin.registration.stakeholder.store');
    Route::post('/registration/stakeholder/check-email', [StakeholderController::class, 'checkEmail'])->name('admin.registration.stakeholder.check-email');
    Route::post('/registration/stakeholder/check-license-no', [StakeholderController::class, 'checkLicenseNo'])->name('admin.registration.stakeholder.check-license-no');
    Route::post('/registration/stakeholder/check-plate-no', [StakeholderController::class, 'checkPlateNo'])->name('admin.registration.stakeholder.check-plate-no');
    Route::get('/registration/administrator', [AdministratorController::class, 'index'])->name('admin.registration.administrator');
    Route::post('/registration/administrator', [AdministratorController::class, 'store'])->name('admin.registration.administrator.store');
    Route::post('/registration/administrator/check-email', [AdministratorController::class, 'checkEmail'])->name('admin.registration.administrator.check-email');
});

// Reporter Routes
Route::middleware(['auth', 'user.type:reporter,security'])->group(function () {
    Route::get('/report-user', [ReportUserController::class, 'index'])->name('reporter.report-user');
    Route::get('/report-user/{vehicle}', [ReportUserController::class, 'showReportForm'])->name('reporter.report-form');
    Route::post('/report-user/search', [ReportUserController::class, 'searchVehicle'])->name('reporter.search-vehicle');
    // Rate limit: max 10 reports per minute per user
    Route::post('/report-user/submit', [ReportUserController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('reporter.report-submit');
    Route::get('/my-reports', [MyReportController::class, 'index'])->name('reporter.my-reports');
});

// Security-only Routes
Route::middleware(['auth', 'user.type:security'])->group(function () {
    Route::get('/my-vehicles', [MyVehiclesController::class, 'index'])->name('reporter.my-vehicles');
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
    return view('layouts.template');
});
