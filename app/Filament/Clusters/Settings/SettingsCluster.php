<?php

namespace App\Filament\Clusters\Settings;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class SettingsCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static bool $shouldRegisterSubNavigation = false;


    public static function getNavigationLabel(): string
    {
        return __('Settings');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Settings');
    }

    public static function getModelLabel(): string
    {
        return __('Settings');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Settings');
    }

     protected static ?int $navigationSort = 110;

    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('admin');
    }
}
