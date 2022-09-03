<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'description' => 'This person may have all rights in the website'
            ],
            [
                'name' => 'Admin',
                'description' => 'This person can post the movie on the website'
            ],
            [
                'name' => 'Checker',
                'description' => 'This person can browse movies so customers can watch'
            ],
            [
                'name' => 'Customer',
                'description' => 'This person is a customer using and experiencing the website'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
