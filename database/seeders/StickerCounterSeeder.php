<?php

namespace Database\Seeders;

use App\Models\StickerCounter;
use Illuminate\Database\Seeder;

class StickerCounterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = ['blue', 'green', 'yellow', 'pink', 'orange', 'white', 'maroon', 'black'];

        foreach ($colors as $color) {
            StickerCounter::firstOrCreate(
                ['color' => $color],
                ['count' => 0]
            );
        }
    }
}
