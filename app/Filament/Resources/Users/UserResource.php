<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Hash;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermissionTo('manage users');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermissionTo('create users');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasPermissionTo('edit users');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasPermissionTo('delete users');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasPermissionTo('delete users');
    }

    public static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getModelLabel(): string
    {
        return __('User');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('name')
                    ->label(__('Name'))
                    ->markAsRequired()
                    ->rules(['required', 'string', 'max:255'])
                    ->columnSpanFull(),
                TextInput::make('email')
                    ->label(__('Email'))
                    ->email()
                    ->markAsRequired()
                    ->rules(['required', 'email', 'max:255'])
                    ->unique(User::class, 'email', ignoreRecord: true)
                    ->columnSpanFull(),
                TextInput::make('password')
                    ->label(__('Password'))
                    ->password()
                    ->markAsRequired()
                    ->rules(['required', 'string', 'min:8'])
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->hiddenOn('edit')
                    ->columnSpanFull(),
                Select::make('role')
                    ->label(__('Role'))
                    ->options(\Spatie\Permission\Models\Role::where('name', '!=', 'admin')->pluck('name', 'name'))
                    ->afterStateHydrated(function (Select $component, $state, $record) {
                        if ($record && $record->roles->isNotEmpty()) {
                            $component->state($record->roles->first()->name);
                        }
                    })
                    ->dehydrated(false)
                    ->afterStateUpdated(function ($state, $record) {
                        if ($record && $state) {
                            $record->syncRoles([$state]);
                        }
                    })
                    ->preload()
                    ->searchable()
                    ->visible(fn () => auth()->user()->hasRole('admin'))
                    ->columnSpanFull(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextEntry::make('name')
                    ->label(__('Name')),
                TextEntry::make('email')
                    ->label(__('Email Address')),
                TextEntry::make('email_verified_at')
                    ->label(__('Email Verified At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->label(__('Created At'))
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->label(__('Updated At'))
                    ->dateTime()
                    ->placeholder('-')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(User::where('created_by', auth()->id())
                ->whereDoesntHave('roles', function ($query) {
                    $query->where('name', 'admin');
                }))
            ->columns([
                TextColumn::make('name')
                    ->label(__('Name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('Email'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->label(__('Roles')),
            ])
            ->filters([
                //
            ])
            ->paginationPageOptions([10])
            ->defaultPaginationPageOption(10)
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('No users yet'))
            ->emptyStateDescription(__('Create your first user to get started.'))
            ->recordActions([
                ViewAction::make()
                    ->modalWidth('md')
                    ->visible(fn () => auth()->user()->hasPermissionTo('view users')),
                EditAction::make()
                    ->successNotificationTitle(__('User updated successfully!'))
                    ->modalWidth('md')
                    ->after(function ($record) {
                        auth()->user()->notify(
                            Notification::make()
                                ->title(__('User Updated Successfully'))
                                ->body(__('User') . ' "' . $record->name . '" ' . __('has been updated successfully.'))
                                ->success()
                                ->toDatabase()
                        );
                    })
                    ->visible(fn () => auth()->user()->hasPermissionTo('edit users')),
                DeleteAction::make()
                    ->successNotificationTitle(__('User deleted successfully'))
                    ->after(function ($record) {
                        auth()->user()->notify(
                            Notification::make()
                                ->title(__('User Deleted Successfully'))
                                ->body(__('User') . ' "' . $record->name . '" ' . __('has been deleted successfully.'))
                                ->success()
                                ->toDatabase()
                        );
                    })
                    ->visible(fn () => auth()->user()->hasPermissionTo('delete users')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()->hasPermissionTo('delete users')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageUsers::route('/'),
        ];
    }
}
