<?php

namespace App\Filament\Resources\Generators\Pages;

use App\Filament\Resources\Generators\GeneratorResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGenerators extends ListRecords
{
    protected static string $resource = GeneratorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
