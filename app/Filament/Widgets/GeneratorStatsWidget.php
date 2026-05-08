<?php

namespace App\Filament\Widgets;

use App\Models\Generator;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GeneratorStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

        protected int | string | array $columnSpan = 12;

    protected function getStats(): array
    {
        return [
            Stat::make(__('Total Users'), User::whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'admin');
                })->where('created_by', auth()->id())->count())
                ->description(__('Registered users'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make(__('Total Crud'), Generator::count())
                ->description(__('All CRUD generators'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make(__('Active Generators'), Generator::where('status', 'generated')->count())
                ->description(__('Currently active'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make(__('Recent Generators'), Generator::where('created_at', '>=', now()->subDays(7))->count())
                ->description(__('Created this week'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('warning'),
        ];
    }
}
