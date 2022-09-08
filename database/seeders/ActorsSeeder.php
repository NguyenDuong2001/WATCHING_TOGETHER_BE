<?php

namespace Database\Seeders;

use App\Models\Actor;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

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

        // foreach ($actors as $actor){
        //     $actor->addMediaFromUrl("https://source.unsplash.com/random/500x500")->toMediaCollection('avatar');
        // };
    }
}
