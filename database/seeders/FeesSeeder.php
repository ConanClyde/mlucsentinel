<?php

namespace Database\Seeders;

use App\Models\Fee;
use Illuminate\Database\Seeder;

class FeesSeeder extends Seeder
{
    /**
     * Seed the application fees.
     */
    public function run(): void
    {
        $fees = [
            [
                'name' => 'sticker_fee',
                'display_name' => 'Sticker Fee',
                'amount' => 15.00,
                'description' => 'Fee for vehicle sticker registration',
            ],
        ];

        foreach ($fees as $fee) {
            Fee::updateOrCreate(
                ['name' => $fee['name']],
                $fee
            );
        }

        if ($this->command) {
            $this->command->info('Fees seeded successfully.');
        }
    }
}
