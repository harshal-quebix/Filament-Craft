<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use App\Filament\Clusters\Settings\Pages\LandingPageSettings;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions\Action;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    /**
     * Override to redirect to Landing Page Settings after creation
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
            Action::make('create')
                ->label(__('Save Changes'))
                ->submit('create')
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
}
