<?php

namespace App\Filament\Resources\Generators\Pages;

use App\Filament\Resources\Generators\GeneratorResource;
use Filament\Resources\Pages\CreateRecord;
use App\Services\CrudGeneratorService;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
class CreateGenerator extends CreateRecord
{
    protected static string $resource = GeneratorResource::class;
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['fields']) || ! is_array($data['fields']) || count($data['fields']) === 0) {
            throw ValidationException::withMessages([
                'data.fields' => __('At least one field or relationship must be configured before saving.'),
            ]);
        }

        if (empty($data['table_columns'])) {
            throw ValidationException::withMessages([
                'data.table_columns' => __('Table Configuration is required before saving.'),
            ]);
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        try {
            app(CrudGeneratorService::class)->generate($this->record);

            auth()->user()->notify(
                Notification::make()
                    ->title(__('CRUD created successfully!'))
                    ->body(__('CRUD Generator') . ' "' . $this->record->name . '" ' . __('has been created successfully.'))
                    ->success()
                    ->toDatabase()
            );
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('Generation Failed!'))
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('CRUD created successfully!');
    }
}
