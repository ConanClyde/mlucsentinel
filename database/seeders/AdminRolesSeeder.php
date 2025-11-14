<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use Illuminate\Database\Seeder;

class AdminRolesSeeder extends Seeder
{
    /**
     * Seed the admin roles reference data.
     */
    public function run(): void
    {
        $roles = [
            'Chancellor',
            'DRRM',
            'Planning',
            'Security',
            'Auxiliary Services',
            'SAS (Student Affairs & Services)',
            'Marketing',
        ];

        foreach ($roles as $roleName) {
            AdminRole::updateOrCreate(
                ['name' => $roleName],
                ['name' => $roleName]
            );
        }

        if ($this->command) {
            $this->command->info('Admin roles seeded successfully.');
        }
    }
}
