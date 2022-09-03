<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Comment;
use App\Models\Rate;
use App\Models\Reply;
use Illuminate\Database\Seeder;

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

        Rate::factory()->count(23)->create();
        Comment::factory()->count(30)->create();
        Reply::factory()->count(15)->create();
    }
}
