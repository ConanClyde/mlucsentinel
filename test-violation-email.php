<?php

/**
 * Test script to send violation approved email
 * Run this with: php artisan tinker < test-violation-email.php
 * Or copy-paste the code inside into tinker
 */

// Find a test report (or create one)
$report = \App\Models\Report::with(['violationType', 'violatorVehicle.type', 'violatorVehicle.user'])->first();

if (!$report) {
    echo "No reports found. Please create a test report first.\n";
    exit;
}

// Create a test user with the email
$testUser = \App\Models\User::firstOrCreate(
    ['email' => 'alvin@student.dmmmsu.edu.ph'],
    [
        'first_name' => 'Alvin',
        'last_name' => 'Test',
        'password' => bcrypt('password'),
        'user_type' => 'student',
        'is_active' => true,
    ]
);

echo "Test user created/found: {$testUser->email}\n";

// Send the notification
$testUser->notify(new \App\Notifications\ViolationApprovedNotification($report));

echo "Email notification sent to: {$testUser->email}\n";
echo "Check the email inbox and queue:work logs.\n";
