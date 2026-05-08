<?php

namespace App\Services;

use App\Models\Generator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Services\CrudGenerator\Builders\AlterMigrationBuilder;
use App\Services\CrudGenerator\Generators\ModelGenerator;
use App\Services\CrudGenerator\Generators\PermissionGenerator;
use App\Services\CrudGenerator\Services\MigrationService;
use App\Services\CrudGenerator\Services\ModelRelationshipSyncer;
use App\Services\CrudGenerator\Services\ModelUsageChecker;
use App\Services\CrudGenerator\Services\ResourceCustomizer;
use App\Services\CrudGenerator\Services\TranslationService;
use App\Services\CrudGenerator\Support\FieldResolver;
use App\Services\CrudGenerator\Support\StubRenderer;
use App\Services\CrudGenerator\FileSystem\FileManager;

class CrudGeneratorService
{
    private FieldResolver          $fieldResolver;
    private MigrationService       $migrationService;
    private ResourceCustomizer     $resourceCustomizer;
    private ModelRelationshipSyncer $relationshipSyncer;
    private ModelUsageChecker      $usageChecker;

    public function __construct()
    {
        $this->fieldResolver         = new FieldResolver();
        $alterBuilder                = new AlterMigrationBuilder($this->fieldResolver);
        $this->migrationService      = new MigrationService($alterBuilder);
        $this->resourceCustomizer    = new ResourceCustomizer($this->fieldResolver);
        $this->relationshipSyncer    = new ModelRelationshipSyncer($this->fieldResolver);
        $this->usageChecker          = new ModelUsageChecker();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Generate
    // ─────────────────────────────────────────────────────────────────────────

    public function generate(Generator $generator, array $previousFields = [], array $previousRelationships = []): bool
    {
        try {
            if (empty($generator->table_columns) || ! is_array($generator->table_columns)) {
                throw ValidationException::withMessages([
                    'table_columns' => __('Table Configuration is required before generation.'),
                ]);
            }

            $modelName = $generator->model_name;
            $tableName = Str::snake(Str::plural($modelName));

            $split     = $this->fieldResolver->splitFields($generator->fields ?? []);
            $fields    = $split['fields'];
            $generator->setRelationshipsForGeneration($split['relationships']);

            $prevSplit         = $this->fieldResolver->splitFields($previousFields);
            $prevRelationships = ! empty($previousRelationships) ? $previousRelationships : $prevSplit['relationships'];

            $this->writeModel($modelName, $fields, $generator);
            $this->migrationService->generate(
                $tableName, $fields, $generator, $prevSplit['fields'], $prevRelationships,
                fn () => $this->resourceCustomizer->regenerateForm($modelName, $fields, $generator, $this->pluralName($modelName))
            );
            $this->generateFilamentResource($modelName, $fields, $generator);

            $this->relationshipSyncer->sync($modelName, $generator);
            $this->assignPermissions($modelName);
            (new TranslationService())->generateLocalizationKeys($modelName, $fields);

            $generator->update([
                'status'          => 'generated',
                'generated_files' => $this->getGeneratedFiles($modelName),
            ]);

            return true;
        } catch (\Exception $e) {
            $generator->update(['status' => 'failed']);
            throw $e;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Cleanup
    // ─────────────────────────────────────────────────────────────────────────

    public function cleanup(Generator $generator): bool
    {
        $modelName  = $generator->model_name;
        $usageCheck = $this->usageChecker->check($modelName);

        if ($usageCheck['inUse']) {
            throw new \Exception(__("Your {$modelName} is used in another module. You cannot delete this."));
        }

        $tableName  = Str::snake(Str::plural($modelName));
        $modelWords = str_replace('_', ' ', $tableName);
        $pluralName = $this->pluralName($modelName);

        (new PermissionGenerator())->delete($modelWords);

        $this->safeFileDelete(app_path("Models/{$modelName}.php"));
        $this->safeDirectoryDelete(app_path("Filament/Resources/{$pluralName}"));
        $this->safeFileDelete(app_path("Filament/Resources/{$modelName}Resource.php"));

        if (Schema::hasTable($tableName)) Schema::dropIfExists($tableName);

        foreach (array_merge(
            File::glob(database_path('migrations/*_create_' . $tableName . '_table.php')),
            File::glob(database_path('migrations/*_add_columns_to_' . $tableName . '_table.php'))
        ) as $file) {
            $this->safeFileDelete($file);
        }

        $this->safeFileDelete(database_path("seeders/{$modelName}Seeder.php"));

        $split = $this->fieldResolver->splitFields($generator->fields ?? []);
        (new TranslationService())->removeLocalizationKeys($modelName, $split['fields']);

        return true;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public helpers
    // ─────────────────────────────────────────────────────────────────────────

    public function regenerateFormWithUpdatedConfig(Generator $generator): void
    {
        $modelName  = $generator->model_name;
        $pluralName = $this->pluralName($modelName);
        $formPath   = app_path("Filament/Resources/{$pluralName}/Schemas/{$modelName}Form.php");

        if (! File::exists($formPath)) return;

        $split = $this->fieldResolver->splitFields($generator->fields ?? []);
        $generator->setRelationshipsForGeneration($split['relationships']);
        $this->resourceCustomizer->customizeResource($modelName, $split['fields'], $generator, $pluralName);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private
    // ─────────────────────────────────────────────────────────────────────────

    private function writeModel(string $modelName, array $fields, Generator $generator): void
    {
        $path = app_path("Models/{$modelName}.php");
        app(FileManager::class)->ensureWritable($path);
        File::put($path, (new ModelGenerator(new StubRenderer(), $this->fieldResolver))->generate([
            'model_name'    => $modelName,
            'fields'        => $fields,
            'relationships' => $generator->relationships ?? [],
            'soft_deletes'  => $generator->soft_deletes ?? false,
        ]));
    }

    private function generateFilamentResource(string $modelName, array $fields, Generator $generator): void
    {
        Artisan::call('make:filament-resource', ['model' => $modelName, '--generate' => false]);

        $pluralName = $this->pluralName($modelName);
        $this->resourceCustomizer->customizeResource($modelName, $fields, $generator, $pluralName);
        $this->resourceCustomizer->customizePages($modelName, $generator);
        $this->resourceCustomizer->customizeTableSchema($modelName, $fields, $generator);
    }

    private function assignPermissions(string $modelName): void
    {
        $modelWords  = str_replace('_', ' ', Str::snake(Str::plural($modelName)));
        $pg          = new PermissionGenerator();
        $pg->assignToCurrentUser($pg->generate($modelWords));
    }

    private function pluralName(string $modelName): string
    {
        return config('crud-generator.plural_names', [])[$modelName] ?? Str::plural($modelName);
    }

    private function getGeneratedFiles(string $modelName): array
    {
        $pluralName = $this->pluralName($modelName);
        $tableName  = Str::snake(Str::plural($modelName));

        return [
            'model'     => "app/Models/{$modelName}.php",
            'resource'  => "app/Filament/Resources/{$pluralName}/{$modelName}Resource.php",
            'pages'     => [
                "app/Filament/Resources/{$pluralName}/Pages/Create{$modelName}.php",
                "app/Filament/Resources/{$pluralName}/Pages/Edit{$modelName}.php",
                "app/Filament/Resources/{$pluralName}/Pages/List{$pluralName}.php",
            ],
            'schemas'   => [
                "app/Filament/Resources/{$pluralName}/Schemas/{$modelName}Form.php",
                "app/Filament/Resources/{$pluralName}/Schemas/{$modelName}Infolist.php",
            ],
            'table'     => "app/Filament/Resources/{$pluralName}/Tables/{$pluralName}Table.php",
            'migration' => "database/migrations/*_create_{$tableName}_table.php",
        ];
    }

    private function safeFileDelete(string $filePath): bool
    {
        if (! File::exists($filePath)) {
            return true;
        }

        return File::delete($filePath);
    }

    private function safeDirectoryDelete(string $dirPath): bool
    {
        if (! File::exists($dirPath)) {
            return true;
        }

        return File::deleteDirectory($dirPath);
    }
}
