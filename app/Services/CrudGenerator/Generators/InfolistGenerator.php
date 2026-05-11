<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Concerns\BuildsRelationships;
use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;
use Illuminate\Support\Str;

class InfolistGenerator implements GeneratorInterface
{
    use BuildsRelationships;

    private const HTML_TYPE_IMPORT_MAP = [
        'toggle' => 'use Filament\Infolists\Components\IconEntry;',
        'file' => 'use Filament\Infolists\Components\ImageEntry;',
        'color' => 'use Filament\Infolists\Components\ColorEntry;',
    ];

    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        $modelName = $config['model_name'];
        $pluralName = $config['plural_name'];
        $fields = $config['fields'] ?? [];
        $relationships = $config['relationships'] ?? [];

        $entries = $this->buildEntries($fields, $relationships, $modelName);
        $imports = $this->buildImports($fields);

        return $this->stubRenderer->load('infolist.stub')->replace([
            'pluralName' => $pluralName,
            'modelName' => $modelName,
            'entries' => implode(",\n                ", $entries),
            'imports' => $imports,
        ]);
    }

    private function buildEntries(array $fields, array $relationships, string $modelName): array
    {
        $entries = [];

        foreach ($fields as $field) {
            $entry = $this->buildFieldEntry($field, $modelName);
            if ($entry) {
                $entries[] = $entry;
            }
        }

        foreach ($relationships as $rel) {
            $entry = $this->buildRelationshipEntry($rel);
            if ($entry) {
                $entries[] = $entry;
            }
        }

        $entries[] = "TextEntry::make('created_at')->label(__('Created At'))->dateTime()->placeholder('-')->columnSpan(1)";
        $entries[] = "TextEntry::make('updated_at')->label(__('Updated At'))->dateTime()->placeholder('-')->columnSpan(1)";

        return $entries;
    }

    private function buildFieldEntry(array $field, string $modelName): ?string
    {
        $name = Str::snake($field['name']);
        $label = Str::title(str_replace('_', ' ', $name));
        $labelKey = "{$modelName}.{$label}";
        $span = $field['column_span'] ?? 1;
        $htmlType = $field['html_type'] ?? 'text';

        return match ($htmlType) {
            'toggle' => "IconEntry::make('{$name}')->label(__('{$labelKey}'))->boolean()->columnSpan({$span})",
            'file' => "ImageEntry::make('{$name}')->label(__('{$labelKey}'))->height(80)->columnSpan({$span})",
            'date' => "TextEntry::make('{$name}')->label(__('{$labelKey}'))->date()->placeholder('-')->columnSpan({$span})",
            'datetime' => "TextEntry::make('{$name}')->label(__('{$labelKey}'))->dateTime()->placeholder('-')->columnSpan({$span})",
            'time' => "TextEntry::make('{$name}')->label(__('{$labelKey}'))->time()->placeholder('-')->columnSpan({$span})",
            'color' => "ColorEntry::make('{$name}')->label(__('{$labelKey}'))->columnSpan({$span})",
            'textarea' => "TextEntry::make('{$name}')->label(__('{$labelKey}'))->placeholder('-')->columnSpanFull()",
            default => "TextEntry::make('{$name}')->label(__('{$labelKey}'))->placeholder('-')->columnSpan({$span})",
        };
    }

    private function buildRelationshipEntry(array $rel): ?string
    {
        $type = $rel['type'] ?? $rel['rel_type'] ?? 'belongsTo';
        if (! in_array($type, ['belongsTo', 'hasOne'])) {
            return null;
        }

        $relModel = $rel['related_model'];
        $displayField = $rel['display_column'] ?? 'name';
        $span = $rel['column_span'] ?? 1;

        $accessor = ! empty($rel['foreign_key']) && str_ends_with($rel['foreign_key'], '_id')
            ? Str::camel(Str::beforeLast($rel['foreign_key'], '_id'))
            : Str::camel($relModel);

        return "TextEntry::make('{$accessor}.{$displayField}')->label(__('{$relModel}'))->placeholder('-')->columnSpan({$span})";
    }

    private function buildImports(array $fields): string
    {
        $imports = ['use Filament\Infolists\Components\TextEntry;'];

        foreach ($fields as $field) {
            $htmlType = $field['html_type'] ?? 'text';
            if (isset(self::HTML_TYPE_IMPORT_MAP[$htmlType])) {
                $imports[] = self::HTML_TYPE_IMPORT_MAP[$htmlType];
            }
        }

        return implode("\n", array_unique($imports));
    }
}
