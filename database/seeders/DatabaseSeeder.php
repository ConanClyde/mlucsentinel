<?php

namespace Database\Seeders;

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
            AdminRolesSeeder::class,
            VehicleTypesSeeder::class,
            ViolationTypesSeeder::class,
            MapLocationTypeSeeder::class,
            StickerCounterSeeder::class,
            StakeholderTypesSeeder::class,
            PrivilegesSeeder::class,
            RolePrivilegesSeeder::class,
            FeesSeeder::class,
            CollegesSeeder::class,
            ProgramsSeeder::class,
            ReporterRolesSeeder::class,
            StickerRulesSeeder::class,
            UsersSeeder::class,
        ]);
    }
}
