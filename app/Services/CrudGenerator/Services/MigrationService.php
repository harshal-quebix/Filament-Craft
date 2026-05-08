<?php

namespace App\Services\CrudGenerator\Services;

use App\Models\Generator;
use App\Services\CrudGenerator\Builders\AlterMigrationBuilder;
use App\Services\CrudGenerator\Generators\MigrationGenerator;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MigrationService
{
    public function __construct(private AlterMigrationBuilder $alterMigrationBuilder)
    {
    }

    public function generate(string $tableName, array $fields, Generator $generator, array $previousFields = [], array $previousRelationships = [], ?callable $afterAlter = null): void
    {
        if (Schema::hasTable($tableName)) {
            $this->generateAlter($tableName, $fields, $generator, $previousFields, $previousRelationships, $afterAlter);
        } else {
            $this->generateCreate($tableName, $fields, $generator);
        }
    }

    private function generateCreate(string $tableName, array $fields, Generator $generator): void
    {
        Artisan::call('make:migration', ['name' => "create_{$tableName}_table"]);

        $files = File::glob(database_path('migrations/*_create_' . $tableName . '_table.php'));
        $file  = end($files);
        if (! $file) return;

        $content = (new MigrationGenerator(new StubRenderer()))->generate([
            'is_alter'         => false,
            'table_name'       => $tableName,
            'fields'           => $fields,
            'relationships'    => $generator->relationships ?? [],
            'soft_deletes'     => $generator->soft_deletes ?? false,
            'primary_key'      => $generator->primary_key ?? 'id',
            'primary_key_type' => $generator->primary_key_type ?? 'int',
        ]);

        $this->writeFile($file, $content);
        Artisan::call('migrate');
    }

    private function generateAlter(string $tableName, array $fields, Generator $generator, array $previousFields, array $previousRelationships, ?callable $afterAlter = null): void
    {
        Artisan::call('make:migration', ['name' => 'add_columns_to_' . $tableName . '_table']);

        $files = File::glob(database_path('migrations/*_add_columns_to_' . $tableName . '_table.php'));
        $file  = end($files);
        if (! $file) return;

        $ops = $this->alterMigrationBuilder->build($tableName, $fields, $generator, $previousFields, $previousRelationships);

        if (empty($ops['up'])) {
            File::delete($file);
            return;
        }

        $content = (new MigrationGenerator(new StubRenderer()))->generate([
            'is_alter'        => true,
            'table_name'      => $tableName,
            'operations'      => $ops['up'],
            'down_operations' => $ops['down'],
        ]);

        $this->writeFile($file, $content);
        Artisan::call('migrate');

        if ($afterAlter) ($afterAlter)();
    }

    private function writeFile(string $path, string $content): void
    {
        $dir = dirname($path);
        if (! File::exists($dir)) File::makeDirectory($dir, 0755, true);
        File::put($path, $content);
    }
}
