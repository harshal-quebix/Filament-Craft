<?php

namespace App\Filament\Resources\Generators\Pages;

use App\Filament\Resources\Generators\GeneratorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use App\Services\CrudGeneratorService;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class EditGenerator extends EditRecord
{
    protected static string $resource = GeneratorResource::class;

    public $previousFields        = [];
    public $previousRelationships = [];
    public $mountWasCalled = false;

    public function mount(int | string $record): void
    {
        $this->mountWasCalled = true;
        parent::mount($record);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure repeater fields have string keys for Filament compatibility
        if (isset($data['fields']) && is_array($data['fields'])) {
            $data['fields'] = $this->keyRepeaterItems($data['fields']);
        }
        if (isset($data['query_conditions']) && is_array($data['query_conditions'])) {
            $data['query_conditions'] = $this->keyRepeaterItems($data['query_conditions']);
        }
        if (isset($data['table_columns']) && is_array($data['table_columns'])) {
            $data['table_columns'] = $this->keyRepeaterItems($data['table_columns']);
        }

        return $data;
    }

    private function keyRepeaterItems(array $items): array
    {
        if (empty($items) || ! array_is_list($items)) {
            return $items;
        }

        $keyedItems = [];
        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }
            $keyedItems[(string) \Illuminate\Support\Str::uuid()] = $item;
        }
        return $keyedItems;
    }

    protected function fillForm(): void
    {
        parent::fillForm();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('Delete'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->successRedirectUrl(GeneratorResource::getUrl('index'))
                ->modalHeading(__('Delete CRUD Generator'))
                ->modalDescription(__('This will delete all generated files, database table, permissions, and localization keys. This action cannot be undone.'))
                ->successNotificationTitle(null)
                ->action(function ($record) {
                    try {
                        app(CrudGeneratorService::class)->cleanup($record);
                        $record->delete();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title(__('Deletion Failed!'))
                            ->body($e->getMessage())
                            ->send();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Preserve per-field configure settings that are not exposed in the repeater row itself
        if (isset($data['fields'])) {
            $existingFields  = $this->record->fields ?? [];
            $configKeys      = [
                'required', 'unique', 'searchable', 'in_form', 'in_table',
                'auto_generate', 'max_length', 'placeholder', 'note',
                'sortable',
            ];

            foreach ($data['fields'] as $index => $field) {
                $savedType = $field['field_type'] ?? 'field';

                if (isset($existingFields[$index]) && ($existingFields[$index]['field_type'] ?? 'field') === $savedType) {
                    foreach ($configKeys as $key) {
                        if (array_key_exists($key, $existingFields[$index])) {
                            $data['fields'][$index][$key] = $existingFields[$index][$key];
                        }
                    }
                }
            }
        }

        // Snapshot previous state for migration diffing
        $this->previousFields = $this->record->fields ?? [];

        $this->previousRelationships = array_values(array_filter(
            $this->record->fields ?? [],
            fn ($f) => ($f['field_type'] ?? 'field') === 'relationship'
        ));

        // Normalise rel_type → type for backwards compat in migration service
        $this->previousRelationships = array_map(function ($rel) {
            if (isset($rel['rel_type'])) {
                $rel['type'] = $rel['rel_type'];
            }
            if (isset($rel['rel_column_span'])) {
                $rel['column_span'] = $rel['rel_column_span'];
            }
            return $rel;
        }, $this->previousRelationships);

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

    protected function afterSave(): void
    {
        try {
            app(CrudGeneratorService::class)->generate(
                $this->record,
                $this->previousFields,
                $this->previousRelationships
            );

            auth()->user()->notify(
                Notification::make()
                    ->title(__('CRUD updated successfully!'))
                    ->body(__('CRUD Generator') . ' "' . $this->record->name . '" ' . __('has been updated successfully.'))
                    ->success()
                    ->toDatabase()
            );
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('Update Failed!'))
                ->body($e->getMessage())
                ->send();
        }
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('CRUD updated successfully!');
    }
}
