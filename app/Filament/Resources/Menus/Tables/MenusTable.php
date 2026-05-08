<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use App\Helpers\Helper;
use Filament\Tables\Columns\IconColumn;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('page_name')->label(__('Page Name'))->toggleable()->searchable()->sortable(),
                TextColumn::make('page_type')->label(__('Page Type'))->toggleable()->searchable()->sortable(),
                TextColumn::make('url')->label(__('URL'))->toggleable()->searchable()->sortable(),
                TextColumn::make('placement')->label(__('Placement'))->toggleable()->searchable()->sortable(),
                TextColumn::make('sort_order')->label(__('Sort Order'))->toggleable()->searchable()->sortable(),
                IconColumn::make('is_active')->label(__('Is Active'))->boolean()->toggleable()->searchable()->sortable()
            ])
            ->paginationPageOptions([10])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->successNotificationTitle(__('Menu deleted successfully'))
                    ->after(function ($record) {
                        auth()->user()->notify(
                            Notification::make()
                                ->title(__('Menu Deleted Successfully'))
                                ->body(__('Menu') . ' "' . ($record->title ?? $record->name ?? $record->id) . '"' . __('has been deleted successfully.'))
                                ->success()
                                ->toDatabase()
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->headerActions([]);
    }
}
