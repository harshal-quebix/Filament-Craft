<?php

namespace App\Filament\Clusters\Profile;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class ProfileCluster extends Cluster
{
    protected static ?string $navigationLabel = null;
    protected static ?string $title = null;
    protected static bool $shouldRegisterSubNavigation = false;

    public static function getNavigationLabel(): string
    {
        return __('Profile');
    }

    public static function getClusterBreadcrumb(): string
    {
        return __('Profile');
    }

    public  function getTitle(): string
    {
        return __('Profile');
    }
    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
