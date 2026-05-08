<?php

namespace App\Services\CrudGenerator\Services;

use App\Models\Generator;

class ModelUsageChecker
{
    public function check(string $modelName): array
    {
        $usedIn = [];

        foreach (Generator::all() as $gen) {
            if ($gen->model_name === $modelName) continue;

            $rels = array_filter($gen->fields ?? [], fn ($f) => ($f['field_type'] ?? 'field') === 'relationship');
            foreach ($rels as $rel) {
                if (($rel['related_model'] ?? '') === $modelName) {
                    $usedIn[] = $gen->model_name;
                    break;
                }
            }
        }

        return ['inUse' => ! empty($usedIn), 'usedIn' => $usedIn];
    }
}
