<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;
    
    protected static bool $canCreateAnother = false;
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getCreatedNotificationTitle(): ?string
    {
        return __('Role created successfully!');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Collect all permission IDs from module-specific fields
        $allPermissions = [];
        
        foreach ($data as $key => $value) {
            if (str_ends_with($key, '_permissions') && is_array($value)) {
                $allPermissions = array_merge($allPermissions, $value);
            }
        }
        
        // Remove module-specific fields from data
        $filteredData = [];
        foreach ($data as $key => $value) {
            if (!str_ends_with($key, '_permissions')) {
                $filteredData[$key] = $value;
            }
        }
        
        return $filteredData;
    }

    protected function afterCreate(): void
    {
        // Collect all permission IDs from form data
        $allPermissions = [];
        $formData = $this->form->getState();
        
        foreach ($formData as $key => $value) {
            if (str_ends_with($key, '_permissions') && is_array($value)) {
                $allPermissions = array_merge($allPermissions, $value);
            }
        }
        
        // Sync permissions with the role
        $this->record->permissions()->sync($allPermissions);
        
        auth()->user()->notify(
            Notification::make()
                ->title(__('Role Created Successfully'))
                ->body(__('Role') . ' "' . $this->record->name . '" ' . __('has been created successfully.'))
                ->success()
                ->toDatabase()
        );
    }
}
