<?php

namespace App\Services\CrudGenerator\Support;

use App\Filament\Resources\Generators\Schemas\GeneratorForm;
use App\Services\CrudGenerator\Concerns\BuildsRelationships;
use Illuminate\Support\Str;

class FieldResolver
{
    use BuildsRelationships;

    public function splitFields(array $unifiedFields): array
    {
        $fields = [];
        $relationships = [];

        foreach (array_values($unifiedFields) as $index => $item) {
            if (! is_array($item)) {
                continue;
            }

            $item['order'] = $item['order'] ?? ($index + 1);

            if (($item['field_type'] ?? 'field') === 'relationship') {
                $rel = $item;
                if (isset($item['rel_type'])) {
                    $rel['type'] = $item['rel_type'];
                }
                if (isset($item['rel_column_span'])) {
                    $rel['column_span'] = $item['rel_column_span'];
                }
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
        if (! empty($savedDisplayColumn)) {
            return $savedDisplayColumn;
        }

        return GeneratorForm::resolveDisplayColumnFromTable($relatedModel);
    }

    public function buildFillable(array $fields, array $relationships = []): array
    {
        $fillable = collect($fields)->map(fn ($f) => Str::snake($f['name']))->toArray();

        foreach ($relationships as $relationship) {
            if ($relationship['type'] === 'belongsTo' && ($relationship['add_foreign_key_field'] ?? true)) {
                $fk = $this->resolveForeignKeyName($relationship);
                if (! in_array($fk, $fillable)) {
                    $fillable[] = $fk;
                }
            }
        }

        return $fillable;
    }

    public function buildCasts(array $fields): array
    {
        $casts = [];

        foreach ($fields as $field) {
            $name = Str::snake($field['name']);
            $type = $field['type'];
            $htmlType = $field['html_type'] ?? '';

            $cast = match (true) {
                $type === 'boolean' => 'boolean',
                in_array($type, ['json', 'jsonb']) => 'array',
                in_array($type, ['date', 'dateTime', 'timestamp']) => 'datetime',
                in_array($htmlType, ['tags', 'checkbox', 'multiselect']) => 'array',
                default => null,
            };

            if ($cast !== null) {
                $casts[$name] = $cast;
            }
        }

        return $casts;
    }

    public function hasPasswordField(array $fields): bool
    {
        return collect($fields)->contains(fn ($f) => ($f['html_type'] ?? '') === 'password');
    }
}
