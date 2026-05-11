<?php

namespace App\Filament\Resources\Concerns;

trait HasPermissionAuthorization
{
    abstract protected static function getPermissionPrefix(): string;

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasPermissionTo('manage ' . static::getPermissionPrefix()) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->hasPermissionTo('create ' . static::getPermissionPrefix()) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->hasPermissionTo('edit ' . static::getPermissionPrefix()) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->hasPermissionTo('delete ' . static::getPermissionPrefix()) ?? false;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->hasPermissionTo('delete ' . static::getPermissionPrefix()) ?? false;
    }

    public static function canView($record): bool
    {
        return auth()->user()?->hasPermissionTo('view ' . static::getPermissionPrefix()) ?? false;
    }
}
