<?php

namespace Database\Seeders;

use App\Models\StickerRule;
use Illuminate\Database\Seeder;

class StickerRulesSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPalette = [
            'black' => '#000000',
            'white' => '#FFFFFF',
            'green' => '#28A745',
            'blue' => '#007BFF',
            'yellow' => '#FFC107',
            'orange' => '#FD7E14',
            'pink' => '#E83E8C',
            'maroon' => '#800000',
        ];

        $rules = StickerRule::query()->first();
        if (! $rules) {
            StickerRule::create([
                'student_expiration_years' => 4,
                'staff_expiration_years' => 4,
                'security_expiration_years' => 4,
                'stakeholder_expiration_years' => 4,
                'staff_color' => 'maroon',
                'security_color' => 'maroon',
                'student_map' => [
                    '12' => 'blue',
                    '34' => 'green',
                    '56' => 'yellow',
                    '78' => 'pink',
                    '90' => 'orange',
                    'no_plate' => 'white',
                ],
                'stakeholder_map' => [
                    'Guardian' => 'white',
                    'Service Provider' => 'white',
                    'Visitor' => 'black',
                ],
                'palette' => $defaultPalette,
            ]);
        } else {
            // Ensure default palette keys exist if seeding on an existing row
            $palette = is_array($rules->palette) ? $rules->palette : [];
            $rules->palette = $palette + $defaultPalette; // preserve existing, add missing defaults
            $rules->save();
        }
    }
}
