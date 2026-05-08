<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use App\Filament\Clusters\Settings\Pages\LandingPageSettings;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    /**
     * Override to redirect to Landing Page Settings after save
     */
    protected function getRedirectUrl(): string
    {
        return LandingPageSettings::getUrl() . '?tab=menu-management';
    }

    /**
     * Override form actions to show only Save and Cancel
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->submit('save')
                ->keyBindings(['mod+s']),
            Action::make('cancel')
                ->label(__('Cancel'))
                ->url($this->getCancelUrl())
                ->color('gray'),
        ];
    }

    /**
     * Get the cancel URL - redirect to Landing Page Settings
     */
    protected function getCancelUrl(): string
    {
        return LandingPageSettings::getUrl() . '?tab=menu-management';
    }

    /**
     * Header actions (Delete button)
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->after(function () {
                    // Redirect to Landing Page Settings after delete
                    return redirect()->to(LandingPageSettings::getUrl() . '?tab=menu-management');
                }),
        ];
    }
}
