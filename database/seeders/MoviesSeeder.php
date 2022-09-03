<?php

namespace Database\Seeders;

use App\Models\Actor;
use App\Models\Movie;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MoviesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        fake()->addProvider(new \Smknstd\FakerPicsumImages\FakerPicsumImagesProvider(fake()));

        $categories = Category::all();
        $actors = Actor::all();

        $movie = Movie::factory()->count(1)->create([
            'name' => 'Red Notice',
            'year' => '2021',
            'movie_duration' => 118,
            'view' => 1230,
            'description' => 'Red Notice is a 2021 American action comedy film written, directed, and produced by Rawson Marshall Thurber. Dwayne Johnson, who also served as a producer, stars as an FBI agent who reluctantly teams up with a renowned art thief (Ryan Reynolds) in order to catch an even more notorious criminal (Gal Gadot). The film marks the third collaboration between Thurber and Johnson, following Central Intelligence (2016) and Skyscraper (2018).',
            'company' => 'Flynn Picture Company Seven Bucks Productions Bad Version, Inc.'
        ]);
        $movie[0]->actors()->attach(
            $actors->random(rand(2,5))->pluck('id')->toArray()
        );
        $movie[0]->categories()->attach(
            $categories->random(rand(1,3))->pluck('id')->toArray()
        );

        $movie[0]->addMediaFromUrl('https://www.elleman.vn/wp-content/uploads/2021/12/06/207758/review-phim-red-notice-elle-man-cover-1.jpeg')->toMediaCollection('poster');
        $movie[0]->addMediaFromUrl('https://staticg.sportskeeda.com/editor/2021/11/412e4-16367363983803-1920.jpg')->toMediaCollection('poster');
        $movie[0]->addMediaFromUrl('https://cdn06.pramborsfm.com/storage/app/media/Prambors/Editorial/red%20notice-20211115192124.jpg?tr=w-800')->toMediaCollection('thumbnail');

        $movies = Movie::factory()->count(40)->create();

        foreach ($movies as $movie){
            $movie->actors()->attach(
                $actors->random(rand(2,5))->pluck('id')->toArray()
            );
            $movie->categories()->attach(
                $categories->random(rand(1,3))->pluck('id')->toArray()
            );

            $movie->addMediaFromUrl(fake()->imageUrl(1200,700))->toMediaCollection('poster');
            $movie->addMediaFromUrl(fake()->imageUrl(1200,700))->toMediaCollection('poster');
            $movie->addMediaFromUrl(fake()->imageUrl(800,450))->toMediaCollection('thumbnail');
        }
    }
}
