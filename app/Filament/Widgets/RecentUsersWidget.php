<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Helpers\Helper;

class RecentUsersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getHeading(): string
    {
        return __('Recent Users');
    }

      public function getTitle(): string
    {
        return __('Recent Users');
    }

    public static function getNavigationLabel(): string
    {
        return __('Recent Users');
    }

    protected int | string | array $columnSpan = 6;

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Recent Users'))
            ->query(User::whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'admin');
                })->where('created_by', auth()->id())->latest()->limit(5))
            ->paginated(false)
            ->emptyStateHeading(__('No users yet'))
            ->emptyStateDescription(__('No users have been created yet.'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('User Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Joined'))
                    ->dateTime(Helper::getDateTimeFormat())
                    ->timezone(Helper::getTimezone())
                    ->sortable(),
                Tables\Columns\TextColumn::make('email_verified_at')
                    ->label(__('Verified'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? __('Verified') : __('Pending'))
                    ->color(fn ($state): string => $state ? 'success' : 'warning'),
            ]);
    }
}
