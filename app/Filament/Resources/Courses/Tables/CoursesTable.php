<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Support\Enums\Size;
use Filament\Tables\Table;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use App\Helpers\Helper;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label(__('ID'))->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')->label(__('Title'))->toggleable()->searchable()->sortable()
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->paginationPageOptions([10])
            ->defaultPaginationPageOption(10)
            ->recordActions([
                RestoreAction::make()
                    ->label(false)
                    ->tooltip(__('Restore'))
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->button()
                    ->size(Size::Small)
                    ->color('success')
                    ->visible(fn ($record) => method_exists($record, 'trashed') && $record->trashed()),
                ForceDeleteAction::make()
                    ->label(false)
                    ->tooltip(__('Force Delete'))
                    ->icon('heroicon-o-trash')
                    ->button()
                    ->size(Size::Small)
                    ->color('danger')
                    ->visible(fn ($record) => method_exists($record, 'trashed') && $record->trashed()),
                ViewAction::make()
                    ->label(false)
                    ->tooltip(__('View Course'))
                    ->icon('heroicon-o-eye')
                    ->button()
                    ->size(Size::Small)
                    ->color('primary')
                    ->visible(fn () => auth()->user()->can('view courses')),
                EditAction::make()
                    ->label(false)
                    ->tooltip(__('Edit Course'))
                    ->icon('heroicon-o-pencil-square')
                    ->button()
                    ->size(Size::Small)
                    ->color('warning')
                    ->visible(fn () => auth()->user()->can('edit courses')),
                DeleteAction::make()
                    ->label(false)
                    ->tooltip(__('Delete Course'))
                    ->icon('heroicon-o-trash')
                    ->button()
                    ->size(Size::Small)
                    ->color('danger')
                    ->successNotificationTitle(__('Course deleted successfully'))
                    ->after(function ($record) {
                        auth()->user()->notify(
                            Notification::make()
                                ->title(__('Course Deleted Successfully'))
                                ->body(__('Course') . ' "' . ($record->title ?? $record->name ?? $record->id) . '"' . __('has been deleted successfully.'))
                                ->success()
                                ->toDatabase()
                        );
                    })
                    ->extraModalFooterActions([
                        Action::make('forceDelete')
                            ->label(__('Force Delete'))
                            ->color('warning')
                            ->requiresConfirmation()
                            ->modalHeading(__('Force Delete'))
                            ->modalDescription(__('This action will permanently delete the record and cannot be undone.'))
                            ->action(function (\Illuminate\Database\Eloquent\Model $record): void {
                                $record->forceDelete();
                            }),
                    ])
                    ->visible(fn ($record) => (method_exists($record, 'trashed') ? !$record->trashed() : true) && auth()->user()->can('delete courses')),
            ])
            ->recordActionsColumnLabel(__('Actions'))
            ->recordActionsAlignment('end')
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make()
                ]),
            ])
            ->headerActions([]);
    }
}
