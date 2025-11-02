<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use Illuminate\Database\Seeder;

class AdminRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'SAS (Student Affairs & Services)'],
            ['name' => 'Chancellor'],
            ['name' => 'Marketing'],
            ['name' => 'Security'],
            ['name' => 'Academic Affairs'],
            ['name' => 'Finance'],
        ];

        foreach ($roles as $role) {
            AdminRole::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
