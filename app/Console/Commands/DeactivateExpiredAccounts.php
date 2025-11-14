<?php

namespace App\Console\Commands;

use App\Events\SecurityUpdated;
use App\Events\StaffUpdated;
use App\Events\StakeholderUpdated;
use App\Events\StudentUpdated;
use App\Events\UserStatusChanged;
use App\Events\UserUpdated;
use App\Models\Reporter;
use App\Models\Security;
use App\Models\Staff;
use App\Models\Stakeholder;
use App\Models\Student;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate user accounts that have reached their expiration date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Checking for expired accounts...');

        $today = now()->startOfDay();
        $deactivatedCount = 0;
        $errors = [];

        // Check Students
        $students = Student::where('expiration_date', '<=', $today)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($students as $student) {
            try {
                DB::transaction(function () use ($student, &$deactivatedCount) {
                    $user = $student->user;
                    $user->is_active = false;
                    $user->save();

                    // Broadcast status change
                    broadcast(new UserStatusChanged($user, false));

                    // Broadcast student update for real-time UI
                    broadcast(new StudentUpdated($student->fresh(['user', 'college', 'program']), 'updated', 'System'));

                    // Broadcast user update
                    broadcast(new UserUpdated($user, 'updated', 'System'));

                    $deactivatedCount++;
                });

                $this->line("Deactivated student: {$student->user->email}");
            } catch (\Exception $e) {
                $errors[] = "Student ID {$student->id}: {$e->getMessage()}";
                $this->error("Failed to deactivate student {$student->user->email}: {$e->getMessage()}");
            }
        }

        // Check Staff
        $staff = Staff::where('expiration_date', '<=', $today)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($staff as $staffMember) {
            try {
                DB::transaction(function () use ($staffMember, &$deactivatedCount) {
                    $user = $staffMember->user;
                    $user->is_active = false;
                    $user->save();

                    // Broadcast status change
                    broadcast(new UserStatusChanged($user, false));

                    // Broadcast staff update for real-time UI
                    broadcast(new StaffUpdated($staffMember->fresh('user'), 'updated', 'System'));

                    // Broadcast user update
                    broadcast(new UserUpdated($user, 'updated', 'System'));

                    $deactivatedCount++;
                });

                $this->line("Deactivated staff: {$staffMember->user->email}");
            } catch (\Exception $e) {
                $errors[] = "Staff ID {$staffMember->id}: {$e->getMessage()}";
                $this->error("Failed to deactivate staff {$staffMember->user->email}: {$e->getMessage()}");
            }
        }

        // Check Stakeholders
        $stakeholders = Stakeholder::where('expiration_date', '<=', $today)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($stakeholders as $stakeholder) {
            try {
                DB::transaction(function () use ($stakeholder, &$deactivatedCount) {
                    $user = $stakeholder->user;
                    $user->is_active = false;
                    $user->save();

                    // Broadcast status change
                    broadcast(new UserStatusChanged($user, false));

                    // Broadcast stakeholder update for real-time UI
                    broadcast(new StakeholderUpdated($stakeholder->fresh(['user', 'type']), 'updated', 'System'));

                    // Broadcast user update
                    broadcast(new UserUpdated($user, 'updated', 'System'));

                    $deactivatedCount++;
                });

                $this->line("Deactivated stakeholder: {$stakeholder->user->email}");
            } catch (\Exception $e) {
                $errors[] = "Stakeholder ID {$stakeholder->id}: {$e->getMessage()}";
                $this->error("Failed to deactivate stakeholder {$stakeholder->user->email}: {$e->getMessage()}");
            }
        }

        // Check Security
        $security = Security::where('expiration_date', '<=', $today)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($security as $securityMember) {
            try {
                DB::transaction(function () use ($securityMember, &$deactivatedCount) {
                    $user = $securityMember->user;
                    $user->is_active = false;
                    $user->save();

                    // Broadcast status change
                    broadcast(new UserStatusChanged($user, false));

                    // Broadcast security update for real-time UI
                    broadcast(new SecurityUpdated($securityMember->fresh('user'), 'updated', 'System'));

                    // Broadcast user update
                    broadcast(new UserUpdated($user, 'updated', 'System'));

                    $deactivatedCount++;
                });

                $this->line("Deactivated security: {$securityMember->user->email}");
            } catch (\Exception $e) {
                $errors[] = "Security ID {$securityMember->id}: {$e->getMessage()}";
                $this->error("Failed to deactivate security {$securityMember->user->email}: {$e->getMessage()}");
            }
        }

        // Check Reporters
        $reporters = Reporter::where('expiration_date', '<=', $today)
            ->where('is_active', true)
            ->whereHas('user', function ($query) {
                $query->where('is_active', true);
            })
            ->with('user')
            ->get();

        foreach ($reporters as $reporter) {
            try {
                DB::transaction(function () use ($reporter, &$deactivatedCount) {
                    $user = $reporter->user;
                    $user->is_active = false;
                    $user->save();

                    // Also deactivate the reporter record
                    $reporter->is_active = false;
                    $reporter->save();

                    // Broadcast status change
                    broadcast(new UserStatusChanged($user, false));

                    // Broadcast user update
                    broadcast(new UserUpdated($user, 'updated', 'System'));

                    $deactivatedCount++;
                });

                $this->line("Deactivated reporter: {$reporter->user->email}");
            } catch (\Exception $e) {
                $errors[] = "Reporter ID {$reporter->id}: {$e->getMessage()}";
                $this->error("Failed to deactivate reporter {$reporter->user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Expiration deactivation completed. Deactivated: {$deactivatedCount}");

        if (! empty($errors)) {
            $this->warn('Some accounts could not be deactivated:');
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
        }

        Log::info('Account expiration deactivation completed', [
            'date' => $today->format('Y-m-d'),
            'deactivated_count' => $deactivatedCount,
            'errors' => $errors,
        ]);

        return self::SUCCESS;
    }
}
