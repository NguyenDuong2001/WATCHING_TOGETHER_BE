<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@gmail.com',
            'role_id' => 1,
            'country_id' => 1
        ]);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'role_id' => 2,
            'country_id' => 1
        ]);

        User::factory()->create([
            'name' => 'Checker',
            'email' => 'checker@gmail.com',
            'role_id' => 3,
            'country_id' => 1
        ]);

        $users = User::factory()->count(10)->create();

        fake()->addProvider(new \Smknstd\FakerPicsumImages\FakerPicsumImagesProvider(fake()));
        $imageUrl = fake()->imageUrl(200,200);
        foreach ($users as $user){
            $user->addMediaFromUrl($imageUrl)->toMediaCollection('avatar');
        };
    }
}
