<?php

namespace App\Filament\Resources\Concerns;

use Filament\Actions\CreateAction;

trait HasTrashToggleAction
{
    protected function getHeaderActions(): array
    {
        $actions = [];

        // Add Create action if user has permission
        $resourceClass = static::getResource();
        if ($resourceClass::canCreate()) {
            $actions[] = CreateAction::make()
                ->label(false)
                ->tooltip(__('Create ' . $resourceClass::getModelLabel()))
                ->icon('heroicon-o-plus');
        }

        return $actions;
    }
}
