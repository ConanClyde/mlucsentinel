<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ViolationType>
 */
class ViolationTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Illegal Parking',
                'Speeding',
                'No Parking Sticker',
                'Expired Sticker',
                'Unauthorized Entry',
                'Reckless Driving',
            ]),
            'description' => fake()->sentence(),
        ];
    }
}
