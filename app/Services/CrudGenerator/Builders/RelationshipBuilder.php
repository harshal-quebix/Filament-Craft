<?php

namespace App\Services\CrudGenerator\Builders;

use App\Services\CrudGenerator\Concerns\BuildsRelationships;
use App\Services\CrudGenerator\Support\FieldResolver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class RelationshipBuilder
{
    use BuildsRelationships;

    public function __construct(private FieldResolver $fieldResolver)
    {
    }

    public function build(array $relationships): string
    {
        $methods = '';
        $addedMethods = [];

        foreach ($relationships as $rel) {
            $name = $this->resolveRelationshipAccessor($rel);

            if (in_array($name, $addedMethods)) {
                continue;
            }

            $addedMethods[] = $name;
            $methods .= $this->buildRelationshipMethod($rel);
        }

        return $methods;
    }

    public function buildFormField(array $rel, string $modelName, array $queryConditions = []): ?string
    {
        if (! ($rel['in_form'] ?? true)) {
            return null;
        }

        $relName = $this->fieldResolver->resolveRelationshipAccessor($rel);
        $relModel = $rel['related_model'];
        $relType = $rel['type'] ?? $rel['rel_type'] ?? 'belongsTo';
        $colSpan = $rel['column_span'] ?? 6;
        $spanCode = "->columnSpan({$colSpan})";
        $displayField = $rel['display_column'] ?? 'name';

        $relatedTable = Str::snake(Str::plural($relModel));
        $columns = Schema::hasTable($relatedTable)
            ? Schema::getColumnListing($relatedTable)
            : [];
        $hasFirstLast = in_array('first_name', $columns) && in_array('last_name', $columns);

        $queryClosure = $this->buildQueryClosure($rel, $queryConditions);

        return match ($relType) {
            'belongsTo' => $this->buildBelongsToField($rel, $relName, $relModel, $displayField, $hasFirstLast, $queryClosure, $spanCode),
            'belongsToMany' => $this->buildBelongsToManyField($rel, $relName, $relModel, $displayField, $hasFirstLast, $queryClosure, $spanCode),
            default => null,
        };
    }

    private function buildBelongsToField(array $rel, string $relName, string $relModel, string $displayField, bool $hasFirstLast, ?string $queryClosure, string $spanCode): string
    {
        $fk = $this->fieldResolver->resolveForeignKeyName($rel);

        if ($hasFirstLast && $displayField === 'first_name') {
            $baseQuery = "\$query->selectRaw('id, CONCAT(first_name, \" \", last_name) as first_name')";
            if ($queryClosure) {
                $baseQuery .= '->' . str_replace('\$query->', '', $queryClosure);
            }
            return "Select::make('{$fk}')->label(__('{$relModel}'))->relationship('{$relName}', 'first_name', fn (\$query) => {$baseQuery})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->first_name . ' ' . \$record->last_name)->searchable()->preload(){$spanCode}";
        }

        $relationshipArgs = "'{$relName}', '{$displayField}'";
        if ($queryClosure) {
            $relationshipArgs .= ", fn (\$query) => {$queryClosure}";
        }

        return "Select::make('{$fk}')->label(__('{$relModel}'))->relationship({$relationshipArgs})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->{$displayField} ?? \$record->id)->searchable()->preload(){$spanCode}";
    }

    private function buildBelongsToManyField(array $rel, string $relName, string $relModel, string $displayField, bool $hasFirstLast, ?string $queryClosure, string $spanCode): string
    {
        if ($hasFirstLast && $displayField === 'first_name') {
            $baseQuery = "\$query->selectRaw('id, CONCAT(first_name, \" \", last_name) as first_name')";
            if ($queryClosure) {
                $baseQuery .= '->' . str_replace('\$query->', '', $queryClosure);
            }
            return "Select::make('{$relName}')->label(__('{$relModel}s'))->relationship('{$relName}', 'first_name', fn (\$query) => {$baseQuery})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->first_name . ' ' . \$record->last_name)->multiple()->searchable()->preload(){$spanCode}";
        }

        $relationshipArgs = "'{$relName}', '{$displayField}'";
        if ($queryClosure) {
            $relationshipArgs .= ", fn (\$query) => {$queryClosure}";
        }

        return "Select::make('{$relName}')->label(__('{$relModel}s'))->relationship({$relationshipArgs})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->{$displayField} ?? \$record->id)->multiple()->searchable()->preload(){$spanCode}";
    }

    private function buildQueryClosure(array $rel, array $queryConditions): ?string
    {
        $relName = $this->fieldResolver->resolveRelationshipAccessor($rel);
        $chains = [];

        foreach ($queryConditions as $condition) {
            if (! ($condition['in_form'] ?? true)) {
                continue;
            }

            $conditionType = $condition['condition_type'] ?? 'where';
            $relField = $condition['relationship'] ?? '';

            if ($conditionType !== 'where' && ! ($conditionType === 'whereHas' && $relField === $relName)) {
                continue;
            }

            if ($relField !== $relName) {
                continue;
            }

            $field = $condition['field'];
            $operator = $condition['operator'] ?? '=';
            $value = $condition['value'] ?? '';
            $cleanField = str_contains($field, '.') ? last(explode('.', $field)) : $field;

            $chains[] = match ($operator) {
                '=' => "where('{$cleanField}', '{$value}')",
                '!=' => "where('{$cleanField}', '!=', '{$value}')",
                '>' => "where('{$cleanField}', '>', '{$value}')",
                '<' => "where('{$cleanField}', '<', '{$value}')",
                '>=' => "where('{$cleanField}', '>=', '{$value}')",
                '<=' => "where('{$cleanField}', '<=', '{$value}')",
                'like' => "where('{$cleanField}', 'like', '%{$value}%')",
                default => "where('{$cleanField}', '{$value}')",
            };
        }

        return empty($chains) ? null : '\$query->' . implode('->', $chains);
    }
}
