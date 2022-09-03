<?php

namespace Database\Seeders;

use App\Models\Director;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class DirectorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $directors = Director::factory()->count(10)->create();

        fake()->addProvider(new \Smknstd\FakerPicsumImages\FakerPicsumImagesProvider(fake()));
        $imageUrl = fake()->imageUrl(200,200);
        foreach ($directors as $director){
            $director->addMediaFromUrl($imageUrl)->toMediaCollection('images');
        };
    }
}
