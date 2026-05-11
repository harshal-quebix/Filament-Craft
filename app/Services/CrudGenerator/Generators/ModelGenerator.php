<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Concerns\BuildsRelationships;
use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\FieldResolver;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Str;

class ModelGenerator implements GeneratorInterface
{
    use BuildsRelationships;

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

        $fillableFields = $this->fieldResolver->buildFillable($fields, $relationships);
        $fillableString = "protected \$fillable = ['" . implode("', '", $fillableFields) . "'];";

        $casts = $this->fieldResolver->buildCasts($fields);
        $castsString = '';
        if (! empty($casts)) {
            $castLines = [];
            foreach ($casts as $key => $value) {
                $castLines[] = "'{$key}' => '{$value}'";
            }
            $castsString = "\n    protected \$casts = [\n        " . implode(",\n        ", $castLines) . "\n    ];";
        }

        $hasPasswordField = $this->fieldResolver->hasPasswordField($fields);

        $useStatements = ['use Illuminate\Database\Eloquent\Model;'];
        $traits = [];

        if ($hasPasswordField) {
            $useStatements[] = 'use Illuminate\Support\Facades\Hash;';
        }

        if ($softDeletes) {
            $useStatements[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
            $traits[] = 'SoftDeletes';
        }

        $traitString = ! empty($traits) ? "\n    use " . implode(', ', $traits) . ";" : '';

        $relationshipMethods = $this->buildRelationshipMethods($relationships);
        $passwordMutators = $hasPasswordField ? $this->buildPasswordMutators($fields) : '';

        return $this->stubRenderer->load('model.stub')->replace([
            'className' => $modelName,
            'useStatements' => implode("\n", $useStatements),
            'implements' => '',
            'traits' => $traitString,
            'fillable' => $fillableString,
            'casts' => $castsString,
            'relationships' => $relationshipMethods,
            'mutators' => $passwordMutators,
        ]);
    }

    private function buildRelationshipMethods(array $relationships): string
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

    private function buildPasswordMutators(array $fields): string
    {
        $mutators = '';

        foreach ($fields as $field) {
            if (($field['html_type'] ?? '') !== 'password') {
                continue;
            }

            $name = Str::snake($field['name']);
            $studly = Str::studly($name);
            $mutators .= "\n\n    public function set{$studly}Attribute(\$value)\n    {\n        \$this->attributes['{$name}'] = Hash::make(\$value);\n    }";
        }

        return $mutators;
    }
}
