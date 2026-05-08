<?php

namespace App\Filament\Widgets;

use App\Models\Generator;
use Filament\Actions\ViewAction;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use App\Helpers\Helper;

class RecentGeneratorsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 6;

    public function getHeading(): string
    {
        return __('Recent Generators');
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('Recent Generators'))
            ->query(Generator::query()->latest()->limit(5))
            ->paginated(false)
            ->emptyStateHeading(__('No generators yet'))
            ->emptyStateDescription(__('Create your first CRUD generator to get started.'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('Generator Name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('model_name')
                    ->label(__('Model'))
                    ->badge(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime(Helper::getDateTimeFormat())
                    ->timezone(Helper::getTimezone())
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'inactive' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->actions([
                ViewAction::make()
                    ->url(fn (Generator $record): string => route('filament.admin.resources.generators.edit', $record)),
            ]);
    }
}
