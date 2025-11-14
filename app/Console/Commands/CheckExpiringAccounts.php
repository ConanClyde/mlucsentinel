<?php

namespace App\Console\Commands;

use App\Mail\AccountExpirationMail;
use App\Models\Notification;
use App\Models\Reporter;
use App\Models\Security;
use App\Models\Staff;
use App\Models\Stakeholder;
use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckExpiringAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:check-expiring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for accounts expiring in 7 days and send email notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for accounts expiring in 7 days...');

        $expirationDate = now()->addDays(7)->format('Y-m-d');
        $sentCount = 0;
        $skippedCount = 0;

        // Check Students
        $students = Student::where('expiration_date', $expirationDate)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($students as $student) {
            if ($this->shouldSendNotification($student->user, $expirationDate)) {
                $this->sendNotification($student->user, $student->expiration_date);
                $sentCount++;
            } else {
                $skippedCount++;
            }
        }

        // Check Staff
        $staff = Staff::where('expiration_date', $expirationDate)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($staff as $staffMember) {
            if ($this->shouldSendNotification($staffMember->user, $expirationDate)) {
                $this->sendNotification($staffMember->user, $staffMember->expiration_date);
                $sentCount++;
            } else {
                $skippedCount++;
            }
        }

        // Check Stakeholders
        $stakeholders = Stakeholder::where('expiration_date', $expirationDate)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($stakeholders as $stakeholder) {
            if ($this->shouldSendNotification($stakeholder->user, $expirationDate)) {
                $this->sendNotification($stakeholder->user, $stakeholder->expiration_date);
                $sentCount++;
            } else {
                $skippedCount++;
            }
        }

        // Check Security
        $security = Security::where('expiration_date', $expirationDate)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($security as $securityMember) {
            if ($this->shouldSendNotification($securityMember->user, $expirationDate)) {
                $this->sendNotification($securityMember->user, $securityMember->expiration_date);
                $sentCount++;
            } else {
                $skippedCount++;
            }
        }

        // Check Reporters
        $reporters = Reporter::where('expiration_date', $expirationDate)
            ->where('is_active', true)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($reporters as $reporter) {
            if ($this->shouldSendNotification($reporter->user, $expirationDate)) {
                $this->sendNotification($reporter->user, $reporter->expiration_date);
                $sentCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->info("Expiration check completed. Sent: {$sentCount}, Skipped: {$skippedCount}");

        Log::info('Account expiration check completed', [
            'expiration_date' => $expirationDate,
            'sent_count' => $sentCount,
            'skipped_count' => $skippedCount,
        ]);

        return self::SUCCESS;
    }

    /**
     * Check if notification should be sent (avoid duplicates).
     */
    private function shouldSendNotification(User $user, string $expirationDate): bool
    {
        // Check if we've already sent a notification for this expiration date in the last 24 hours
        $recentNotification = Notification::where('user_id', $user->id)
            ->where('type', 'account_expiration')
            ->whereRaw("JSON_EXTRACT(data, '$.expiration_date') = ?", [$expirationDate])
            ->where('created_at', '>=', now()->subDay())
            ->exists();

        return ! $recentNotification;
    }

    /**
     * Send expiration notification email.
     */
    private function sendNotification(User $user, $expirationDate): void
    {
        try {
            Mail::to($user->email)->send(new AccountExpirationMail($user, $expirationDate));

            // Create in-app notification
            Notification::create([
                'user_id' => $user->id,
                'type' => 'account_expiration',
                'title' => 'Account Expiration Notice',
                'message' => "Your account will expire on {$expirationDate->format('F d, Y')}. Please contact an administrator to renew your account.",
                'data' => [
                    'expiration_date' => $expirationDate->format('Y-m-d'),
                    'days_until_expiration' => now()->diffInDays($expirationDate),
                ],
            ]);

            $this->line("Sent expiration notice to: {$user->email}");
        } catch (\Exception $e) {
            $this->error("Failed to send email to {$user->email}: {$e->getMessage()}");
            Log::error('Failed to send expiration email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
