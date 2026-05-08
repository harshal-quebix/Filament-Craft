<?php

namespace App\Filament\Resources\Generators\Tables;

use App\Filament\Resources\Generators\GeneratorResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Notifications\Notification;
use App\Services\CrudGeneratorService;
use App\Helpers\Helper;

class GeneratorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Generator Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('model_name')
                    ->label(__('Model Name'))
                    ->searchable()
                    ->sortable(),
                BadgeColumn::make('status')
                    ->label(__('Status'))
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'generated',
                        'danger' => 'failed',
                    ]),
                TextColumn::make('created_at')
                    ->label(__('Created'))
                    ->dateTime(Helper::getDateTimeFormat())
                    ->timezone(Helper::getTimezone())
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->paginationPageOptions([10])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('No generators yet'))
            ->emptyStateDescription(__('Create your first CRUD generator to get started.'))
            ->recordActions([
                EditAction::make()->successNotificationTitle(__('CRUD updated successfully!')),
                Action::make('generate')
                    ->label(__('Generate'))
                    ->icon('heroicon-o-cog')
                    ->color('success')
                    ->successNotification(null)
                    ->action(function ($record) {
                        try {
                            $generator = new CrudGeneratorService();
                            $generator->generate($record);
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title(__('Generation Failed!'))
                                ->body($e->getMessage())
                                ->send();
                        }
                    })
                    ->visible(fn ($record) => $record->status === 'pending'),

                DeleteAction::make()
                    ->label(__('Delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->successRedirectUrl(GeneratorResource::getUrl('index'))
                    ->modalHeading(__('Delete CRUD Generator'))
                    ->modalDescription(__('This will delete all generated files, database table, permissions, and localization keys. This action cannot be undone.'))
                    ->successNotificationTitle(null)
                    ->action(function ($record) {
                        try {
                            $generator = new CrudGeneratorService();
                            $generator->cleanup($record);
                            $record->delete();
                            Notification::make()
                                ->success()
                                ->title(__('Crud deleted successfully'))
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title(__('Deletion Failed!'))
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
