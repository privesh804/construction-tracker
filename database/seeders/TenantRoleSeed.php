<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\{Role,Permission, Tenant};

class TenantRoleSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenants = Tenant::all();
    
        foreach ($tenants as $tenant) {

            tenancy()->initialize($tenant);

            Role::firstOrCreate(['name' => 'contractors']);
            Role::firstOrCreate(['name' => 'clients']);
            Role::firstOrCreate(['name' => 'internal team']);
            Role::firstOrCreate(['name' => 'manager']);
            $role = Role::firstOrCreate(['name' => 'admin']);
    
            $permissions = [
                'team' => [
                    'list',
                    'create',
                    'update',
                    'delete'
                ],
                'role' => [
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
        }

    }
}
