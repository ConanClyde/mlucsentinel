<?php

namespace App\Console\Commands;

use App\Models\Report;
use App\Models\User;
use App\Notifications\ViolationApprovedNotification;
use Illuminate\Console\Command;

class TestViolationEmail extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:violation-email {email}';

    /**
     * The console command description.
     */
    protected $description = 'Send a test violation approved email to a specified email address';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');

        $this->info('Testing violation email notification...');

        // Find or create a test user
        $testUser = User::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => bcrypt('password'),
                'user_type' => 'student',
                'is_active' => true,
            ]
        );

        $this->info("Test user: {$testUser->first_name} {$testUser->last_name} ({$testUser->email})");

        // Get a sample report (or create a mock one)
        $report = Report::with(['violationType', 'violatorVehicle.type', 'violatorVehicle.user'])
            ->where('status', 'approved')
            ->first();

        if (! $report) {
            $this->warn('No approved reports found. Using first available report...');
            $report = Report::with(['violationType', 'violatorVehicle.type', 'violatorVehicle.user'])->first();
        }

        if (! $report) {
            $this->error('No reports found in the database. Please create a test report first.');

            return self::FAILURE;
        }

        $this->info("Using Report ID: #{$report->id}");
        $this->info("Violation Type: {$report->violationType->name}");
        $this->info("Location: {$report->location}");

        // Send the notification
        try {
            $testUser->notify(new ViolationApprovedNotification($report));

            $this->newLine();
            $this->info('✓ Email notification queued successfully!');
            $this->info("✓ Sent to: {$email}");
            $this->newLine();
            $this->warn("Note: Make sure to run 'php artisan queue:work' to process the email.");
            $this->warn('Check your email inbox (including spam folder) for the notification.');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to send email: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
