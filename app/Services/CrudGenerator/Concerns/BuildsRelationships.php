<?php

namespace App\Services\CrudGenerator\Concerns;

use Illuminate\Support\Str;

trait BuildsRelationships
{
    public function buildRelationshipMethod(array $rel): string
    {
        $name = $this->resolveRelationshipAccessor($rel);
        $type = $rel['type'] ?? $rel['rel_type'] ?? 'belongsTo';
        $model = $rel['related_model'] ?? '';

        $body = match ($type) {
            'belongsTo' => "return \$this->belongsTo({$model}::class);",
            'hasMany' => "return \$this->hasMany({$model}::class);",
            'hasOne' => "return \$this->hasOne({$model}::class);",
            'belongsToMany' => "return \$this->belongsToMany({$model}::class);",
            default => "return \$this->belongsTo({$model}::class);",
        };

        return "\n\n    public function {$name}()\n    {\n        {$body}\n    }";
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
        if (! empty($relationship['foreign_key'])) {
            return $relationship['foreign_key'];
        }

        if (! empty($relationship['related_model'])) {
            return Str::snake($relationship['related_model']) . '_id';
        }

        return Str::snake($relationship['name'] ?? 'relation') . '_id';
    }
}
