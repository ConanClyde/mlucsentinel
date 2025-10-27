<?php

namespace Database\Factories;

use App\Models\StakeholderType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StakeholderFactory extends Factory
{
    protected $model = \App\Models\Stakeholder::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type_id' => StakeholderType::inRandomOrder()->first()?->id ?? 1,
            'license_no' => fake()->unique()->bothify('L#######'),
            'license_image' => null,
            'expiration_date' => fake()->dateTimeBetween('now', '+5 years'),
        ];
    }
}
