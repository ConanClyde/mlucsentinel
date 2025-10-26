<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\HomeController as AdminHomeController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Controllers\Admin\VehiclesController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\StickersController;
use App\Http\Controllers\Admin\Users\StudentsController;
use App\Http\Controllers\Admin\Users\StaffController;
use App\Http\Controllers\Admin\Users\SecurityController as AdminUsersSecurityController;
use App\Http\Controllers\Admin\Users\ReportersController;
use App\Http\Controllers\Admin\Users\StakeholdersController;
use App\Http\Controllers\Admin\Users\AdministratorsController;
use App\Http\Controllers\Admin\Registration\StudentController;
use App\Http\Controllers\Admin\Registration\StaffController as AdminRegistrationStaffController;
use App\Http\Controllers\Admin\Registration\SecurityController as AdminRegistrationSecurityController;
use App\Http\Controllers\Admin\Registration\ReporterController;
use App\Http\Controllers\Admin\Registration\StakeholderController;
use App\Http\Controllers\Admin\Registration\AdministratorController;
use App\Http\Controllers\Reporter\DashboardController as ReporterDashboardController;
use App\Http\Controllers\Reporter\HomeController as ReporterHomeController;
use App\Http\Controllers\Reporter\ReportUserController;
use App\Http\Controllers\Reporter\MyReportController;
use App\Http\Controllers\Reporter\MyVehiclesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Landing Page
Route::get('/', [AuthController::class, 'landing'])->name('landing');

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    
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
    Route::get('/reports', [ReportsController::class, 'index'])->name('admin.reports');
    Route::get('/stickers', [StickersController::class, 'index'])->name('admin.stickers');
    
    // User Types Routes
    Route::get('/users/students', [StudentsController::class, 'index'])->name('admin.users.students');
    Route::get('/users/staff', [StaffController::class, 'index'])->name('admin.users.staff');
    Route::get('/users/security', [AdminUsersSecurityController::class, 'index'])->name('admin.users.security');
    Route::get('/users/reporters', [ReportersController::class, 'index'])->name('admin.users.reporters');
    Route::put('/users/reporters/{reporter}', [ReportersController::class, 'update'])->name('admin.users.reporters.update');
    Route::delete('/users/reporters/{reporter}', [ReportersController::class, 'destroy'])->name('admin.users.reporters.destroy');
    
    Route::get('/users/stakeholders', [StakeholdersController::class, 'index'])->name('admin.users.stakeholders');
    Route::get('/users/administrators', [AdministratorsController::class, 'index'])->name('admin.users.administrators');
    Route::post('/users/administrators', [AdministratorsController::class, 'store'])->name('admin.users.administrators.store');
    Route::put('/users/administrators/{administrator}', [AdministratorsController::class, 'update'])->name('admin.users.administrators.update');
    Route::delete('/users/administrators/{administrator}', [AdministratorsController::class, 'destroy'])->name('admin.users.administrators.destroy');
    
    // Registration Routes
    Route::get('/registration/student', [StudentController::class, 'index'])->name('admin.registration.student');
    Route::get('/registration/staff', [AdminRegistrationStaffController::class, 'index'])->name('admin.registration.staff');
    Route::get('/registration/security', [AdminRegistrationSecurityController::class, 'index'])->name('admin.registration.security');
    Route::get('/registration/reporter', [ReporterController::class, 'index'])->name('admin.registration.reporter');
    Route::post('/registration/reporter', [ReporterController::class, 'store'])->name('admin.registration.reporter.store');
    Route::post('/registration/reporter/check-email', [ReporterController::class, 'checkEmail'])->name('admin.registration.reporter.check-email');
    Route::get('/registration/stakeholder', [StakeholderController::class, 'index'])->name('admin.registration.stakeholder');
    Route::get('/registration/administrator', [AdministratorController::class, 'index'])->name('admin.registration.administrator');
    Route::post('/registration/administrator', [AdministratorController::class, 'store'])->name('admin.registration.administrator.store');
    Route::post('/registration/administrator/check-email', [AdministratorController::class, 'checkEmail'])->name('admin.registration.administrator.check-email');
});

// Reporter Routes
Route::middleware(['auth', 'user.type:reporter,security'])->group(function () {
    Route::get('/report-user', [ReportUserController::class, 'index'])->name('reporter.report-user');
    Route::get('/my-reports', [MyReportController::class, 'index'])->name('reporter.my-reports');
    Route::get('/my-vehicles', [MyVehiclesController::class, 'index'])->name('reporter.my-vehicles');
});



// Test Admin Dashboard Route
Route::get('/test-admin', function () {
    return view('admin.test', [
        'pageTitle' => 'Test Admin Dashboard'
    ]);
})->middleware('auth');

// Template Demo Route
Route::get('/app', function () {
    return view('layouts.app');
});




Route::get('/template', function () {
    return view('layouts.template');
});