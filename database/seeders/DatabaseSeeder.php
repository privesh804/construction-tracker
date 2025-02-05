<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        $faker = Factory::create();

        $roles = ['admin', 'operator', 'internal-team'];

        foreach ($roles as $key => $role) {
            \App\Models\Role::create([
                'name' => $role,
                'guard_name' => 'sanctum',
            ]);
        }

        $user = \App\Models\User::factory()->create([
            'name' => "Admin",
            'email' => 'admin@cpt.com',
            'password' => '123456',
        ]);

        $user->guard_name = 'sanctum';
        $user->assignRole('admin');


    }
}
