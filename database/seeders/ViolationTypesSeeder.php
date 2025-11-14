<?php

namespace Database\Seeders;

use App\Models\ViolationType;
use Illuminate\Database\Seeder;

class ViolationTypesSeeder extends Seeder
{
    /**
     * Seed the violation types reference data.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Improper Parking',
                'description' => 'Parking outside of designated areas or exceeding time limits.',
            ],
            [
                'name' => 'Blocking Driveway',
                'description' => 'Blocking driveways or access roads causing obstructions.',
            ],
            [
                'name' => 'Parking on Non Designated Area',
                'description' => 'Parking on sidewalks, grass, or other restricted zones.',
            ],
            [
                'name' => 'Parking on corners',
                'description' => 'Parking too close to corners and intersections.',
            ],
            [
                'name' => 'Disrespecting Personnel in Authority',
                'description' => 'Failing to comply with directives from campus authorities.',
            ],
            [
                'name' => 'No ID Presented / Use of Other Student\'s ID',
                'description' => 'Entering campus without ID or using someone else’s identification.',
            ],
            [
                'name' => 'Improper School Attire',
                'description' => 'Not wearing the prescribed uniform or dress code.',
            ],
            [
                'name' => 'Noisy Muffler (Tambutso)',
                'description' => 'Excessively loud vehicle mufflers causing disturbance.',
            ],
        ];

        foreach ($types as $type) {
            ViolationType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        if ($this->command) {
            $this->command->info('Violation types seeded successfully.');
        }
    }
}
