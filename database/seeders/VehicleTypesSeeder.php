<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypesSeeder extends Seeder
{
    /**
     * Seed the vehicle types reference data.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Motorcycle',
                'requires_plate' => true,
            ],
            [
                'name' => 'Car',
                'requires_plate' => true,
            ],
            [
                'name' => 'Electric Vehicle',
                'requires_plate' => false,
            ],
        ];

        foreach ($types as $type) {
            VehicleType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }

        if ($this->command) {
            $this->command->info('Vehicle types seeded successfully.');
        }
    }
}
