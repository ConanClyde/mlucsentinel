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
                'icon' => 'square-parking',
                'default_color' => '#3B82F6',
                'description' => 'Designated parking areas for vehicles',
                'is_active' => true,
                'display_order' => 1,
            ],
            [
                'name' => 'Building',
                'icon' => 'building-office',
                'default_color' => '#8B5CF6',
                'description' => 'Academic and administrative buildings',
                'is_active' => true,
                'display_order' => 2,
            ],
            [
                'name' => 'Patrol Point',
                'icon' => 'shield-check',
                'default_color' => '#EF4444',
                'description' => 'Security patrol check-in locations',
                'is_active' => true,
                'display_order' => 3,
            ],
            [
                'name' => 'Gate/Entrance',
                'icon' => 'arrow-right-circle',
                'default_color' => '#10B981',
                'description' => 'Campus entry and exit points',
                'is_active' => true,
                'display_order' => 4,
            ],
            [
                'name' => 'Security Post',
                'icon' => 'shield-exclamation',
                'default_color' => '#F59E0B',
                'description' => 'Security guard stations',
                'is_active' => true,
                'display_order' => 5,
            ],
            [
                'name' => 'Restricted Area',
                'icon' => 'no-symbol',
                'default_color' => '#DC2626',
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
