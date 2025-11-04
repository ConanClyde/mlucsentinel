<?php

namespace Database\Seeders;

use App\Models\GlobalAdministrator;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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
            'password' => Hash::make('admin123'),
            'user_type' => 'global_administrator',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Create GlobalAdministrator record
        GlobalAdministrator::create(['user_id' => $user->id]);

        $this->command->info("Created Global Administrator: Alvin de Mesa ({$email})");
    }
}
