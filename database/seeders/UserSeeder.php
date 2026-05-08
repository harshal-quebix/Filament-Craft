<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create permissions
        $manageUsers = Permission::firstOrCreate(['name' => 'manage users']);
        $createUsers = Permission::firstOrCreate(['name' => 'create users']);
        $editUsers = Permission::firstOrCreate(['name' => 'edit users']);
        $deleteUsers = Permission::firstOrCreate(['name' => 'delete users']);
        $viewUsers = Permission::firstOrCreate(['name' => 'view users']);

        // CRUD Generator permissions
        $manageCrudGenerator = Permission::firstOrCreate(['name' => 'manage crud generator']);
        $createCrudGenerator = Permission::firstOrCreate(['name' => 'create crud generator']);
        $editCrudGenerator = Permission::firstOrCreate(['name' => 'edit crud generator']);
        $deleteCrudGenerator = Permission::firstOrCreate(['name' => 'delete crud generator']);
        $viewCrudGenerator = Permission::firstOrCreate(['name' => 'view crud generator']);

        // Create admin role and user first
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['created_by' => 0]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('admin@1232'),
                'email_verified_at' => now(),
            ]
        );
        $admin->assignRole($adminRole);

        // Create user role with admin as creator
        $userRole = Role::firstOrCreate(['name' => 'user'], ['created_by' => $admin->id]);

        // Assign all permissions to admin role
        $adminRole->syncPermissions([
            $manageUsers,
            $createUsers,
            $editUsers,
            $deleteUsers,
            $viewUsers,
            $manageCrudGenerator,
            $createCrudGenerator,
            $editCrudGenerator,
            $deleteCrudGenerator,
            $viewCrudGenerator
        ]);

        // Assign basic permissions to user role
        $userRole->syncPermissions([
            $manageCrudGenerator,
            $createCrudGenerator,
            $editCrudGenerator,
            $deleteCrudGenerator,
            $viewCrudGenerator
        ]);

        // Create regular user
        $user = User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'User',
                'password' => Hash::make('admin@1232'),
                'email_verified_at' => now(),
                'created_by' => $admin->id,
            ]
        );
        $user->assignRole($userRole);

        // Seed default settings for admin
        $this->seedDefaultSettings($admin->id);
    }

    private function seedDefaultSettings(int $adminId): void
    {
        // Plain text settings — use DB insert to bypass JSON cast (Setting model casts value as json)
        $textSettings = [
            'site_title'       => 'Craft Laravel',
            'footer_text'      => '© ' . date('Y') . ' Craft Laravel. All rights reserved.',
            'default_timezone' => 'UTC',
            'date_format'      => 'Y-m-d',
            'time_format'      => 'H:i',
            'theme_color'      => 'indigo',
            'font_family'      => 'Inter',
            'email_verification' => false,
            'user_registration' => true,
            'two_factor_required' => false,
        ];

        foreach ($textSettings as $key => $value) {
            $exists = Setting::where('key', $key)->where('created_by', $adminId)->exists();
            if (!$exists) {
                Setting::create([
                    'key'        => $key,
                    'value'      => $value,
                    'created_by' => $adminId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

    }
}
