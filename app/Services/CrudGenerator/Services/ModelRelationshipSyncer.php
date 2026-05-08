<?php

namespace App\Services\CrudGenerator\Services;

use App\Models\Generator;
use App\Services\CrudGenerator\Support\FieldResolver;
use Illuminate\Support\Facades\File;

class ModelRelationshipSyncer
{
    public function __construct(private FieldResolver $fieldResolver)
    {
    }

    public function sync(string $modelName, Generator $generator): void
    {
        $modelPath = app_path("Models/{$modelName}.php");
        if (! File::exists($modelPath) || empty($generator->relationships)) return;

        $content = File::get($modelPath);

        foreach ($generator->relationships as $rel) {
            $name  = $this->fieldResolver->resolveRelationshipAccessor($rel);
            $model = $rel['related_model'];
            $type  = $rel['type'];

            if (preg_match('/public function ' . preg_quote($name, '/') . '\s*\(/i', $content)) continue;

            $body = match ($type) {
                'belongsTo'     => "return \$this->belongsTo({$model}::class);",
                'hasMany'       => "return \$this->hasMany({$model}::class);",
                'hasOne'        => "return \$this->hasOne({$model}::class);",
                'belongsToMany' => "return \$this->belongsToMany({$model}::class);",
                default         => "return \$this->belongsTo({$model}::class);",
            };

            $method  = "\n\n    public function {$name}()\n    {\n        {$body}\n    }";
            $content = preg_replace('/\n}\s*$/', $method . "\n}", $content);
        }

        File::put($modelPath, $content);
    }
}
