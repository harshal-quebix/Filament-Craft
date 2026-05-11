<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;

class TableSchemaGenerator implements GeneratorInterface
{
    private const HTML_TYPE_IMPORT_MAP = [
        'toggle' => 'use Filament\Tables\Columns\IconColumn;',
        'file' => 'use Filament\Tables\Columns\ImageColumn;',
    ];

    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        $pluralName = $config['plural_name'];
        $modelName = $config['model_name'];
        $modelWords = $config['model_words'];
        $columns = $config['columns'];
        $fields = $config['fields'] ?? [];
        $customTableColumns = $config['custom_table_columns'] ?? [];
        $softDeletes = $config['soft_deletes'] ?? false;

        $tableColumnsString = implode(",\n                ", $columns);
        $importsString = $this->buildImports(array_merge($fields, $customTableColumns));

        $softDeleteImports = $softDeletes
            ? "\nuse Filament\Actions\ForceDeleteBulkAction;\nuse Filament\Actions\RestoreBulkAction;\nuse Filament\Actions\RestoreAction;\nuse Filament\Actions\ForceDeleteAction;\nuse Filament\Tables\Filters\TrashedFilter;"
            : '';

        $softDeleteFilters = $softDeletes ? "\n                TrashedFilter::make()," : '';
        $softDeleteBulkActions = $softDeletes ? ",\n                    ForceDeleteBulkAction::make(),\n                    RestoreBulkAction::make()" : '';
        $softDeleteRecordActions = $softDeletes
            ? "\n                RestoreAction::make()\n                    ->label(false)\n                    ->tooltip(__('Restore'))\n                    ->icon('heroicon-o-arrow-uturn-left')\n                    ->button()\n                    ->size(Size::Small)\n                    ->color('success')\n                    ->visible(fn (\$record) => method_exists(\$record, 'trashed') && \$record->trashed()),\n                ForceDeleteAction::make()\n                    ->label(false)\n                    ->tooltip(__('Force Delete'))\n                    ->icon('heroicon-o-trash')\n                    ->button()\n                    ->size(Size::Small)\n                    ->color('danger')\n                    ->visible(fn (\$record) => method_exists(\$record, 'trashed') && \$record->trashed()),"
            : '';

        return $this->stubRenderer->load('table-schema.stub')->replace([
            'pluralName' => $pluralName,
            'modelName' => $modelName,
            'modelWords' => $modelWords,
            'columns' => $tableColumnsString,
            'imports' => $importsString,
            'softDeleteImports' => $softDeleteImports,
            'softDeleteFilters' => $softDeleteFilters,
            'softDeleteBulkActions' => $softDeleteBulkActions,
            'softDeleteRecordActions' => $softDeleteRecordActions,
        ]);
    }

    public function buildColumn(string $colName, string $label, string $htmlType, string $searchable, string $sortable): string
    {
        return match ($htmlType) {
            'toggle' => "IconColumn::make('{$colName}')->label(__('{$label}'))->boolean()->toggleable(){$searchable}{$sortable}",
            'date' => "TextColumn::make('{$colName}')->label(__('{$label}'))->date(Helper::getDateFormat())->timezone(Helper::getTimezone())->toggleable(){$searchable}{$sortable}",
            'datetime' => "TextColumn::make('{$colName}')->label(__('{$label}'))->dateTime(Helper::getDateTimeFormat())->timezone(Helper::getTimezone())->toggleable(){$searchable}{$sortable}",
            'time' => "TextColumn::make('{$colName}')->label(__('{$label}'))->time(Helper::getTimeFormat())->timezone(Helper::getTimezone())->toggleable(){$searchable}{$sortable}",
            'file' => "ImageColumn::make('{$colName}')->label(__('{$label}'))->size(50)->toggleable()->getStateUsing(fn (\$record) => getImageUrl(\$record->{$colName}))",
            'textarea' => "TextColumn::make('{$colName}')->label(__('{$label}'))->limit(50)->toggleable(){$searchable}{$sortable}",
            default => "TextColumn::make('{$colName}')->label(__('{$label}'))->toggleable(){$searchable}{$sortable}",
        };
    }

    private function buildImports(array $columns): string
    {
        $imports = ['use Filament\Tables\Columns\TextColumn;', 'use App\Helpers\Helper;'];

        foreach ($columns as $col) {
            $htmlType = $col['html_type'] ?? '';
            if (isset(self::HTML_TYPE_IMPORT_MAP[$htmlType])) {
                $imports[] = self::HTML_TYPE_IMPORT_MAP[$htmlType];
            }
        }

        return implode("\n", array_unique($imports));
    }
}
