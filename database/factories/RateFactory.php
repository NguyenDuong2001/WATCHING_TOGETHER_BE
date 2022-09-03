<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Rate>
 */
class RateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'rate' => fake()->numberBetween(1, 5),
            'user_id' => fake()->numberBetween(1, 12),
            'movie_id' => fake()->numberBetween(1, 10),
        ];
    }
}
