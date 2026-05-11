<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class EditCourse extends EditRecord
{
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return __('Course updated successfully!');
    }

    protected function afterSave(): void
    {
        auth()->user()->notify(
            Notification::make()
                ->title(__('Course Updated Successfully'))
                ->body(__('Course') . ' "' . ($this->record->title ?? $this->record->name ?? $this->record->id) . '" ' . __('has been updated successfully.'))
                ->success()
                ->toDatabase()
        );
    }

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        foreach ($schema->getComponents(withHidden: true) as $component) {
            if ($component instanceof \Filament\Schemas\Components\Section) {
                $component->footerActions([
                    $this->getSaveFormAction(),
                    $this->getCancelFormAction(),
                ]);
                break;
            }
        }

        return $schema;
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected static string $resource = CourseResource::class;
}
