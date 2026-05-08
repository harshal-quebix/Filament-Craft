<?php

namespace App\Filament\Resources\Permissions;

use App\Filament\Resources\Permissions\Pages\ManagePermissions;
use App\Models\Permission;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class PermissionResource extends Resource
{
    protected static ?string $model = Permission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-key';

    protected static ?int $navigationSort = 3;

     protected static ?string $recordTitleAttribute = 'name';

    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('admin');
    }

    public static function getNavigationLabel(): string
    {
        return __('Permissions');
    }

    public static function getModelLabel(): string
    {
        return __('Permissions');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Permissions');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->markAsRequired()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->rules(['required', 'string', 'max:255'])
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->paginationPageOptions([10])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('No permissions yet'))
            ->emptyStateDescription(__('Create your first permission to get started.'))
            ->recordActions([
                EditAction::make()
                    ->modalWidth('md')
                    ->successNotificationTitle(__('Permission updated successfully!'))
                    ->after(function ($record) {
                        auth()->user()->notify(
                            Notification::make()
                                ->title(__('Permission Updated Successfully'))
                                ->body(__('Permission') . ' "' . $record->name . '" ' . __('has been updated successfully.'))
                                ->success()
                                ->toDatabase()
                        );
                    }),
                DeleteAction::make()
                    ->successNotificationTitle(__('Permission deleted successfully'))
                    ->after(function ($record) {
                        auth()->user()->notify(
                            Notification::make()
                                ->title(__('Permission Deleted Successfully'))
                                ->body(__('Permission') . ' "' . $record->name . '" ' . __('has been deleted successfully.'))
                                ->success()
                                ->toDatabase()
                        );
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePermissions::route('/'),
        ];
    }
}
