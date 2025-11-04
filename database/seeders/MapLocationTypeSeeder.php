<?php

namespace Database\Seeders;

use App\Models\MapLocationType;
use Illuminate\Database\Seeder;

class MapLocationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locationTypes = [
            [
                'name' => 'Parking Zone',
                'default_color' => '#3B82F6',
                'requires_polygon' => true,
                'description' => 'Designated parking areas for vehicles',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Building',
                'default_color' => '#8B5CF6',
                'requires_polygon' => true,
                'description' => 'Academic and administrative buildings',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Patrol Point',
                'default_color' => '#EF4444',
                'requires_polygon' => true,
                'description' => 'Security patrol check-in locations',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Gate/Entrance',
                'default_color' => '#10B981',
                'requires_polygon' => true,
                'description' => 'Campus entry and exit points',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Security Post',
                'default_color' => '#F59E0B',
                'requires_polygon' => true,
                'description' => 'Security guard stations',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'Restricted Area',
                'default_color' => '#DC2626',
                'requires_polygon' => true,
                'description' => 'Areas with restricted vehicle access',
                'is_active' => true,
                'display_order' => 6,
            ],
        ];

        foreach ($locationTypes as $type) {
            MapLocationType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
