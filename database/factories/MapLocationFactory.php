<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MapLocation>
 */
class MapLocationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type_id' => \App\Models\MapLocationType::factory(),
            'name' => fake()->words(3, true),
            'short_code' => strtoupper(fake()->lexify('???')),
            'description' => fake()->sentence(),
            'color' => fake()->hexColor(),
            'vertices' => [
                ['x' => fake()->randomFloat(2, 0, 1000), 'y' => fake()->randomFloat(2, 0, 1000)],
                ['x' => fake()->randomFloat(2, 0, 1000), 'y' => fake()->randomFloat(2, 0, 1000)],
                ['x' => fake()->randomFloat(2, 0, 1000), 'y' => fake()->randomFloat(2, 0, 1000)],
            ],
            'center_x' => fake()->randomFloat(4, 0, 1000),
            'center_y' => fake()->randomFloat(4, 0, 1000),
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 100),
        ];
    }
}
