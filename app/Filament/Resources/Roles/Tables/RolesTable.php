<?php

namespace App\Filament\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use App\Helpers\Helper;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('name')->label(__('Name'))->toggleable()->searchable()->sortable(),
                TextColumn::make('created_at')->label(__('Created At'))->dateTime(Helper::getDateTimeFormat())->timezone(Helper::getTimezone())->toggleable()->sortable()
            ])
            ->paginationPageOptions([10])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->successNotificationTitle(__('Role deleted successfully'))
                    ->after(function ($record) {
                        auth()->user()->notify(
                            Notification::make()
                                ->title(__('Role Deleted Successfully'))
                                ->body(__('Role') . ' "' . ($record->title ?? $record->name ?? $record->id) . '"' . __('has been deleted successfully.'))
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
