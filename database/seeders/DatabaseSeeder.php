<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed reference data first
        $this->call([
            AdminRoleSeeder::class,
            CollegeSeeder::class,
            VehicleTypeSeeder::class,
            ViolationTypeSeeder::class,
            MapLocationTypeSeeder::class,
            StakeholderTypeSeeder::class,
            ReporterTypeSeeder::class,
            StickerCounterSeeder::class,
        ]);

        // Optional: Uncomment to create test user
        // User::factory()->create([
        //     'first_name' => 'Test',
        //     'last_name' => 'User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
