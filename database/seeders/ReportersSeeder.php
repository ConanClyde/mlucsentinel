<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Reporter;
use App\Models\ReporterRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class ReportersSeeder extends Seeder
{
    /**
     * Seed sample reporter accounts linked to reporter roles.
     */
    public function run(): void
    {
        $reporters = [
            [
                'first_name' => 'Sofia',
                'last_name' => 'Santiago',
                'email' => 'sbo.reporter@example.com',
                'password' => 'password123',
                'role' => 'SBO (Student Body Organization)',
            ],
            [
                'first_name' => 'Diego',
                'last_name' => 'Drrm',
                'email' => 'drrm.reporter@example.com',
                'password' => 'password123',
                'role' => 'DRRM Facilitators',
            ],
            [
                'first_name' => 'Sara',
                'last_name' => 'Sas',
                'email' => 'sas.reporter@example.com',
                'password' => 'password123',
                'role' => 'SAS Facilitators',
            ],
            [
                'first_name' => 'Samuel',
                'last_name' => 'Security',
                'email' => 'security.guard@example.com',
                'password' => 'password123',
                'role' => 'Security Guard',
            ],
        ];

        foreach ($reporters as $reporter) {
            $role = ReporterRole::where('name', $reporter['role'])->first();

            if (! $role) {
                if ($this->command) {
                    $this->command->warn("Reporter role '{$reporter['role']}' not found. Skipping {$reporter['email']}.");
                }

                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $reporter['email']],
                Arr::only($reporter, ['first_name', 'last_name', 'email']) + [
                    'password' => $reporter['password'],
                    'user_type' => UserType::Reporter,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'two_factor_enabled' => false,
                ]
            );

            Reporter::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'reporter_role_id' => $role->id,
                    'is_active' => true,
                ]
            );

            if ($this->command) {
                $this->command->info("Reporter {$reporter['first_name']} {$reporter['last_name']} seeded as {$reporter['role']}.");
            }
        }
    }
}
