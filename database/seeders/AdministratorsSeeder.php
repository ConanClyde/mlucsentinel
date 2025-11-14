<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Administrator;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class AdministratorsSeeder extends Seeder
{
    /**
     * Seed baseline administrator accounts tied to their roles.
     */
    public function run(): void
    {
        $administrators = [
            [
                'first_name' => 'Samantha',
                'last_name' => 'Security',
                'email' => 'security.admin@example.com',
                'password' => 'password123',
                'role' => 'Security',
            ],
            [
                'first_name' => 'Darius',
                'last_name' => 'Drrm',
                'email' => 'drrm.admin@example.com',
                'password' => 'password123',
                'role' => 'DRRM',
            ],
            [
                'first_name' => 'Martha',
                'last_name' => 'Marketing',
                'email' => 'marketing.admin@example.com',
                'password' => 'password123',
                'role' => 'Marketing',
            ],
        ];

        foreach ($administrators as $admin) {
            $role = AdminRole::where('name', $admin['role'])->first();

            if (! $role) {
                if ($this->command) {
                    $this->command->warn("Admin role '{$admin['role']}' not found. Skipping {$admin['email']}.");
                }

                continue;
            }

            $user = User::updateOrCreate(
                ['email' => $admin['email']],
                Arr::only($admin, ['first_name', 'last_name', 'email']) + [
                    'password' => $admin['password'],
                    'user_type' => UserType::Administrator,
                    'is_active' => true,
                    'email_verified_at' => now(),
                    'two_factor_enabled' => false,
                ]
            );

            Administrator::updateOrCreate(
                ['user_id' => $user->id],
                ['role_id' => $role->id]
            );

            if ($this->command) {
                $this->command->info("Administrator {$admin['first_name']} {$admin['last_name']} seeded with {$admin['role']} role.");
            }
        }
    }
}
