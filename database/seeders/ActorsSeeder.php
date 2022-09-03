<?php

namespace Database\Seeders;

use App\Models\Actor;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ActorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $actors = Actor::factory()->count(30)->create();

        fake()->addProvider(new \Smknstd\FakerPicsumImages\FakerPicsumImagesProvider(fake()));
        $imageUrl = fake()->imageUrl(200,200);
        foreach ($actors as $actor){
            $actor->addMediaFromUrl($imageUrl)->toMediaCollection('avatar');
        };
    }
}
