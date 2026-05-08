<?php

namespace App\Services\CrudGenerator\Support;

use App\Filament\Resources\Generators\Schemas\GeneratorForm;
use Illuminate\Support\Str;

class FieldResolver
{
    public function splitFields(array $unifiedFields): array
    {
        $fields        = [];
        $relationships = [];

        foreach (array_values($unifiedFields) as $index => $item) {
            if (! is_array($item)) continue;

            $item['order'] = $item['order'] ?? ($index + 1);

            if (($item['field_type'] ?? 'field') === 'relationship') {
                $rel = $item;
                if (isset($item['rel_type']))        $rel['type']        = $item['rel_type'];
                if (isset($item['rel_column_span'])) $rel['column_span'] = $item['rel_column_span'];
                $relationships[] = $rel;
            } else {
                $fields[] = $item;
            }
        }

        return compact('fields', 'relationships');
    }

    public function mergeAndSortFields(array $fields, array $relationships): array
    {
        $merged = [];

        foreach ($fields as $field) {
            $field['_sort_order'] = $field['order'] ?? PHP_INT_MAX;
            $field['_field_type'] = 'field';
            $merged[] = $field;
        }

        foreach ($relationships as $rel) {
            $rel['_sort_order'] = $rel['order'] ?? PHP_INT_MAX;
            $rel['_field_type'] = 'relationship';
            $merged[] = $rel;
        }

        usort($merged, fn ($a, $b) => ($a['_sort_order'] ?? PHP_INT_MAX) <=> ($b['_sort_order'] ?? PHP_INT_MAX));

        foreach ($merged as &$item) {
            unset($item['_sort_order'], $item['_field_type']);
        }

        return $merged;
    }

    public function resolveDisplayField(string $relatedModel, ?string $savedDisplayColumn = null): string
    {
        if (! empty($savedDisplayColumn)) return $savedDisplayColumn;
        return GeneratorForm::resolveDisplayColumnFromTable($relatedModel);
    }

    public function resolveRelationshipAccessor(array $relationship): string
    {
        $type = $relationship['type'] ?? $relationship['rel_type'] ?? 'belongsTo';

        if ($type === 'belongsTo') {
            if (! empty($relationship['foreign_key']) && str_ends_with($relationship['foreign_key'], '_id')) {
                return Str::camel(Str::beforeLast($relationship['foreign_key'], '_id'));
            }
            if (! empty($relationship['related_model'])) {
                return Str::camel($relationship['related_model']);
            }
        }

        return Str::camel($relationship['name'] ?? $relationship['related_model'] ?? 'relation');
    }

    public function resolveForeignKeyName(array $relationship): string
    {
        if (! empty($relationship['foreign_key'])) return $relationship['foreign_key'];
        if (! empty($relationship['related_model'])) return Str::snake($relationship['related_model']) . '_id';
        return Str::snake($relationship['name'] ?? 'relation') . '_id';
    }
}
