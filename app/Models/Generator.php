<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Generator extends Model
{
    protected $fillable = [
        'name',
        'model_name',
        'primary_key',
        'primary_key_type',
        'timestamps',
        'soft_deletes',
        'default_card_size',
        'fields',
        'relationships',
        'query_conditions',
        'table_columns',
        'status',
        'generated_files',
    ];

    protected ?array $runtimeRelationships = null;

    public function setRelationshipsForGeneration(array $relationships): void
    {
        $this->runtimeRelationships = $relationships;
    }

    public function getRelationshipsAttribute($value): array
    {
        if ($this->runtimeRelationships !== null) {
            return $this->runtimeRelationships;
        }

        return $value ? (is_array($value) ? $value : json_decode($value, true) ?? []) : [];
    }

    protected $casts = [
        'fields' => 'array',
        'relationships' => 'array',
        'query_conditions' => 'array',
        'table_columns' => 'array',
        'timestamps' => 'boolean',
        'soft_deletes' => 'boolean',
        'generated_files' => 'array',
    ];

    public function getTableColumnsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_filter($decoded, fn ($item) => is_array($item));
    }

    public function getFieldsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_filter($decoded, fn ($item) => is_array($item));
    }

    public function getQueryConditionsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode($value, true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_filter($decoded, fn ($item) => is_array($item));
    }

    protected static function booted(): void
    {
        static::deleting(function ($generator) {
            try {
                $generator->cleanupGeneratedFiles();
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }
        });
    }

    public function cleanupGeneratedFiles(): void
    {
        app(\App\Services\CrudGeneratorService::class)->cleanup($this);
    }
}
