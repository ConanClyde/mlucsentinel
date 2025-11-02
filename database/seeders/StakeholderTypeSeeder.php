<?php

namespace Database\Seeders;

use App\Models\StakeholderType;
use Illuminate\Database\Seeder;

class StakeholderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stakeholderTypes = [
            ['name' => 'Visitor'],
            ['name' => 'Guardian'],
            ['name' => 'Service Provider'],
        ];

        foreach ($stakeholderTypes as $type) {
            StakeholderType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
