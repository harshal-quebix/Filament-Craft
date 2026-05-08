<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Str;

class ResourceGenerator implements GeneratorInterface
{
    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        $modelName = $config['model_name'];
        $pluralName = $config['plural_name'];
        $modelWords = $config['model_words'];
        $navigationSort = $config['navigation_sort'];
        $navigationIcon = $config['navigation_icon'];
        $softDeletes = $config['soft_deletes'] ?? false;

        $permissionMethods = $this->buildPermissionMethods($modelWords, $modelName, $pluralName);
        $softDeleteQuery = $softDeletes ? $this->buildSoftDeleteQuery() : '';

        return $this->stubRenderer->load('resource.stub')->replace([
            'pluralName' => $pluralName,
            'modelName' => $modelName,
            'softDeleteQuery' => $softDeleteQuery,
            'navigationSort' => $navigationSort,
            'permissionMethods' => $permissionMethods,
            'navigationIcon' => $navigationIcon,
        ]);
    }

    private function buildPermissionMethods(string $modelWords, string $modelName, string $pluralName): string
    {
        return <<<PHP
    public static function canViewAny(): bool
    {
        return auth()->user()->can('manage {$modelWords}');
    }

    public static function canCreate(): bool
    {
        return auth()->user()->can('create {$modelWords}');
    }

    public static function canEdit(\$record): bool
    {
        return auth()->user()->can('edit {$modelWords}');
    }

    public static function canDelete(\$record): bool
    {
        return auth()->user()->can('delete {$modelWords}');
    }

    public static function getNavigationLabel(): string
    {
        return __('{$pluralName}');
    }
    
    public static function getModelLabel(): string
    {
        return __('{$modelName}');
    }
    
    public static function getPluralModelLabel(): string
    {
        return __('{$pluralName}');
    }
PHP;
    }

    private function buildSoftDeleteQuery(): string
    {
        return <<<'PHP'


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
PHP;
    }
}
