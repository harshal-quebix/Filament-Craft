<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Assign all permissions to Super Admin (role id 1)
        $superAdminRole = Role::find(1);
        if ($superAdminRole) {
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions);
        }

        // Assign specific permissions to Admin (role id 2) 
        $adminRole = Role::find(2);
        if ($adminRole) {
            $adminPermissions = Permission::whereIn('id', [6, 7, 8, 9, 10])->get();
            $adminRole->syncPermissions($adminPermissions);
        }
    }
}
