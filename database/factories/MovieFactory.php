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
        $limit_age = array(3, 6, 12, 16, 18);
        $key = array_rand($limit_age);

        return [
            'name' => fake()->words(5, true),
            'year' => fake()->numberBetween(2010, 2022),
            'movie_duration' => fake()->numberBetween(30, 150),
            'publication_time' => fake()->date(),
            'view' => fake()->numberBetween(900, 2000),
            'description' => fake()->text(150),
            'company' => fake()->words(3, true),
            'limit_age' => $limit_age[$key],
            'status' => MovieStatus::Published,
            'country_id' => fake()->numberBetween(1, 30),
            'director_id' => fake()->numberBetween(1, 10),
        ];
    }
}
