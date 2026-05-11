<?php

namespace App\Services\CrudGenerator\Services;

use App\Models\Generator;
use App\Services\CrudGenerator\Concerns\BuildsRelationships;
use App\Services\CrudGenerator\Support\FieldResolver;
use Illuminate\Support\Facades\File;

class ModelRelationshipSyncer
{
    use BuildsRelationships;

    public function __construct(private FieldResolver $fieldResolver)
    {
    }

    public function sync(string $modelName, Generator $generator): void
    {
        $modelPath = config('crud-generator.paths.models') . "/{$modelName}.php";
        if (! File::exists($modelPath) || empty($generator->relationships)) {
            return;
        }

        $content = File::get($modelPath);

        foreach ($generator->relationships as $rel) {
            $name = $this->resolveRelationshipAccessor($rel);

            if (preg_match('/public function ' . preg_quote($name, '/') . '\s*\(/i', $content)) {
                continue;
            }

            $method = $this->buildRelationshipMethod($rel);
            $content = preg_replace('/\n}\s*$/', $method . "\n}", $content);
        }

        File::put($modelPath, $content);
    }
}
