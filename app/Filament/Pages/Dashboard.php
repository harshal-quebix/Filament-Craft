<?php

namespace App\Filament\Pages;

use Filament\Panel;
use Filament\Pages\Dashboard as BaseDashboard;
use App\Filament\Widgets\GeneratorStatsWidget;
use App\Filament\Widgets\RecentGeneratorsWidget;
use App\Filament\Widgets\RecentUsersWidget;

class Dashboard extends BaseDashboard
{
    protected static string $routePath = '/dashboard';

    public static function getRoutePath(Panel $panel): string
    {
        return static::$routePath;
    }

    public function getColumns(): int
    {
        return 12;
    }

    public function getWidgets(): array
    {
        return [
            GeneratorStatsWidget::class,
            RecentGeneratorsWidget::class,
            RecentUsersWidget::class,
        ];
    }
}
