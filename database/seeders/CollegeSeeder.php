<?php

namespace Database\Seeders;

use App\Models\College;
use Illuminate\Database\Seeder;

class CollegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colleges = [
            ['name' => 'College of Arts and Sciences'],
            ['name' => 'College of Business Administration'],
            ['name' => 'College of Computer Studies'],
            ['name' => 'College of Criminal Justice Education'],
            ['name' => 'College of Engineering'],
            ['name' => 'College of Health Sciences'],
            ['name' => 'College of Hospitality Management'],
            ['name' => 'College of Marine Transportation'],
            ['name' => 'College of Marine Engineering'],
            ['name' => 'College of Teacher Education'],
        ];

        foreach ($colleges as $college) {
            College::firstOrCreate(['name' => $college['name']], $college);
        }
    }
}
