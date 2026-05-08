<?php

namespace App\Services\CrudGenerator\Services;

use App\Models\Generator;
use App\Services\CrudGenerator\Builders\FieldBuilder;
use App\Services\CrudGenerator\Builders\RelationshipBuilder;
use App\Services\CrudGenerator\Generators\FormSchemaGenerator;
use App\Services\CrudGenerator\Generators\InfolistGenerator;
use App\Services\CrudGenerator\Generators\ResourcePageGenerator;
use App\Services\CrudGenerator\Generators\TableSchemaGenerator;
use App\Services\CrudGenerator\Resolvers\IconResolver;
use App\Services\CrudGenerator\Support\FieldResolver;
use App\Services\CrudGenerator\Support\StubRenderer;
use App\Services\CrudGenerator\FileSystem\FileManager;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ResourceCustomizer
{
    public function __construct(private FieldResolver $fieldResolver)
    {
    }

    public function customizeResource(string $modelName, array $fields, ?Generator $generator, string $pluralName): void
    {
        $resourcePath = app_path("Filament/Resources/{$pluralName}/{$modelName}Resource.php");

        if (File::exists($resourcePath)) {
            $content    = File::get($resourcePath);
            $modelWords = str_replace('_', ' ', Str::snake($pluralName));

            $content = $this->injectNavigationSort($content, $pluralName);
            $content = $this->injectPermissionMethods($content, $modelWords, $modelName, $pluralName);
            $content = $this->injectSoftDeleteQuery($content, $generator);
            $content = $this->injectNavigationIcon($content, $modelName);

            $this->writeFile($resourcePath, $content);
        }

        $this->writeFormSchema($modelName, $fields, $generator, $pluralName);
    }

    public function customizePages(string $modelName, ?Generator $generator): void
    {
        $pluralName      = Str::plural($modelName);
        $modelWords      = Str::snake($pluralName);
        $usesSoftDeletes = $generator->soft_deletes ?? false;
        $pageGenerator   = new ResourcePageGenerator(new StubRenderer());

        $pages = [
            'create' => app_path("Filament/Resources/{$pluralName}/Pages/Create{$modelName}.php"),
            'edit'   => app_path("Filament/Resources/{$pluralName}/Pages/Edit{$modelName}.php"),
            'list'   => app_path("Filament/Resources/{$pluralName}/Pages/List{$pluralName}.php"),
        ];

        foreach ($pages as $type => $path) {
            if (! File::exists($path)) continue;
            $content = $pageGenerator->generate([
                'page_type'    => $type,
                'model_name'   => $modelName,
                'plural_name'  => $pluralName,
                'model_words'  => $modelWords,
                'soft_deletes' => $usesSoftDeletes,
            ]);
            $this->writeFile($path, $content);
        }
    }

    public function customizeTableSchema(string $modelName, array $fields, Generator $generator): void
    {
        $pluralName      = Str::plural($modelName);
        $tableSchemaPath = app_path("Filament/Resources/{$pluralName}/Tables/{$pluralName}Table.php");
        if (! File::exists($tableSchemaPath)) return;

        $usesSoftDeletes    = $generator->soft_deletes ?? false;
        $customTableColumns = is_array($generator->table_columns ?? null) ? $generator->table_columns : [];
        $tableGenerator     = new TableSchemaGenerator(new StubRenderer());
        $tableColumns       = ["TextColumn::make('id')->label(__('ID'))->sortable()->toggleable(isToggledHiddenByDefault: true)"];

        if (! empty($customTableColumns)) {
            $tableColumns = array_merge($tableColumns, $this->buildCustomColumns($customTableColumns, $generator, $tableGenerator));
        } else {
            $tableColumns = array_merge($tableColumns, $this->buildAutoColumns($fields, $generator, $modelName, $tableGenerator));
            $tableColumns[] = "TextColumn::make('created_at')->label(__('Created At'))->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true)";
        }

        $content = $tableGenerator->generate([
            'plural_name'          => $pluralName,
            'model_name'           => $modelName,
            'model_words'          => str_replace('_', ' ', Str::snake($pluralName)),
            'columns'              => $tableColumns,
            'fields'               => $fields,
            'custom_table_columns' => $customTableColumns,
            'soft_deletes'         => $usesSoftDeletes,
        ]);

        $this->writeFile($tableSchemaPath, $content);
    }

    public function regenerateForm(string $modelName, array $fields, Generator $generator, string $pluralName): void
    {
        $formSchemaPath = app_path("Filament/Resources/{$pluralName}/Schemas/{$modelName}Form.php");
        if (! File::exists($formSchemaPath)) return;

        $fieldBuilder = new FieldBuilder();
        $formFields   = collect($fields)
            ->filter(fn ($f) => ($f['in_form'] ?? true) === true)
            ->map(fn ($f) => $fieldBuilder->buildFormField($f, $modelName))
            ->filter()->values()->toArray();

        if (empty($formFields)) return;

        $formGenerator = new FormSchemaGenerator(new StubRenderer());
        $cardSize      = $generator->default_card_size ?? 3;

        $content = $formGenerator->generate([
            'class_name'  => $modelName,
            'plural_name' => $pluralName,
            'fields'      => $formFields,
            'imports'     => $formGenerator->buildImports($fields, ! empty($generator->relationships)),
            'card_size'   => $cardSize,
            'offset_size' => ceil((12 - $cardSize) / 2) + 1,
        ]);

        File::put($formSchemaPath, $content);
        $this->writeInfolistSchema($modelName, $fields, $generator, $pluralName);
    }

    private function injectNavigationSort(string $content, string $pluralName): string
    {
        if (str_contains($content, 'navigationSort')) return $content;

        $generatorSort = 99;
        $path = app_path('Filament/Resources/Generators/GeneratorResource.php');
        if (File::exists($path) && preg_match('/protected static \?int \$navigationSort = (\d+);/', File::get($path), $m)) {
            $generatorSort = (int) $m[1];
        }

        $sort = "\n    protected static ?int \$navigationSort = " . ($generatorSort + 1) . ";";
        return str_replace('protected static ?string $model', $sort . "\n\n    protected static ?string \$model", $content);
    }

    private function injectPermissionMethods(string $content, string $modelWords, string $modelName, string $pluralName): string
    {
        if (str_contains($content, 'public static function canViewAny()')) return $content;

        $methods = "\n    public static function canViewAny(): bool\n    {\n        return auth()->user()->can('manage {$modelWords}');\n    }\n\n    public static function canCreate(): bool\n    {\n        return auth()->user()->can('create {$modelWords}');\n    }\n\n    public static function canEdit(\$record): bool\n    {\n        return auth()->user()->can('edit {$modelWords}');\n    }\n\n    public static function canDelete(\$record): bool\n    {\n        return auth()->user()->can('delete {$modelWords}');\n    }\n\n    public static function getNavigationLabel(): string\n    {\n        return __('{$pluralName}');\n    }\n    \n    public static function getModelLabel(): string\n    {\n        return __('{$modelName}');\n    }\n    \n    public static function getPluralModelLabel(): string\n    {\n        return __('{$pluralName}');\n    }";

        return str_replace('protected static ?string $model', $methods . "\n\n    protected static ?string \$model", $content);
    }

    private function injectSoftDeleteQuery(string $content, ?Generator $generator): string
    {
        if (! ($generator->soft_deletes ?? false) || str_contains($content, 'getEloquentQuery')) return $content;

        $query = "\n\n    public static function getEloquentQuery(): \\Illuminate\\Database\\Eloquent\\Builder\n    {\n        return parent::getEloquentQuery()->withoutGlobalScopes([\\Illuminate\\Database\\Eloquent\\SoftDeletingScope::class]);\n    }";
        return str_replace('protected static ?string $model', $query . "\n\n    protected static ?string \$model", $content);
    }

    private function injectNavigationIcon(string $content, string $modelName): string
    {
        if (! str_contains($content, 'protected static string|BackedEnum|null $navigationIcon')) return $content;

        $icon = app(IconResolver::class)->resolve($modelName);
        return preg_replace(
            '/protected static string\|BackedEnum\|null \$navigationIcon = .+?;/',
            "protected static string|BackedEnum|null \$navigationIcon = '{$icon}';",
            $content
        );
    }

    private function writeFormSchema(string $modelName, array $fields, ?Generator $generator, string $pluralName): void
    {
        $formSchemaPath = app_path("Filament/Resources/{$pluralName}/Schemas/{$modelName}Form.php");
        if (! File::exists($formSchemaPath)) return;

        $formGenerator = new FormSchemaGenerator(new StubRenderer());
        $formFields    = $this->buildFormFields($fields, $modelName, $generator);
        $cardSize      = $generator->default_card_size ?? 3;

        $content = $formGenerator->generate([
            'class_name'  => $modelName,
            'plural_name' => $pluralName,
            'fields'      => $formFields,
            'imports'     => $formGenerator->buildImports($fields, ! empty($generator->relationships)),
            'card_size'   => $cardSize,
            'offset_size' => ceil((12 - $cardSize) / 2) + 1,
        ]);

        $this->writeFile($formSchemaPath, $content);
        $this->writeInfolistSchema($modelName, $fields, $generator, $pluralName);
    }

    private function writeInfolistSchema(string $modelName, array $fields, ?Generator $generator, string $pluralName): void
    {
        $infolistPath = app_path("Filament/Resources/{$pluralName}/Schemas/{$modelName}Infolist.php");

        $content = (new InfolistGenerator(new StubRenderer()))->generate([
            'model_name'    => $modelName,
            'plural_name'   => $pluralName,
            'fields'        => $fields,
            'relationships' => $generator->relationships ?? [],
        ]);

        $this->writeFile($infolistPath, $content);
        $this->injectInfolistIntoResource($modelName, $pluralName);
    }

    private function injectInfolistIntoResource(string $modelName, string $pluralName): void
    {
        $resourcePath = app_path("Filament/Resources/{$pluralName}/{$modelName}Resource.php");
        if (! File::exists($resourcePath)) return;

        $content = File::get($resourcePath);

        if (str_contains($content, 'public static function infolist')) return;

        $infolistMethod = "\n\n    public static function infolist(\\Filament\\Schemas\\Schema \$schema): \\Filament\\Schemas\\Schema\n    {\n        return \\App\\Filament\\Resources\\{$pluralName}\\Schemas\\{$modelName}Infolist::configure(\$schema);\n    }";

        // Inject before the closing brace of the class
        $content = preg_replace('/\n}(\s*)$/', $infolistMethod . "\n}\$1", $content);

        File::put($resourcePath, $content);
    }

    private function buildFormFields(array $fields, string $modelName, ?Generator $generator): array
    {
        $sortedFields        = $this->fieldResolver->mergeAndSortFields($fields, $generator->relationships ?? []);
        $fieldBuilder        = new FieldBuilder();
        $relationshipBuilder = new RelationshipBuilder($this->fieldResolver);

        return collect($sortedFields)
            ->filter(fn ($f) => ($f['in_form'] ?? true) === true)
            ->map(function ($f) use ($modelName, $fieldBuilder, $relationshipBuilder, $generator) {
                if (($f['field_type'] ?? 'field') === 'relationship' || isset($f['rel_type'])) {
                    return $relationshipBuilder->buildFormField($f, $modelName, $generator->query_conditions ?? []);
                }
                return $fieldBuilder->buildFormField($f, $modelName);
            })
            ->filter()->values()->toArray();
    }

    private function buildCustomColumns(array $customTableColumns, Generator $generator, TableSchemaGenerator $tableGenerator): array
    {
        usort($customTableColumns, fn ($a, $b) => ($a['order'] ?? PHP_INT_MAX) <=> ($b['order'] ?? PHP_INT_MAX));

        $relDisplayMap = [];
        foreach ($generator->relationships ?? [] as $rel) {
            $relDisplayMap[$rel['name']] = $this->fieldResolver->resolveDisplayField($rel['related_model'] ?? '', $rel['display_column'] ?? null);
        }

        $columns = [];
        foreach ($customTableColumns as $col) {
            if (! is_array($col)) continue;
            $rawName  = $col['name'];
            $isDotted = str_contains($rawName, '.');
            if (! $isDotted && isset($relDisplayMap[$rawName])) {
                $rawName  = $rawName . '.' . $relDisplayMap[$rawName];
                $isDotted = true;
            }
            $colName    = $isDotted ? $rawName : Str::snake($rawName);
            $labelBase  = $isDotted ? explode('.', $rawName)[0] : $rawName;
            $colLabel   = Str::title(str_replace('_', ' ', $labelBase));
            $searchable = ($col['searchable'] ?? false) ? '->searchable()' : '';
            $sortable   = ($col['sortable']   ?? false) ? '->sortable()' : '';
            $columns[]  = $tableGenerator->buildColumn($colName, $colLabel, $col['html_type'] ?? 'text', $searchable, $sortable);
        }

        return $columns;
    }

    private function buildAutoColumns(array $fields, Generator $generator, string $modelName, TableSchemaGenerator $tableGenerator): array
    {
        $allItems = [];

        foreach ($fields as $field) {
            if (($field['in_table'] ?? true) !== true) continue;
            $field['_sort_order'] = $field['order'] ?? PHP_INT_MAX;
            $field['_item_type']  = 'field';
            $allItems[] = $field;
        }

        foreach ($generator->relationships ?? [] as $rel) {
            if (! ($rel['in_table'] ?? true) || $rel['type'] !== 'belongsTo') continue;
            $rel['_sort_order'] = $rel['order'] ?? PHP_INT_MAX;
            $rel['_item_type']  = 'relationship';
            $allItems[] = $rel;
        }

        usort($allItems, fn ($a, $b) => ($a['_sort_order'] ?? PHP_INT_MAX) <=> ($b['_sort_order'] ?? PHP_INT_MAX));

        $columns = [];
        foreach ($allItems as $item) {
            if (($item['_item_type'] ?? '') === 'relationship') {
                $relName      = $this->fieldResolver->resolveRelationshipAccessor($item);
                $relModel     = $item['related_model'];
                $displayField = $this->fieldResolver->resolveDisplayField($relModel, $item['display_column'] ?? null);
                $relatedTable = Str::snake(Str::plural($relModel));
                $cols         = Schema::hasTable($relatedTable) ? Schema::getColumnListing($relatedTable) : [];
                $hasFirstLast = in_array('first_name', $cols) && in_array('last_name', $cols);

                $columns[] = $hasFirstLast && $displayField === 'first_name'
                    ? "TextColumn::make('{$relName}.first_name')->label(__('{$relModel}'))->formatStateUsing(fn (\$record) => \$record->{$relName} ? \$record->{$relName}->first_name . ' ' . \$record->{$relName}->last_name : '')->toggleable()->searchable()->sortable()"
                    : "TextColumn::make('{$relName}.{$displayField}')->label(__('{$relModel}'))->toggleable()->searchable()->sortable()";
            } else {
                $colName    = Str::snake($item['name']);
                $label      = Str::title(str_replace('_', ' ', $colName));
                $searchable = ($item['searchable'] ?? false) ? '->searchable()' : '';
                $sortable   = ($item['searchable'] ?? false) ? '->sortable()' : '';
                $columns[]  = $tableGenerator->buildColumn($colName, "{$modelName}.{$label}", $item['html_type'] ?? 'text', $searchable, $sortable);
            }
        }

        return $columns;
    }

    private function writeFile(string $path, string $content): void
    {
        app(FileManager::class)->ensureWritable($path);
        $dir = dirname($path);
        if (! File::exists($dir)) File::makeDirectory($dir, 0755, true);
        File::put($path, $content);
    }
}
