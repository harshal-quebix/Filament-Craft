<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Resources\Courses\CourseResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;

class CreateCourse extends CreateRecord
{
    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return __('Course created successfully!');
    }

    protected function afterCreate(): void
    {
        auth()->user()->notify(
            Notification::make()
                ->title(__('Course Created Successfully'))
                ->body(__('Course') . ' "' . ($this->record->title ?? $this->record->name ?? $this->record->id) . '" ' . __('has been created successfully.'))
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
                    $this->getCreateFormAction(),
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
