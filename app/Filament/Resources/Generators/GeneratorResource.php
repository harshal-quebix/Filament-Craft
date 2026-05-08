<?php

namespace App\Filament\Resources\Generators;

use App\Filament\Resources\Generators\Pages\CreateGenerator;
use App\Filament\Resources\Generators\Pages\EditGenerator;
use App\Filament\Resources\Generators\Pages\ListGenerators;
use App\Filament\Resources\Generators\Schemas\GeneratorForm;
use App\Filament\Resources\Generators\Tables\GeneratorsTable;
use App\Models\Generator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

use Filament\Tables\Table;

class GeneratorResource extends Resource
{
    protected static ?string $model = Generator::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-code-bracket';

    protected static ?string $navigationLabel = null;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 99;

    public static function canViewAny(): bool
    {
        return auth()->user()->hasPermissionTo('manage crud generator');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->hasPermissionTo('create crud generator');
    }

    public static function canEdit($record): bool
    {
        return auth()->user()->hasPermissionTo('edit crud generator');
    }

    public static function canDelete($record): bool
    {
        return auth()->user()->hasPermissionTo('delete crud generator');
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()->hasPermissionTo('delete crud generator');
    }

        public static function getNavigationLabel(): string
    {
        return __('CRUD Generator');
    }

    public static function getModelLabel(): string
    {
        return __('CRUD Generator');
    }

    public static function getPluralModelLabel(): string
    {
        return __('CRUD Generator');
    }

    public static function form(Schema $schema): Schema
    {
        return GeneratorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GeneratorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGenerators::route('/'),
            'create' => CreateGenerator::route('/create'),
            'edit' => EditGenerator::route('/{record}/edit'),
        ];
    }
}
