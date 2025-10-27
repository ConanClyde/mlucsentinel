<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
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
            'college_id' => \App\Models\College::factory(),
            'student_id' => fake()->unique()->numerify('#####'),
            'license_no' => fake()->unique()->bothify('L#######'),
            'license_image' => null,
            'expiration_date' => fake()->dateTimeBetween('now', '+5 years'),
        ];
    }
}
