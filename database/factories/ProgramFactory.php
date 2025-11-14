<?php

namespace Database\Factories;

use App\Models\College;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories.Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'college_id' => College::factory(),
            'code' => strtoupper(fake()->unique()->bothify('PRG-###')),
            'name' => 'Bachelor of '.fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
