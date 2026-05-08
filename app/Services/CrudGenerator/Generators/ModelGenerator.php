<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\FieldResolver;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class ModelGenerator implements GeneratorInterface
{
    public function __construct(
        private StubRenderer $stubRenderer,
        private FieldResolver $fieldResolver,
    ) {
    }

    public function generate(array $config): string
    {
        $modelName = $config['model_name'];
        $fields = $config['fields'];
        $relationships = $config['relationships'] ?? [];
        $softDeletes = $config['soft_deletes'] ?? false;

        $fillableFields = collect($fields)->map(fn($f) => Str::snake($f['name']))->toArray();

        foreach ($relationships as $relationship) {
            if ($relationship['type'] === 'belongsTo' && ($relationship['add_foreign_key_field'] ?? true)) {
                $fk = $this->fieldResolver->resolveForeignKeyName($relationship);
                if (!in_array($fk, $fillableFields)) $fillableFields[] = $fk;
            }
        }

        $fillableString = "protected \$fillable = ['" . implode("', '", $fillableFields) . "'];";

        $casts = [];
        foreach ($fields as $field) {
            $name = Str::snake($field['name']);
            $type = $field['type'];
            $htmlType = $field['html_type'] ?? '';

            if ($type === 'boolean') {
                $casts[] = "'{$name}' => 'boolean'";
            } elseif (in_array($type, ['json', 'jsonb'])) {
                $casts[] = "'{$name}' => 'array'";
            } elseif (in_array($type, ['date', 'dateTime', 'timestamp'])) {
                $casts[] = "'{$name}' => 'datetime'";
            } elseif (in_array($htmlType, ['tags', 'checkbox', 'multiselect'])) {
                $casts[] = "'{$name}' => 'array'";
            }
        }

        $castsString = '';
        if (!empty($casts)) {
            $castsString = "\n    protected \$casts = [\n        " . implode(",\n        ", $casts) . "\n    ];";
        }

        $hasPasswordField = collect($fields)->contains(fn($f) => ($f['html_type'] ?? '') === 'password');

        $useStatements = 'use Illuminate\Database\Eloquent\Model;';
        $implements = '';
        $traits = [];

        if ($hasPasswordField) {
            $useStatements .= "\nuse Illuminate\Support\Facades\Hash;";
        }

        if ($softDeletes) {
            $useStatements .= "\nuse Illuminate\Database\Eloquent\SoftDeletes;";
            $traits[] = 'SoftDeletes';
        }

        $traitString = !empty($traits) ? "\n    use " . implode(', ', $traits) . ";" : '';

        $relationshipMethods = '';
        $addedMethods = [];

        foreach ($relationships as $rel) {
            $name = $this->fieldResolver->resolveRelationshipAccessor($rel);
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

            $relationshipMethods .= "\n\n    public function {$name}()\n    {\n        {$body}\n    }";
        }

        $passwordMutators = '';
        if ($hasPasswordField) {
            foreach ($fields as $field) {
                if (($field['html_type'] ?? '') !== 'password') continue;
                $name = Str::snake($field['name']);
                $studly = Str::studly($name);
                $passwordMutators .= "\n\n    public function set{$studly}Attribute(\$value)\n    {\n        \$this->attributes['{$name}'] = Hash::make(\$value);\n    }";
            }
        }

        return $this->stubRenderer->load('model.stub')->replace([
            'className' => $modelName,
            'useStatements' => $useStatements,
            'implements' => $implements,
            'traits' => $traitString,
            'fillable' => $fillableString,
            'casts' => $castsString,
            'relationships' => $relationshipMethods,
            'mutators' => $passwordMutators,
        ]);
    }

}
