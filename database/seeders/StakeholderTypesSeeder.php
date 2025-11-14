<?php

namespace Database\Seeders;

use App\Models\StakeholderType;
use Illuminate\Database\Seeder;

class StakeholderTypesSeeder extends Seeder
{
    /**
     * Seed the stakeholder types reference data.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Guardian',
                'description' => 'Parents or guardians affiliated with students.',
            ],
            [
                'name' => 'Service Provider',
                'description' => 'Vendors and third-party personnel servicing the campus.',
            ],
            [
                'name' => 'Visitor',
                'description' => 'Guests who temporarily access campus facilities.',
            ],
        ];

        foreach ($types as $typeData) {
            StakeholderType::updateOrCreate(
                ['name' => $typeData['name']],
                $typeData
            );
        }

        if ($this->command) {
            $this->command->info('Stakeholder types seeded successfully.');
        }
    }
}
