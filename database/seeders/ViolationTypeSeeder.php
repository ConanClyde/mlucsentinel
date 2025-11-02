<?php

namespace Database\Seeders;

use App\Models\ViolationType;
use Illuminate\Database\Seeder;

class ViolationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $violationTypes = [
            [
                'name' => 'Illegal Parking',
                'description' => 'Vehicle parked in unauthorized or restricted areas',
            ],
            [
                'name' => 'No Parking Sticker',
                'description' => 'Vehicle without valid parking sticker',
            ],
            [
                'name' => 'Expired Sticker',
                'description' => 'Vehicle with expired parking sticker',
            ],
            [
                'name' => 'Speeding',
                'description' => 'Driving above the campus speed limit',
            ],
            [
                'name' => 'Reckless Driving',
                'description' => 'Dangerous or careless driving on campus',
            ],
            [
                'name' => 'Unauthorized Entry',
                'description' => 'Vehicle entering restricted areas',
            ],
            [
                'name' => 'Improper Parking',
                'description' => 'Vehicle not properly parked within designated space',
            ],
            [
                'name' => 'Blocking Traffic',
                'description' => 'Vehicle blocking traffic flow or other vehicles',
            ],
            [
                'name' => 'Wrong Sticker Color',
                'description' => 'Vehicle using incorrect sticker color for user type',
            ],
            [
                'name' => 'Noise Violation',
                'description' => 'Excessive noise from vehicle (loud music, modified exhaust)',
            ],
        ];

        foreach ($violationTypes as $type) {
            ViolationType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
