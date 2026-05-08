<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use App\Services\CrudGeneratorService;

class Generator extends Model
{
    protected $fillable = [
        'name',
        'model_name',
        'controller_name',
        'table_name',
        'primary_key',
        'primary_key_type',
        'timestamps',
        'soft_deletes',
        'default_card_size',
        'fields',
        'relationships',
        'query_conditions',
        'table_columns',
        'generate_migration',
        'generate_model',
        'generate_controller',
        'generate_request',
        'generate_factory',
        'generate_seeder',
        'generate_resource',
        'generate_views',
        'status',
        'generated_files'
    ];

    /**
     * Runtime-only override for relationships (used by CrudGeneratorService
     * after splitting the unified fields array — not persisted).
     */
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
        // Fall back to stored value (legacy or direct DB relationships column)
        return $value ? (is_array($value) ? $value : json_decode($value, true) ?? []) : [];
    }

    protected $casts = [
        'fields' => 'array',
        'relationships' => 'array',
        'query_conditions' => 'array',
        'table_columns' => 'array',
        'timestamps' => 'boolean',
        'soft_deletes' => 'boolean',
        'generate_migration' => 'boolean',
        'generate_model' => 'boolean',
        'generate_controller' => 'boolean',
        'generate_request' => 'boolean',
        'generate_factory' => 'boolean',
        'generate_seeder' => 'boolean',
        'generate_resource' => 'boolean',
        'generate_views' => 'boolean',
        'generated_files' => 'array'
    ];
    
    /**
     * Get the table_columns attribute with safety checks.
     * Ensures we always return a valid array, never integers or other types.
     */
    public function getTableColumnsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }
        
        $decoded = is_array($value) ? $value : json_decode($value, true);
        
        // If decoding fails or result is not an array, return empty array
        if (! is_array($decoded)) {
            return [];
        }
        
        // Filter out any non-array items (integers, strings, etc.) but preserve keys.
        // Filament Repeaters rely on stable item keys during drag-and-drop reorder.
        return array_filter($decoded, fn($item) => is_array($item));
    }

    /**
     * Get the fields attribute with safety checks.
     * Ensures we always return a valid array, never integers or other types.
     */
    public function getFieldsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode($value, true);

        // If decoding fails or result is not an array, return empty array
        if (! is_array($decoded)) {
            return [];
        }

        // Filter out any non-array items but preserve keys.
        // Filament Repeaters rely on stable item keys during drag-and-drop reorder.
        return array_filter($decoded, fn ($item) => is_array($item));
    }

    /**
     * Get the query_conditions attribute with safety checks.
     * Ensures we always return a valid array, never integers or other types.
     */
    public function getQueryConditionsAttribute($value): array
    {
        if (empty($value)) {
            return [];
        }

        $decoded = is_array($value) ? $value : json_decode($value, true);

        // If decoding fails or result is not an array, return empty array
        if (! is_array($decoded)) {
            return [];
        }

        // Filter out any non-array items (Filament repeaters expect array items) but preserve keys.
        return array_filter($decoded, fn ($item) => is_array($item));
    }
    
    protected static function booted()
    {
        static::deleting(function ($generator) {
            try {
                $generator->cleanupGeneratedFiles();
            } catch (\Exception $e) {
                // Prevent deletion and show error message
                throw new \Exception($e->getMessage());
            }
        });
    }
    
    public function cleanupGeneratedFiles()
    {
        // Reuse the generator cleanup logic used elsewhere (Edit/Delete action).
        // This also keeps type-safety consistent with the actual service API.
        (new CrudGeneratorService())->cleanup($this);
    }
}