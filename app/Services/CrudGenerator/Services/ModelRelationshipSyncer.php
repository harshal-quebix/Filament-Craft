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

            if (empty($name)) {
                continue;
            }

            if (preg_match('/public function ' . preg_quote($name, '/') . '\s*\(/i', $content)) {
                continue;
            }

            $method = $this->buildRelationshipMethod($rel);
            $lastBrace = strrpos($content, '}');
            if ($lastBrace !== false) {
                $content = substr($content, 0, $lastBrace) . $method . "\n" . substr($content, $lastBrace);
            }
        }

        File::put($modelPath, $content);
    }
}
