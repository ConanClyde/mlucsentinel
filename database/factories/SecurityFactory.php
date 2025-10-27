<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Security>
 */
class SecurityFactory extends Factory
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
            'security_id' => fake()->unique()->numerify('SEC-#####'),
            'license_no' => fake()->unique()->bothify('L#######'),
            'license_image' => null,
            'expiration_date' => fake()->dateTimeBetween('now', '+5 years'),
        ];
    }
}
