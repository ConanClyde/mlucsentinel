<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MapLocationType>
 */
class MapLocationTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement(['Parking Zone', 'Building', 'Gate', 'Patrol Point', 'Security Post']),
            'icon' => fake()->randomElement(['map-pin', 'building', 'shield', 'flag']),
            'default_color' => fake()->hexColor(),
            'description' => fake()->sentence(),
            'is_active' => true,
            'display_order' => fake()->numberBetween(1, 100),
        ];
    }
}
