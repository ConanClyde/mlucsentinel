<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\GlobalAdministrator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class GlobalAdministratorSeeder extends Seeder
{
    /**
     * Seed the primary global administrator account.
     */
    public function run(): void
    {
        $admin = [
            'first_name' => 'Alvin',
            'last_name' => 'de Mesa',
            'email' => 'ademesa.dev@gmail.com',
            'password' => 'admin123', // hashed automatically by model cast
        ];

        $user = User::updateOrCreate(
            ['email' => $admin['email']],
            Arr::only($admin, ['first_name', 'last_name', 'email']) + [
                'password' => $admin['password'],
                'user_type' => UserType::GlobalAdministrator,
                'is_active' => true,
                'email_verified_at' => now(),
                'two_factor_enabled' => false,
            ]
        );

        GlobalAdministrator::firstOrCreate(['user_id' => $user->id]);

        if ($this->command) {
            $this->command->info("Global administrator available at {$admin['email']} (password: {$admin['password']})");
        }
    }
}
