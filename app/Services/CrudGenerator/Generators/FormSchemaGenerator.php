<?php

namespace App\Services\CrudGenerator\Generators;

use App\Services\CrudGenerator\Contracts\GeneratorInterface;
use App\Services\CrudGenerator\Support\StubRenderer;

class FormSchemaGenerator implements GeneratorInterface
{
    private const HTML_TYPE_IMPORT_MAP = [
        'textarea' => 'use Filament\Forms\Components\Textarea;',
        'toggle' => 'use Filament\Forms\Components\Toggle;',
        'color' => 'use Filament\Forms\Components\ColorPicker;',
        'tags' => 'use Filament\Forms\Components\TagsInput;',
        'select' => 'use Filament\Forms\Components\Select;',
        'multiselect' => 'use Filament\Forms\Components\Select;',
        'radio' => 'use Filament\Forms\Components\Radio;',
        'checkbox' => 'use Filament\Forms\Components\CheckboxList;',
        'date' => 'use Filament\Forms\Components\DatePicker;',
        'datetime' => 'use Filament\Forms\Components\DateTimePicker;',
        'time' => 'use Filament\Forms\Components\TimePicker;',
        'file' => 'use Filament\Forms\Components\FileUpload;',
    ];

    private const BASE_IMPORTS = [
        'use Filament\Forms\Components\TextInput;',
        'use Filament\Schemas\Components\Section;',
        'use App\Helpers\Helper;',
    ];

    public function __construct(private StubRenderer $stubRenderer)
    {
    }

    public function generate(array $config): string
    {
        $className = $config['class_name'];
        $pluralName = $config['plural_name'];
        $fields = $config['fields'];
        $imports = $config['imports'] ?? $this->buildImports($config['raw_fields'] ?? [], $config['has_relationships'] ?? false);
        $cardSize = $config['card_size'] ?? 6;
        $offsetSize = $config['offset_size'] ?? 4;

        $formFieldsString = implode(",\n                    ", $fields);

        return $this->stubRenderer->load('form-schema.stub')->replace([
            'pluralName' => $pluralName,
            'className' => $className,
            'fields' => $formFieldsString,
            'imports' => $imports,
            'cardSize' => $cardSize,
            'offsetSize' => $offsetSize,
        ]);
    }

    public function buildImports(array $fields, bool $hasRelationships = false): string
    {
        $imports = self::BASE_IMPORTS;

        if ($hasRelationships) {
            $imports[] = 'use Filament\Forms\Components\Select;';
        }

        $hasFile = false;
        foreach ($fields as $field) {
            if (($field['in_form'] ?? true) !== true) {
                continue;
            }

            $htmlType = $field['html_type'] ?? 'text';
            if (isset(self::HTML_TYPE_IMPORT_MAP[$htmlType])) {
                $imports[] = self::HTML_TYPE_IMPORT_MAP[$htmlType];
            }
            if ($htmlType === 'file') {
                $hasFile = true;
            }
        }

        if ($hasFile) {
            $imports[] = 'use Illuminate\Support\Str;';
        }

        return implode("\n", array_unique($imports));
    }
}
