<?php

namespace App\Services\CrudGenerator\Generators;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;

class PermissionGenerator
{
    private const ACTIONS = ['manage', 'create', 'edit', 'delete', 'view'];

    public function generate(string $modelWords): array
    {
        $permissions = array_map(
            fn ($action) => "{$action} {$modelWords}",
            self::ACTIONS
        );

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        return $permissions;
    }

    public function assignToCurrentUser(array $permissions): void
    {
        try {
            $user = auth()->user();
            if (! $user) {
                return;
            }

            $userRoles = $user->roles;
            if ($userRoles->isNotEmpty()) {
                foreach ($userRoles as $role) {
                    $role->givePermissionTo($permissions);
                }
            } else {
                $user->givePermissionTo($permissions);
            }

            $adminRole = Role::where('name', 'admin')->first();
            if ($adminRole) {
                $adminRole->givePermissionTo($permissions);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to assign permissions during CRUD generation: ' . $e->getMessage());
        }
    }

    public function delete(string $modelWords): void
    {
        foreach (self::ACTIONS as $action) {
            Permission::where('name', "{$action} {$modelWords}")->delete();
        }
    }
}
