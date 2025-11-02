<?php

namespace Database\Seeders;

use App\Models\ReporterType;
use Illuminate\Database\Seeder;

class ReporterTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reporterTypes = [
            ['name' => 'Campus Reporter'],
            ['name' => 'External Reporter'],
            ['name' => 'Volunteer Reporter'],
        ];

        foreach ($reporterTypes as $type) {
            ReporterType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
