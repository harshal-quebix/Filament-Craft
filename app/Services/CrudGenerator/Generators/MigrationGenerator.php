<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Str;

class MigrationGenerator implements GeneratorInterface
{
    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        if ($config['is_alter']) {
            return $this->generateAlter($config);
        }
        
        return $this->generateCreate($config);
    }

    private function generateCreate(array $config): string
    {
        $tableName = $config['table_name'];
        $fields = $config['fields'];
        $relationships = $config['relationships'] ?? [];
        $softDeletes = $config['soft_deletes'] ?? false;
        $primaryKey = $config['primary_key'] ?? 'id';
        $primaryKeyType = $config['primary_key_type'] ?? 'int';

        $primaryKeyLine = match($primaryKeyType) {
            'uuid' => "\$table->uuid('{$primaryKey}')->primary();",
            'bigint' => "\$table->id('{$primaryKey}');",
            default => "\$table->id('{$primaryKey}');",
        };

        $fieldsString = '';
        foreach ($fields as $field) {
            $type = $field['type'];
            $name = Str::snake($field['name']);
            $htmlType = $field['html_type'] ?? 'text';

            if ($htmlType === 'file') {
                $type = 'string';
            }

            if ($type === 'enum') {
                $opts = array_map('trim', explode(',', $field['options'] ?? 'active,inactive'));
                $enumVals = "['" . implode("', '", $opts) . "']";
                $fieldLine = "\$table->enum('{$name}', {$enumVals})";
            } elseif (in_array($htmlType, ['tags', 'checkbox'])) {
                $fieldLine = "\$table->json('{$name}')";
            } elseif ($type === 'string') {
                $fieldLine = "\$table->string('{$name}')";
            } else {
                $fieldLine = "\$table->{$type}('{$name}')";
            }

            $fieldLine .= '->nullable()';
            if ($field['index'] ?? false) $fieldLine .= '->index()';
            $fieldLine .= ';' . PHP_EOL . '            ';
            $fieldsString .= $fieldLine;
        }

        $foreignKeyFields = '';
        foreach ($relationships as $rel) {
            if ($rel['type'] !== 'belongsTo' || !($rel['add_foreign_key_field'] ?? true)) continue;
            $fk = $this->resolveForeignKeyName($rel);
            $relTable = Str::snake(Str::plural($rel['related_model']));
            $foreignKeyFields .= "\$table->unsignedBigInteger('{$fk}')->nullable();" . PHP_EOL . '            ';
            $foreignKeyFields .= "\$table->foreign('{$fk}')->references('id')->on('{$relTable}')->onDelete('cascade');" . PHP_EOL . '            ';
        }

        $softDeletesString = $softDeletes ? "\$table->softDeletes();" . PHP_EOL . '            ' : '';

        return $this->stubRenderer->load('migration.create.stub')->replace([
            'tableName' => $tableName,
            'primaryKey' => $primaryKeyLine,
            'fields' => $fieldsString,
            'foreignKeys' => $foreignKeyFields,
            'softDeletes' => $softDeletesString,
        ]);
    }

    private function generateAlter(array $config): string
    {
        $tableName = $config['table_name'];
        $operations = $config['operations'] ?? [];
        $downOperations = $config['down_operations'] ?? [];

        $up = implode(PHP_EOL . '            ', $operations);
        $down = !empty($downOperations) ? implode(PHP_EOL . '            ', $downOperations) : '// No operations';

        return $this->stubRenderer->load('migration.alter.stub')->replace([
            'tableName' => $tableName,
            'operations' => $up,
            'downOperations' => $down,
        ]);
    }

    private function resolveForeignKeyName(array $relationship): string
    {
        if (!empty($relationship['foreign_key'])) {
            return $relationship['foreign_key'];
        }

        if (!empty($relationship['related_model'])) {
            return Str::snake($relationship['related_model']) . '_id';
        }

        return Str::snake($relationship['name'] ?? 'relation') . '_id';
    }
}
