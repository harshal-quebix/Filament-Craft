<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Str;

class ResourcePageGenerator implements GeneratorInterface
{
    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        $pageType = $config['page_type']; // 'create', 'edit', 'list'
        $modelName = $config['model_name'];
        $pluralName = $config['plural_name'];
        $modelWords = $config['model_words'] ?? '';
        $softDeletes = $config['soft_deletes'] ?? false;

        return match ($pageType) {
            'create' => $this->generateCreatePage($modelName, $pluralName),
            'edit' => $this->generateEditPage($modelName, $pluralName),
            'list' => $this->generateListPage($modelName, $pluralName, $modelWords, $softDeletes),
            default => '',
        };
    }

    private function generateCreatePage(string $modelName, string $pluralName): string
    {
        return $this->stubRenderer->load('resource-page-create.stub')->replace([
            'modelName' => $modelName,
            'pluralName' => $pluralName,
        ]);
    }

    private function generateEditPage(string $modelName, string $pluralName): string
    {
        return $this->stubRenderer->load('resource-page-edit.stub')->replace([
            'modelName' => $modelName,
            'pluralName' => $pluralName,
        ]);
    }

    private function generateListPage(string $modelName, string $pluralName, string $modelWords, bool $softDeletes): string
    {
        if ($softDeletes) {
            $useStatements = 'use App\Filament\Resources\Concerns\HasTrashToggleAction;';
            $traitUse = '    use HasTrashToggleAction;' . "\n";
            $headerActions = '';
        } else {
            $useStatements = 'use Filament\Actions\CreateAction;';
            $traitUse = '';
            $headerActions = <<<PHP

    protected function getHeaderActions(): array
    {
        \$actions = [];

        if (auth()->user()->can('create {$modelWords}')) {
            \$actions[] = CreateAction::make();
        }

        return \$actions;
    }
PHP;
        }

        return $this->stubRenderer->load('resource-page-list.stub')->replace([
            'modelName' => $modelName,
            'pluralName' => $pluralName,
            'useStatements' => $useStatements,
            'traitUse' => $traitUse,
            'headerActions' => $headerActions,
        ]);
    }
}
