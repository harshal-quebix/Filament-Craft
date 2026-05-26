<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;

class ManagePermissions extends ManageRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-m-plus')
                ->modalWidth('md')
                ->createAnother(false)
                ->successNotificationTitle(__('Permission created successfully!'))
                ->after(function ($record) {
                    auth()->user()->notify(
                        Notification::make()
                            ->title(__('Permission Created Successfully'))
                            ->body(__('Permission') . ' "' . $record->name . '" ' . __('has been created successfully.'))
                            ->success()
                            ->toDatabase()
                    );
                }),
        ];
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('Permission created successfully!');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('Permission updated successfully!');
    }
}
