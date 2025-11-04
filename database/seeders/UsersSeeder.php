<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\GlobalAdministrator;
use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'ademesa.dev@gmail.com';

        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            $this->command->warn("User with email {$email} already exists. Skipping...");

            return;
        }

        // Create Global Administrator user
        $user = User::create([
            'first_name' => 'Alvin',
            'last_name' => 'de Mesa',
            'email' => $email,
            'password' => 'admin123', // Will be auto-hashed by User model cast
            'user_type' => UserType::GlobalAdministrator,
            'is_active' => true,
            'email_verified_at' => now(),
            'two_factor_enabled' => false,
        ]);

        // Create GlobalAdministrator record
        GlobalAdministrator::create(['user_id' => $user->id]);

        $this->command->info("Created Global Administrator: Alvin de Mesa ({$email})");
        $this->command->info("Password: admin123");
    }
}
