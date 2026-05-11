<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Concerns\BuildsRelationships;
use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Str;

class MigrationGenerator implements GeneratorInterface
{
    use BuildsRelationships;

    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        return $config['is_alter']
            ? $this->generateAlter($config)
            : $this->generateCreate($config);
    }

    private function generateCreate(array $config): string
    {
        $tableName = $config['table_name'];
        $fields = $config['fields'];
        $relationships = $config['relationships'] ?? [];
        $softDeletes = $config['soft_deletes'] ?? false;
        $primaryKey = $config['primary_key'] ?? 'id';
        $primaryKeyType = $config['primary_key_type'] ?? 'int';

        $primaryKeyLine = match ($primaryKeyType) {
            'uuid' => "\$table->uuid('{$primaryKey}')->primary();",
            default => "\$table->id('{$primaryKey}');",
        };

        $fieldsString = '';
        foreach ($fields as $field) {
            $fieldsString .= $this->buildFieldLine($field) . PHP_EOL . '            ';
        }

        $foreignKeyFields = '';
        foreach ($relationships as $rel) {
            if ($rel['type'] !== 'belongsTo' || ! ($rel['add_foreign_key_field'] ?? true)) {
                continue;
            }

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

    private function buildFieldLine(array $field): string
    {
        $type = $field['type'];
        $name = Str::snake($field['name']);
        $htmlType = $field['html_type'] ?? 'text';

        if ($htmlType === 'file') {
            $type = 'string';
        }

        $fieldLine = match (true) {
            $type === 'enum' => $this->buildEnumLine($name, $field['options'] ?? 'active,inactive'),
            in_array($htmlType, ['tags', 'checkbox']) => "\$table->json('{$name}')",
            $type === 'string' => "\$table->string('{$name}')",
            default => "\$table->{$type}('{$name}')",
        };

        $fieldLine .= '->nullable()';
        if ($field['index'] ?? false) {
            $fieldLine .= '->index()';
        }
        $fieldLine .= ';';

        return $fieldLine;
    }

    private function buildEnumLine(string $name, string $options): string
    {
        $opts = array_map('trim', explode(',', $options));
        $enumVals = "['" . implode("', '", $opts) . "']";
        return "\$table->enum('{$name}', {$enumVals})";
    }

    private function generateAlter(array $config): string
    {
        $tableName = $config['table_name'];
        $operations = $config['operations'] ?? [];
        $downOperations = $config['down_operations'] ?? [];

        $up = implode(PHP_EOL . '            ', $operations);
        $down = ! empty($downOperations) ? implode(PHP_EOL . '            ', $downOperations) : '// No operations';

        return $this->stubRenderer->load('migration.alter.stub')->replace([
            'tableName' => $tableName,
            'operations' => $up,
            'downOperations' => $down,
        ]);
    }
}
