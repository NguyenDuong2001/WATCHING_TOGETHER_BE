<?php

namespace Database\Factories;

use App\Enums\MovieStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->words(5, true),
            'year' => fake()->numberBetween(2010, 2022),
            'movie_duration' => fake()->numberBetween(30, 150),
            'publication_time' => fake()->date(),
            'view' => fake()->numberBetween(900, 2000),
            'description' => fake()->text(150),
            'company' => fake()->words(3, true),
            'status' => MovieStatus::Published,
            'country_id' => fake()->numberBetween(1, 10),
            'director_id' => fake()->numberBetween(1, 10),
        ];
    }
}
