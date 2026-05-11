<?php

namespace App\Services\CrudGenerator\Builders;

use App\Models\Generator;
use App\Services\CrudGenerator\Concerns\BuildsRelationships;
use App\Services\CrudGenerator\Support\FieldResolver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AlterMigrationBuilder
{
    use BuildsRelationships;

    public function __construct(private FieldResolver $fieldResolver)
    {
    }

    public function build(
        string $tableName,
        array $fields,
        Generator $generator,
        array $previousFields,
        array $previousRelationships
    ): array {
        $existingColumns = Schema::getColumnListing($tableName);

        $lastColumn = null;
        $createdAtIndex = array_search('created_at', $existingColumns);
        if ($createdAtIndex !== false && $createdAtIndex > 0) {
            $lastColumn = $existingColumns[$createdAtIndex - 1];
        }

        if (empty($previousFields)) {
            $prevGen = Generator::find($generator->id);
            $previousFields = $prevGen ? $prevGen->getOriginal('fields') ?? [] : [];
        }

        $previousFieldNames = collect($previousFields)->pluck('name')->map(fn ($n) => Str::snake($n))->toArray();
        $currentFieldNames = collect($fields)->pluck('name')->map(fn ($n) => Str::snake($n))->toArray();

        $up = [];
        $down = [];
        $processedFields = [];

        $this->detectRenames($previousFields, $fields, $existingColumns, $up, $down, $processedFields);
        $this->dropRemovedFields($previousFieldNames, $currentFieldNames, $existingColumns, $previousFields, $processedFields, $up, $down);
        $this->addNewRelationshipForeignKeys($generator, $existingColumns, $lastColumn, $up, $down);
        $this->dropRemovedRelationshipForeignKeys($generator, $previousRelationships, $existingColumns, $up, $down);
        $this->addNewFields($fields, $existingColumns, $processedFields, $lastColumn, $up, $down);
        $this->modifyChangedFieldTypes($fields, $previousFields, $existingColumns, $up, $down);
        $this->handleSoftDeletes($generator, $existingColumns, $up, $down);

        return ['up' => $up, 'down' => $down];
    }

    private function detectRenames(array $previousFields, array $fields, array $existingColumns, array &$up, array &$down, array &$processedFields): void
    {
        if (count($previousFields) !== count($fields)) {
            return;
        }

        $renames = [];

        for ($i = 0; $i < count($previousFields); $i++) {
            $oldName = Str::snake($previousFields[$i]['name']);
            $newName = Str::snake($fields[$i]['name']);

            if ($oldName === $newName) {
                continue;
            }

            if (! in_array($oldName, $existingColumns) || in_array($newName, $existingColumns)) {
                continue;
            }

            $oldType = $previousFields[$i]['type'] ?? '';
            $newType = $fields[$i]['type'] ?? '';

            if ($oldType !== $newType) {
                continue;
            }

            $renames[] = [
                'old' => $oldName,
                'new' => $newName,
                'type' => $oldType,
            ];
        }

        if (count($renames) !== 1) {
            return;
        }

        $rename = $renames[0];

        $up[] = "\$table->renameColumn('{$rename['old']}', '{$rename['new']}');";
        $down[] = "\$table->renameColumn('{$rename['new']}', '{$rename['old']}');";
        $processedFields[] = $rename['old'];
        $processedFields[] = $rename['new'];
    }

    private function dropRemovedFields(array $previousFieldNames, array $currentFieldNames, array $existingColumns, array $previousFields, array $processedFields, array &$up, array &$down): void
    {
        foreach (array_diff($previousFieldNames, $currentFieldNames) as $removed) {
            if (
                ! in_array($removed, $existingColumns)
                || in_array($removed, ['id', 'created_at', 'updated_at', 'deleted_at'])
                || in_array($removed, $processedFields)
            ) {
                continue;
            }

            $up[] = "\$table->dropColumn('{$removed}');";
            $oldField = collect($previousFields)->first(fn ($f) => Str::snake($f['name']) === $removed);
            if ($oldField) {
                $downLine = "\$table->{$oldField['type']}('{$removed}')";
                if (! ($oldField['required'] ?? true)) {
                    $downLine .= '->nullable()';
                }
                $down[] = $downLine . ';';
            }
        }
    }

    private function addNewRelationshipForeignKeys(Generator $generator, array $existingColumns, ?string $lastColumn, array &$up, array &$down): void
    {
        foreach ($generator->relationships ?? [] as $rel) {
            if ($rel['type'] !== 'belongsTo' || ! ($rel['add_foreign_key_field'] ?? true)) {
                continue;
            }

            $fk = $this->fieldResolver->resolveForeignKeyName($rel);
            if (in_array($fk, $existingColumns)) {
                continue;
            }

            $relTable = Str::snake(Str::plural($rel['related_model']));
            $after = $lastColumn ? "->after('{$lastColumn}')" : '';
            $up[] = "\$table->unsignedBigInteger('{$fk}')->nullable(){$after};";
            $up[] = "\$table->foreign('{$fk}')->references('id')->on('{$relTable}')->onDelete('cascade');";
            $down[] = "\$table->dropForeign(['{$fk}']);";
            $down[] = "\$table->dropColumn('{$fk}');";
        }
    }

    private function dropRemovedRelationshipForeignKeys(Generator $generator, array $previousRelationships, array $existingColumns, array &$up, array &$down): void
    {
        $currentRelNames = collect($generator->relationships ?? [])->pluck('name')->toArray();

        foreach ($previousRelationships as $oldRel) {
            if ($oldRel['type'] !== 'belongsTo') {
                continue;
            }

            if (in_array($oldRel['name'], $currentRelNames)) {
                continue;
            }

            $fk = $this->fieldResolver->resolveForeignKeyName($oldRel);
            if (! in_array($fk, $existingColumns)) {
                continue;
            }

            $up[] = "\$table->dropForeign(['{$fk}']);";
            $up[] = "\$table->dropColumn('{$fk}');";
            $down[] = "\$table->unsignedBigInteger('{$fk}')->nullable();";
        }
    }

    private function addNewFields(array $fields, array $existingColumns, array $processedFields, ?string $lastColumn, array &$up, array &$down): void
    {
        foreach ($fields as $field) {
            $name = Str::snake($field['name']);
            if (in_array($name, $existingColumns) || in_array($name, $processedFields)) {
                continue;
            }

            $type = $field['type'];
            $htmlType = $field['html_type'] ?? 'text';

            $fieldLine = match (true) {
                $type === 'enum' => $this->buildEnumLine($name, $field['options'] ?? 'active,inactive'),
                in_array($htmlType, ['tags', 'checkbox', 'multiselect']) => "\$table->json('{$name}')",
                default => "\$table->{$type}('{$name}')",
            };

            $fieldLine .= '->nullable()';
            if ($field['index'] ?? false) {
                $fieldLine .= '->index()';
            }
            if ($lastColumn) {
                $fieldLine .= "->after('{$lastColumn}')";
            }

            $up[] = $fieldLine . ';';
            $down[] = "\$table->dropColumn('{$name}');";
        }
    }

    private function buildEnumLine(string $name, string $options): string
    {
        $opts = array_map('trim', explode(',', $options));
        $enumVals = "['" . implode("', '", $opts) . "']";
        return "\$table->enum('{$name}', {$enumVals})";
    }

    private function modifyChangedFieldTypes(array $fields, array $previousFields, array $existingColumns, array &$up, array &$down): void
    {
        foreach ($fields as $field) {
            $name = Str::snake($field['name']);
            if (! in_array($name, $existingColumns)) {
                continue;
            }

            $oldField = collect($previousFields)->first(fn ($f) => Str::snake($f['name']) === $name);
            if ($oldField && $oldField['type'] !== $field['type']) {
                $up[] = "\$table->{$field['type']}('{$name}')->change();";
                $down[] = "\$table->{$oldField['type']}('{$name}')->change();";
            }
        }
    }

    private function handleSoftDeletes(Generator $generator, array $existingColumns, array &$up, array &$down): void
    {
        $hasSoftDeletes = $generator->soft_deletes ?? false;
        $hasDeletedAt = in_array('deleted_at', $existingColumns);

        if ($hasSoftDeletes && ! $hasDeletedAt) {
            $up[] = "\$table->softDeletes();";
        }

        if (! $hasSoftDeletes && $hasDeletedAt) {
            $up[] = "\$table->dropColumn('deleted_at');";
            $down[] = "\$table->softDeletes();";
        }
    }
}
