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

        $roles = ['operator', 'internal-team', 'admin'];

        foreach ($roles as $key => $role) {
            $role = \App\Models\Role::updateOrCreate([
                'name' => $role,
                'guard_name' => 'sanctum',
            ]);
        }

        $permissions = [
            'tenant' => [
                'invite',
                'list',
                'create',
                'update',
                'delete'
            ],
            'team' => [
                'list',
                'create',
                'update',
                'delete'
            ]
        ];


        foreach ($permissions as $key => $permission) {
            foreach ($permission as $value) {
                \App\Models\Permission::firstOrCreate([
                    'name' => $key."-".$value,
                    'guard_name' => 'sanctum'
                ]);
                $role->givePermissionTo($key."-".$value);
            }
        }

        $user = \App\Models\User::updateOrCreate([
            'email' => 'admin@cpt.com',
        ],[
            'name' => "Admin",
            'password' => '123456',
        ]);

        $user->guard_name = 'sanctum';
        $user->syncRoles(['admin']);


    }
}
