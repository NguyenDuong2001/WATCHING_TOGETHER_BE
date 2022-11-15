<?php

namespace Database\Seeders;

use App\Models\Room;
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
            'name' => 'Super Admin',
            'email' => 'superadmin1@gmail.com',
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
        $users->each(fn ($user) => Room::create(['user_id' => $user->id]));
        // foreach ($users as $user){
        //     $user->addMediaFromUrl("https://source.unsplash.com/random/180x180")->toMediaCollection('avatar');
        // };
    }
}
