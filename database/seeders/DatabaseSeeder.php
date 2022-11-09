<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Enums\ActivityType;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Movie;
use App\Models\Rate;
use App\Models\Reply;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CountriesSeeder::class,
            RolesSeeder::class,
            UsersSeeder::class,
            CategoriesSeeder::class,
            DirectorsSeeder::class,
            ActorsSeeder::class,
            MoviesSeeder::class,
        ]);

        for ($i = 0; $i < 100; $i++) {
            $user_id = fake()->numberBetween(1, 12);
            $movie_id = fake()->numberBetween(1, 10);
            $rate = fake()->numberBetween(1, 5);
             if (Rate::where('user_id', $user_id)->where('movie_id', $movie_id)->exists()) {
                 continue;
             }

             Rate::create([
                 'rate' => $rate,
                 'user_id' => $user_id,
                 'movie_id' => $movie_id,
             ]);

            Activity::create([
                'user_id' => $user_id,
                'object_id' => $movie_id,
                'object_type' => Movie::class,
                'description' => 'User #' . $user_id . ' rated ' . $rate . ' in movie #' . $movie_id,
                'content' => $rate,
                'type' => ActivityType::Rate,
            ]);
        }

        for ($i = 0; $i < 50; $i++) {
            $user_id = fake()->numberBetween(1, 12);
            $movie_id = fake()->numberBetween(1, 10);
            $content = fake()->sentence(10);

            Activity::create([
                'user_id' => $user_id,
                'object_id' => $movie_id,
                'object_type' => Movie::class,
                'description' => 'User #' . $user_id . ' commented as "' . $content . '" in movie #' . $movie_id,
                'content' => $content,
                'type' => ActivityType::Comment,
            ]);

            Comment::create([
                'user_id' => $user_id,
                'movie_id' => $movie_id,
                'content' => $content,
            ]);
        }

        for ($i = 0; $i < 30; $i++) {
            $user_id = fake()->numberBetween(1, 12);
            $comment_id = fake()->numberBetween(1, 20);
            $content = fake()->sentence(7);

            Activity::create([
                'user_id' => $user_id,
                'object_id' => $comment_id,
                'object_type' => Comment::class,
                'description' => 'User #' . $user_id . ' replied as "' . $content . '" in comment #' . $comment_id,
                'content' => $content,
                'type' => ActivityType::Reply,
            ]);

            Reply::create([
                'user_id' => $user_id,
                'comment_id' => $comment_id,
                'content' => $content,
            ]);
        }

//        Comment::factory()->count(30)->create();
//        Reply::factory()->count(15)->create();
    }
}
