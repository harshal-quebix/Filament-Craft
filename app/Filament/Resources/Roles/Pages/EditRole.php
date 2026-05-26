<?php

namespace App\Filament\Resources\Roles\Pages;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    public function getTitle(): string
    {
        return __('Edit Role');
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save')
                ->keyBindings(['mod+s']),
            Action::make('cancel')
                ->label(__('Cancel'))
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // DeleteAction::make(),
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    
    protected function getSavedNotificationTitle(): ?string
    {
        return __('Role updated successfully!');
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function afterSave(): void
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
                ->title(__('Role Updated Successfully'))
                ->body(__('Role') . ' "' . $this->record->name . '" ' . __('has been updated successfully.'))
                ->success()
                ->toDatabase()
        );
    }
}
