<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $vehicleTypes = [
            ['name' => 'Motorcycle'],
            ['name' => 'Car'],
            ['name' => 'SUV'],
            ['name' => 'Van'],
            ['name' => 'Pickup Truck'],
            ['name' => 'Electric Bike'],
            ['name' => 'Electric Scooter'],
        ];

        foreach ($vehicleTypes as $type) {
            VehicleType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
