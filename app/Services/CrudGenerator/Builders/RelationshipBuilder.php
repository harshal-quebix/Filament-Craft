<?php

namespace App\Services\CrudGenerator\Builders;

use App\Services\CrudGenerator\Support\FieldResolver;
use Illuminate\Support\Str;

class RelationshipBuilder
{
    public function __construct(private FieldResolver $fieldResolver)
    {
    }
    public function build(array $relationships): string
    {
        $methods = '';
        $addedMethods = [];

        foreach ($relationships as $rel) {
            $name = $this->resolveAccessor($rel);
            $type = $rel['type'];
            $model = $rel['related_model'];

            if (in_array($name, $addedMethods)) continue;
            $addedMethods[] = $name;

            $body = match ($type) {
                'belongsTo' => "return \$this->belongsTo({$model}::class);",
                'hasMany' => "return \$this->hasMany({$model}::class);",
                'hasOne' => "return \$this->hasOne({$model}::class);",
                'belongsToMany' => "return \$this->belongsToMany({$model}::class);",
                default => "return \$this->belongsTo({$model}::class);",
            };

            $methods .= "\n\n    public function {$name}()\n    {\n        {$body}\n    }";
        }

        return $methods;
    }

    public function buildFormField(array $rel, string $modelName, array $queryConditions = []): ?string
    {
        if (!($rel['in_form'] ?? true)) return null;

        $relName = $this->fieldResolver->resolveRelationshipAccessor($rel);
        $relModel = $rel['related_model'];
        $relType = $rel['type'] ?? $rel['rel_type'] ?? 'belongsTo';
        $colSpan = $rel['column_span'] ?? 6;
        $spanCode = "->columnSpan({$colSpan})";
        $displayField = $rel['display_column'] ?? 'name';

        $relatedTable = Str::snake(Str::plural($relModel));
        $columns = \Illuminate\Support\Facades\Schema::hasTable($relatedTable)
            ? \Illuminate\Support\Facades\Schema::getColumnListing($relatedTable)
            : [];
        $hasFirstLast = in_array('first_name', $columns) && in_array('last_name', $columns);

        $queryClosure = $this->buildQueryClosure($rel, $queryConditions);

        switch ($relType) {
            case 'belongsTo':
                $fk = $this->fieldResolver->resolveForeignKeyName($rel);
                if ($hasFirstLast && $displayField === 'first_name') {
                    $baseQuery = "\$query->selectRaw('id, CONCAT(first_name, \" \", last_name) as first_name')";
                    if ($queryClosure) {
                        $baseQuery .= '->' . str_replace('\$query->', '', $queryClosure);
                    }
                    return "Select::make('{$fk}')->label(__('{$relModel}'))->relationship('{$relName}', 'first_name', fn (\$query) => {$baseQuery})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->first_name . ' ' . \$record->last_name)->searchable()->preload(){$spanCode}";
                } else {
                    $relationshipArgs = "'{$relName}', '{$displayField}'";
                    if ($queryClosure) {
                        $relationshipArgs .= ", fn (\$query) => {$queryClosure}";
                    }
                    return "Select::make('{$fk}')->label(__('{$relModel}'))->relationship({$relationshipArgs})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->{$displayField} ?? \$record->id)->searchable()->preload(){$spanCode}";
                }

            case 'belongsToMany':
                if ($hasFirstLast && $displayField === 'first_name') {
                    $baseQuery = "\$query->selectRaw('id, CONCAT(first_name, \" \", last_name) as first_name')";
                    if ($queryClosure) {
                        $baseQuery .= '->' . str_replace('\$query->', '', $queryClosure);
                    }
                    return "Select::make('{$relName}')->label(__('{$relModel}s'))->relationship('{$relName}', 'first_name', fn (\$query) => {$baseQuery})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->first_name . ' ' . \$record->last_name)->multiple()->searchable()->preload(){$spanCode}";
                } else {
                    $relationshipArgs = "'{$relName}', '{$displayField}'";
                    if ($queryClosure) {
                        $relationshipArgs .= ", fn (\$query) => {$queryClosure}";
                    }
                    return "Select::make('{$relName}')->label(__('{$relModel}s'))->relationship({$relationshipArgs})->getOptionLabelFromRecordUsing(fn (\$record) => \$record->{$displayField} ?? \$record->id)->multiple()->searchable()->preload(){$spanCode}";
                }

            default:
                return null;
        }
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
