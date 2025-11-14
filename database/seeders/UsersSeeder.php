<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Seed core user accounts.
     */
    public function run(): void
    {
        $this->call([
            GlobalAdministratorSeeder::class,
            AdministratorsSeeder::class,
            ReportersSeeder::class,
        ]);
    }
}
