<?php

namespace App\Services\CrudGenerator\Services;

use App\Models\Generator;
use App\Services\CrudGenerator\Support\FieldResolver;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class QueryConditionApplier
{
    public function __construct(private FieldResolver $fieldResolver)
    {
    }

    public function apply(string $modelName, Generator $generator): void
    {
        $pluralName     = config('crud-generator.plural_names', [])[$modelName] ?? Str::plural($modelName);
        $formSchemaPath = app_path("Filament/Resources/{$pluralName}/Schemas/{$modelName}Form.php");

        if (! File::exists($formSchemaPath) || empty($generator->query_conditions)) return;

        $formContent = File::get($formSchemaPath);

        foreach ($generator->relationships ?? [] as $rel) {
            if ($rel['type'] !== 'belongsTo') continue;

            $relName    = $this->fieldResolver->resolveRelationshipAccessor($rel);
            $fk         = $this->fieldResolver->resolveForeignKeyName($rel);
            $conditions = $this->buildConditions($generator->query_conditions, $relName);

            if (empty($conditions)) continue;

            $hasConditions = collect($generator->query_conditions)
                ->contains(fn ($c) => ($c['relationship'] ?? '') === $relName);

            if (! $hasConditions) continue;

            $displayField = $this->fieldResolver->resolveDisplayField($rel['related_model'], $rel['display_column'] ?? null);
            $queryCode    = ", function (\$query) { " . implode('; ', $conditions) . "; }";

            $relatedTable = Str::snake(Str::plural($rel['related_model']));
            $hasFirstLast = Schema::hasTable($relatedTable)
                && in_array('first_name', Schema::getColumnListing($relatedTable))
                && in_array('last_name', Schema::getColumnListing($relatedTable));

            $pattern = "/Select::make\('{$fk}'\)[^\n]*->relationship\([^\)]*\)[^\n]*->searchable\(\)->preload\(\)->columnSpan\(\d+\)/";

            $newSelect = $hasFirstLast && $displayField === 'first_name'
                ? "Select::make('{$fk}')->label(__('" . ucfirst($rel['related_model']) . "'))->relationship('{$relName}', '{$displayField}'{$queryCode})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->first_name . ' ' . \$record->last_name)->searchable()->preload()->columnSpan(6)"
                : "Select::make('{$fk}')->label(__('" . ucfirst($rel['related_model']) . "'))->relationship('{$relName}', '{$displayField}'{$queryCode})->searchable()->preload()->columnSpan(6)";

            $formContent = preg_replace($pattern, $newSelect, $formContent);
        }

        File::put($formSchemaPath, $formContent);
    }

    private function buildConditions(array $queryConditions, string $relName): array
    {
        $conditions = [];

        foreach ($queryConditions as $condition) {
            if (! ($condition['in_form'] ?? true)) continue;

            $field         = $condition['field'];
            $operator      = $condition['operator'] ?? '=';
            $value         = $condition['value'] ?? '';
            $conditionType = $condition['condition_type'] ?? 'where';
            $relField      = $condition['relationship'] ?? '';
            $cleanField    = str_contains($field, '.') ? last(explode('.', $field)) : $field;

            if ($conditionType === 'where' || ($conditionType === 'whereHas' && $relField === $relName)) {
                $conditions[] = match ($operator) {
                    '='     => "\$query->where('{$cleanField}', '{$value}')",
                    '!='    => "\$query->where('{$cleanField}', '!=', '{$value}')",
                    '>'     => "\$query->where('{$cleanField}', '>', '{$value}')",
                    '<'     => "\$query->where('{$cleanField}', '<', '{$value}')",
                    '>='    => "\$query->where('{$cleanField}', '>=', '{$value}')",
                    '<='    => "\$query->where('{$cleanField}', '<=', '{$value}')",
                    'like'  => "\$query->where('{$cleanField}', 'like', '%{$value}%')",
                    default => "\$query->where('{$cleanField}', '{$value}')",
                };
            }
        }

        return $conditions;
    }
}
