<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;

class ResourcePageGenerator implements GeneratorInterface
{
    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        $pageType = $config['page_type'];
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
            $headerActions = $this->stubRenderer->load('resource-page-list-header-actions.stub')->replace([
                'modelWords' => $modelWords,
                'modelName' => $modelName,
            ]);
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
