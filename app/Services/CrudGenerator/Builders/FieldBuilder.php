<?php

namespace App\Services\CrudGenerator\Builders;

use Illuminate\Support\Str;

class FieldBuilder
{
    public function buildFillable(array $fields): array
    {
        return collect($fields)->map(fn($f) => Str::snake($f['name']))->toArray();
    }

    public function buildCasts(array $fields): array
    {
        $casts = [];
        
        foreach ($fields as $field) {
            $name = Str::snake($field['name']);
            $type = $field['type'];
            $htmlType = $field['html_type'] ?? '';

            if ($type === 'boolean') {
                $casts[$name] = 'boolean';
            } elseif (in_array($type, ['json', 'jsonb'])) {
                $casts[$name] = 'array';
            } elseif (in_array($type, ['date', 'dateTime', 'timestamp'])) {
                $casts[$name] = 'datetime';
            } elseif (in_array($htmlType, ['tags', 'checkbox', 'multiselect'])) {
                $casts[$name] = 'array';
            }
        }

        return $casts;
    }

    public function buildFormField(array $field, string $modelName): ?string
    {
        $fieldName = Str::snake($field['name']);
        $htmlType = $field['html_type'] ?? 'text';
        $label = Str::title(str_replace('_', ' ', $fieldName));
        $labelKey = "{$modelName}.{$label}";
        $autoGenerate = $field['auto_generate'] ?? false;
        $required = ($field['required'] ?? true) ? '->required()' : '';
        $unique = ($field['unique'] ?? false) ? '->unique()' : '';
        $defaultValue = $autoGenerate ? "->default(fn () => \\Illuminate\\Support\\Str::random(10))" : '';
        $disabledOnEdit = $autoGenerate ? "->disabled(fn (string \$operation): bool => \$operation === 'edit')" : '';
        $columnSpan = $field['column_span'] ?? 6;
        $spanCode = "->columnSpan({$columnSpan})";
        $options = $field['options'] ?? '';

        $maxLength = '';
        if (in_array($field['type'] ?? '', ['string', 'text']) && isset($field['max_length']) && $field['max_length'] !== null && $field['max_length'] !== '') {
            $maxLength = "->maxLength({$field['max_length']})";
        }

        $placeholderCode = '';
        if (!empty($field['placeholder'])) {
            $placeholderCode = "->placeholder('" . addslashes($field['placeholder']) . "')";
        }

        $noteCode = '';
        if (!empty($field['note'])) {
            $noteCode = "->helperText('" . addslashes($field['note']) . "')";
        }

        return match($htmlType) {
            'textarea' => $this->buildTextarea($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $maxLength, $defaultValue, $noteCode, $spanCode),
            'toggle' => "Toggle::make('$fieldName')->label(__('{$labelKey}'))$required$unique$defaultValue{$noteCode}$spanCode",
            'color' => "ColorPicker::make('$fieldName')->label(__('{$labelKey}'))$required$unique$defaultValue{$noteCode}$spanCode",
            'tags' => "TagsInput::make('$fieldName')->label(__('{$labelKey}')){$placeholderCode}$required$unique$defaultValue{$noteCode}$spanCode",
            'select' => $this->buildSelect($fieldName, $labelKey, $options, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode),
            'multiselect' => $this->buildMultiselect($fieldName, $labelKey, $options, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode),
            'radio' => $this->buildRadio($fieldName, $labelKey, $options, $required, $unique, $defaultValue, $noteCode, $spanCode),
            'checkbox' => $this->buildCheckbox($fieldName, $labelKey, $options, $required, $unique, $defaultValue, $noteCode, $spanCode),
            'url' => $this->buildUrl($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $disabledOnEdit, $noteCode, $spanCode),
            'file' => $this->buildFile($fieldName, $labelKey, $modelName, $required, $noteCode, $spanCode),
            'email' => $this->buildEmail($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $disabledOnEdit, $noteCode, $spanCode),
            'number' => $this->buildNumber($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $disabledOnEdit, $noteCode, $spanCode),
            'date' => "DatePicker::make('$fieldName')->label(__('{$labelKey}'))->displayFormat(Helper::getDateFormat())->timezone(Helper::getTimezone())$required$unique$defaultValue{$noteCode}$spanCode",
            'datetime' => "DateTimePicker::make('$fieldName')->label(__('{$labelKey}'))->displayFormat(Helper::getDateTimeFormat())->timezone(Helper::getTimezone())$required$unique$defaultValue{$noteCode}$spanCode",
            'time' => "TimePicker::make('$fieldName')->label(__('{$labelKey}'))->displayFormat(Helper::getTimeFormat())->timezone(Helper::getTimezone())$required$unique$defaultValue{$noteCode}$spanCode",
            'password' => $this->buildPassword($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode),
            default => $this->buildTextInput($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $maxLength, $defaultValue, $disabledOnEdit, $noteCode, $spanCode),
        };
    }

    private function buildTextarea($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $maxLength, $defaultValue, $noteCode, $spanCode): string
    {
        $autoMax = ($field['type'] === 'string' && (!isset($field['max_length']) || $field['max_length'] === null || $field['max_length'] === '')) ? '->maxLength(255)' : $maxLength;
        return "Textarea::make('$fieldName')->label(__('{$labelKey}')){$placeholderCode}$required$unique$autoMax$defaultValue{$noteCode}$spanCode";
    }

    private function buildSelect($fieldName, $labelKey, $options, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options);
        return "Select::make('$fieldName')->label(__('{$labelKey}'))->options($optArr){$placeholderCode}$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildMultiselect($fieldName, $labelKey, $options, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options);
        return "Select::make('$fieldName')->label(__('{$labelKey}'))->multiple()->options($optArr){$placeholderCode}$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildRadio($fieldName, $labelKey, $options, $required, $unique, $defaultValue, $noteCode, $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options);
        return "Radio::make('$fieldName')->label(__('{$labelKey}'))->options($optArr){$required}{$unique}->inline()$defaultValue{$noteCode}$spanCode";
    }

    private function buildCheckbox($fieldName, $labelKey, $options, $required, $unique, $defaultValue, $noteCode, $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options);
        return "CheckboxList::make('$fieldName')->label(__('{$labelKey}'))->options($optArr)$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildUrl($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $disabledOnEdit, $noteCode, $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->url()->suffixIcon('heroicon-m-globe-alt'){$placeholderCode}{$ml}$required$unique$defaultValue$disabledOnEdit{$noteCode}$spanCode";
    }

    private function buildFile($fieldName, $labelKey, $modelName, $required, $noteCode, $spanCode): string
    {
        return "FileUpload::make('$fieldName')->label(__('{$labelKey}'))->disk('public')->directory(Str::snake('{$modelName}'))->image()$required{$noteCode}$spanCode";
    }

    private function buildEmail($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $disabledOnEdit, $noteCode, $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->email(){$placeholderCode}{$ml}$required$unique$defaultValue$disabledOnEdit{$noteCode}$spanCode";
    }

    private function buildNumber($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $disabledOnEdit, $noteCode, $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        $dbValidation = $this->getDbTypeValidation($field['type'] ?? '');
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->numeric(){$dbValidation}{$placeholderCode}{$ml}$required$unique$defaultValue$disabledOnEdit{$noteCode}$spanCode";
    }

    private function buildPassword($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->password(){$placeholderCode}{$ml}$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildTextInput($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $maxLength, $defaultValue, $disabledOnEdit, $noteCode, $spanCode): string
    {
        $dbMax = $this->getDbTypeMaxLength($field['type'] ?? '');
        return "TextInput::make('$fieldName')->label(__('{$labelKey}')){$placeholderCode}$required$unique$maxLength{$dbMax}$defaultValue$disabledOnEdit{$noteCode}$spanCode";
    }

    private function buildOptionsArray($options): string
    {
        if (empty($options)) return '[]';
        $list = is_array($options) ? $options : explode(',', $options);
        $list = array_filter(array_map('trim', $list));
        if (empty($list)) return '[]';
        return '[' . implode(', ', array_map(fn($o) => "'$o' => '$o'", $list)) . ']';
    }

    private function explicitMaxLength(array $field): string
    {
        if (isset($field['max_length']) && $field['max_length'] !== null && $field['max_length'] !== '') {
            return "->maxLength({$field['max_length']})";
        }
        return '';
    }

    private function getDbTypeValidation(string $type): string
    {
        return match($type) {
            'tinyInteger' => '->minValue(-128)->maxValue(127)',
            'unsignedTinyInteger' => '->minValue(0)->maxValue(255)',
            'smallInteger' => '->minValue(-32768)->maxValue(32767)',
            'unsignedSmallInteger' => '->minValue(0)->maxValue(65535)',
            'mediumInteger' => '->minValue(-8388608)->maxValue(8388607)',
            'unsignedMediumInteger' => '->minValue(0)->maxValue(16777215)',
            'integer' => '->minValue(-2147483648)->maxValue(2147483647)',
            'unsignedInteger' => '->minValue(0)->maxValue(4294967295)',
            'bigInteger' => '',
            'unsignedBigInteger' => '->minValue(0)',
            default => '',
        };
    }

    private function getDbTypeMaxLength(string $type): string
    {
        return match($type) {
            'char' => '->maxLength(1)',
            'string' => '->maxLength(255)',
            default => '',
        };
    }
}
