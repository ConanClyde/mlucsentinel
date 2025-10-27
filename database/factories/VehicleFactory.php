<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'type_id' => \App\Models\VehicleType::factory(),
            'plate_no' => fake()->optional()->bothify('###-####'),
            'color' => fake()->randomElement(['blue', 'green', 'yellow', 'pink', 'orange', 'white', 'maroon']),
            'number' => fake()->numerify('####'),
            'sticker' => null,
            'is_active' => true,
        ];
    }
}
