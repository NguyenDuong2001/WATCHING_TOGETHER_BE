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
        $directors = Director::factory()->count(20)->create();

        // foreach ($directors as $director){
        //     $director->addMediaFromUrl("https://source.unsplash.com/random/500x500")->toMediaCollection('images');
        // };
    }
}
