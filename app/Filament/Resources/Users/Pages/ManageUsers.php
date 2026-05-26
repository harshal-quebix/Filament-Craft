<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->successNotificationTitle(__('User created successfully!'))
                ->modalWidth('md')
                ->createAnother(false)
                ->mutateFormDataUsing(function (array $data): array {
                    $data['created_by'] = auth()->id();
                    return $data;
                })
                ->after(function ($record) {
                    // Assign default user role if no role is set (for non-admin users)
                    if (!auth()->user()->isAdmin() || !$record->roles->count()) {
                        $record->assignRole('user');
                    }

                    auth()->user()->notify(
                        Notification::make()
                            ->title(__('User created successfully!'))
                            ->body(__('User') . ' "' . $record->name . '" ' . __('has been created successfully.'))
                            ->success()
                            ->toDatabase()
                    );
                })
                ->visible(fn () => auth()->user()->hasPermissionTo('create users')),
        ];
    }
}
