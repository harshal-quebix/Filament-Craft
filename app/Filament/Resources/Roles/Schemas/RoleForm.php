<?php

namespace App\Filament\Resources\Roles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Permission;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        // Get all unique modules from permissions
        $permissions = Permission::all();
        $modules = [];

        foreach ($permissions as $permission) {
            $parts = explode(' ', $permission->name);
            if (count($parts) >= 2) {
                $module = $parts[1];
                if (count($parts) > 2) {
                    $module = $parts[1] . ' ' . $parts[2];
                }
                if (!isset($modules[$module])) {
                    $modules[$module] = [];
                }
                $modules[$module][] = $permission;
            }
        }

        $permissionSections = [];

        // Create sections for each module
        foreach ($modules as $moduleName => $modulePermissions) {
            $fieldName = str_replace(' ', '_', $moduleName) . '_permissions';

            $permissionSections[] = Section::make(__('Permissions'))
                ->description(__('Manage permissions'))
                ->icon('heroicon-o-shield-check')
                ->collapsible()
                ->schema([
                    CheckboxList::make($fieldName)
                        ->hiddenLabel()
                        ->options(function () use ($modulePermissions) {
                            $options = [];
                            foreach ($modulePermissions as $permission) {
                                $options[$permission->id] = ucwords(str_replace('_', ' ', $permission->name));
                            }
                            return $options;
                        })
                        ->columns([
                            'sm' => 1,
                            'md' => 2,
                            'lg' => 3,
                            'xl' => 4,
                        ])
                        ->gridDirection('row')
                        ->columnSpanFull()
                        ->afterStateHydrated(function ($component, $state, $record) use ($modulePermissions) {
                            if ($record && $record->permissions) {
                                $selectedIds = [];
                                foreach ($modulePermissions as $permission) {
                                    if ($record->permissions->contains($permission->id)) {
                                        $selectedIds[] = $permission->id;
                                    }
                                }
                                $component->state($selectedIds);
                            }
                        })
                        ->afterStateUpdated(function ($state, $set, $get) use ($modules) {
                            $allPermissions = [];
                            foreach ($modules as $module => $perms) {
                                $fieldName = str_replace(' ', '_', $module) . '_permissions';
                                $modulePerms = $get($fieldName) ?? [];
                                $allPermissions = array_merge($allPermissions, $modulePerms);
                            }
                            $set('permissions', $allPermissions);
                        })
                ])
                ->columnSpanFull();
        }

        return $schema->components([
            Section::make(__('Role Information'))
                ->description(__('Basic role details and identification'))
                ->icon('heroicon-o-identification')
                ->schema([
                    TextInput::make('name')
                        ->label(__('Role Name'))
                        ->markAsRequired()
                        ->rules(['required', 'string', 'max:255'])
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->placeholder(__('Enter role name'))
                        ->helperText(__('Choose a descriptive name for this role'))
                        ->columnSpan(4),
                ])
                ->columns(12)
                ->columnSpanFull(),

            ...$permissionSections,

            // Hidden field to store all selected permissions
            CheckboxList::make('permissions')
                ->relationship('permissions', 'name')
                ->dehydrated(false)
                ->hidden(),
        ]);
    }
}
