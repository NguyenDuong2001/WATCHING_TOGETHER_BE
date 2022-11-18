<?php

namespace Database\Seeders;

use App\Enums\RoleType;
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
                'name' => RoleType::SuperAdmin,
                'description' => 'This person may have all rights in the website'
            ],
            [
                'name' => RoleType::Admin,
                'description' => 'This person can post the movie on the website'
            ],
            [
                'name' => RoleType::Customer,
                'description' => 'This person is a customer using and experiencing the website'
            ]
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
