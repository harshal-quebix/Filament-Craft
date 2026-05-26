<?php

namespace App\Services\CrudGenerator\Builders;

use Illuminate\Support\Str;

class FieldBuilder
{
    public function buildFormField(array $field, string $modelName): ?string
    {
        $fieldName = Str::snake($field['name']);
        $htmlType = $field['html_type'] ?? 'text';
        $label = Str::title(str_replace('_', ' ', $fieldName));
        $labelKey = "{$modelName}.{$label}";
        $autoGenerate = $field['auto_generate'] ?? false;
        $required = ($field['required'] ?? true) ? '->required()->extraInputAttributes([\'required\' => false])' : '';
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
        if (! empty($field['placeholder'])) {
            $placeholderCode = "->placeholder('" . addslashes($field['placeholder']) . "')";
        }

        $noteCode = '';
        if (! empty($field['note'])) {
            $noteCode = "->helperText('" . addslashes($field['note']) . "')";
        }

        return match ($htmlType) {
            'textarea' => $this->buildTextarea($fieldName, $labelKey, $field, $placeholderCode, $required, $unique, $maxLength, $defaultValue, $noteCode, $spanCode),
            'toggle' => "Toggle::make('$fieldName')->label(__('{$labelKey}'))$required$unique$defaultValue{$noteCode}$spanCode",
            'color' => "ColorPicker::make('$fieldName')->label(__('{$labelKey}'))$required$unique$defaultValue{$noteCode}$spanCode",
            'tags' => "TagsInput::make('$fieldName')->label(__('{$labelKey}')){$placeholderCode}$required$unique$defaultValue{$noteCode}$spanCode",
            'select' => $this->buildSelect($fieldName, $labelKey, $field, $options, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode),
            'multiselect' => $this->buildMultiselect($fieldName, $labelKey, $field, $options, $placeholderCode, $required, $unique, $defaultValue, $noteCode, $spanCode),
            'radio' => $this->buildRadio($fieldName, $labelKey, $field, $options, $required, $unique, $defaultValue, $noteCode, $spanCode),
            'checkbox' => $this->buildCheckbox($fieldName, $labelKey, $field, $options, $required, $unique, $defaultValue, $noteCode, $spanCode),
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

    public function buildOptionsArray(string|array|null $options, string $dbType = 'string'): string
    {
        if (empty($options)) {
            return '[]';
        }

        $list = is_array($options) ? $options : explode(',', $options);
        $list = array_filter(array_map('trim', $list));

        if (empty($list)) {
            return '[]';
        }

        $isInteger = in_array($dbType, [
            'tinyInteger', 'unsignedTinyInteger', 'smallInteger', 'unsignedSmallInteger',
            'mediumInteger', 'unsignedMediumInteger', 'integer', 'unsignedInteger',
            'bigInteger', 'unsignedBigInteger', 'year',
        ]);

        if ($isInteger) {
            $items = [];
            foreach (array_values($list) as $index => $value) {
                // If the option is already numeric, use it as the key (value is preserved).
                // Otherwise assign an auto-incrementing numeric key so the stored
                // value is a valid integer while the label remains the user's text.
                $key = is_numeric($value) ? $value : ($index + 1);
                $items[] = "'{$key}' => '{$value}'";
            }
            return '[' . implode(', ', $items) . ']';
        }

        return '[' . implode(', ', array_map(fn ($o) => "'{$o}' => '{$o}'", $list)) . ']';
    }

    private function buildTextarea(string $fieldName, string $labelKey, array $field, string $placeholderCode, string $required, string $unique, string $maxLength, string $defaultValue, string $noteCode, string $spanCode): string
    {
        $autoMax = ($field['type'] === 'string' && (! isset($field['max_length']) || $field['max_length'] === null || $field['max_length'] === ''))
            ? '->maxLength(255)'
            : $maxLength;

        // Add DB-type validation for textarea (e.g., json fields using textarea input)
        $dbValidation = $this->getNumericValidationForDbType($field['type'] ?? '');

        return "Textarea::make('$fieldName')->label(__('{$labelKey}')){$dbValidation}{$placeholderCode}$required$unique$autoMax$defaultValue{$noteCode}$spanCode";
    }

    private function buildSelect(string $fieldName, string $labelKey, array $field, string|array|null $options, string $placeholderCode, string $required, string $unique, string $defaultValue, string $noteCode, string $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options, $field['type'] ?? 'string');
        $dbValidation = $this->getChoiceValidationForDbType($field['type'] ?? '');
        return "Select::make('$fieldName')->label(__('{$labelKey}'))->options($optArr){$dbValidation}{$placeholderCode}$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildMultiselect(string $fieldName, string $labelKey, array $field, string|array|null $options, string $placeholderCode, string $required, string $unique, string $defaultValue, string $noteCode, string $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options, $field['type'] ?? 'string');
        $dbValidation = $this->getChoiceValidationForDbType($field['type'] ?? '', true);
        return "Select::make('$fieldName')->label(__('{$labelKey}'))->multiple()->options($optArr){$dbValidation}{$placeholderCode}$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildRadio(string $fieldName, string $labelKey, array $field, string|array|null $options, string $required, string $unique, string $defaultValue, string $noteCode, string $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options, $field['type'] ?? 'string');
        $dbValidation = $this->getChoiceValidationForDbType($field['type'] ?? '');
        return "Radio::make('$fieldName')->label(__('{$labelKey}'))->options($optArr){$dbValidation}{$required}{$unique}->inline()$defaultValue{$noteCode}$spanCode";
    }

    private function buildCheckbox(string $fieldName, string $labelKey, array $field, string|array|null $options, string $required, string $unique, string $defaultValue, string $noteCode, string $spanCode): string
    {
        $optArr = $this->buildOptionsArray($options, $field['type'] ?? 'string');
        $dbValidation = $this->getChoiceValidationForDbType($field['type'] ?? '', true);
        return "CheckboxList::make('$fieldName')->label(__('{$labelKey}'))->options($optArr){$dbValidation}$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildUrl(string $fieldName, string $labelKey, array $field, string $placeholderCode, string $required, string $unique, string $defaultValue, string $disabledOnEdit, string $noteCode, string $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->url()->suffixIcon('heroicon-m-globe-alt'){$placeholderCode}{$ml}$required$unique$defaultValue$disabledOnEdit{$noteCode}$spanCode";
    }

    private function buildFile(string $fieldName, string $labelKey, string $modelName, string $required, string $noteCode, string $spanCode): string
    {
        return "FileUpload::make('$fieldName')->label(__('{$labelKey}'))->disk('public')->directory(Str::snake('{$modelName}'))->image()$required{$noteCode}$spanCode";
    }

    private function buildEmail(string $fieldName, string $labelKey, array $field, string $placeholderCode, string $required, string $unique, string $defaultValue, string $disabledOnEdit, string $noteCode, string $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->email(){$placeholderCode}{$ml}$required$unique$defaultValue$disabledOnEdit{$noteCode}$spanCode";
    }

    private function buildNumber(string $fieldName, string $labelKey, array $field, string $placeholderCode, string $required, string $unique, string $defaultValue, string $disabledOnEdit, string $noteCode, string $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        $dbType = $field['type'] ?? '';
        $dbValidation = $this->getDbTypeValidation($dbType);
        $isInteger = in_array($dbType, ['tinyInteger', 'unsignedTinyInteger', 'smallInteger', 'unsignedSmallInteger', 'mediumInteger', 'unsignedMediumInteger', 'integer', 'unsignedInteger', 'bigInteger', 'unsignedBigInteger', 'year']);
        $inputType = $isInteger ? 'integer' : 'numeric';
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->{$inputType}(){$dbValidation}{$placeholderCode}{$ml}$required$unique$defaultValue$disabledOnEdit{$noteCode}$spanCode";
    }

    private function buildPassword(string $fieldName, string $labelKey, array $field, string $placeholderCode, string $required, string $unique, string $defaultValue, string $noteCode, string $spanCode): string
    {
        $ml = $this->explicitMaxLength($field);
        return "TextInput::make('$fieldName')->label(__('{$labelKey}'))->password(){$placeholderCode}{$ml}$required$unique$defaultValue{$noteCode}$spanCode";
    }

    private function buildTextInput(string $fieldName, string $labelKey, array $field, string $placeholderCode, string $required, string $unique, string $maxLength, string $defaultValue, string $disabledOnEdit, string $noteCode, string $spanCode): string
    {
        $dbType = $field['type'] ?? '';
        $dbMax = $this->getDbTypeMaxLength($dbType);
        $numericValidation = $this->getNumericValidationForDbType($dbType);
        return "TextInput::make('$fieldName')->label(__('{$labelKey}')){$numericValidation}{$placeholderCode}$required$unique$maxLength{$dbMax}$defaultValue$disabledOnEdit{$noteCode}$spanCode";
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
        return match ($type) {
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
        return match ($type) {
            'char' => '->maxLength(1)',
            'string' => '->maxLength(255)',
            default => '',
        };
    }

    /**
     * Returns validation rules based on DB column type.
     * This ensures that even when HTML input type is 'text', the form
     * validates data according to the underlying DB column constraints.
     */
    private function getNumericValidationForDbType(string $type): string
    {
        return match ($type) {
            // Signed integers with min/max range validation
            'tinyInteger' => '->integer()->minValue(-128)->maxValue(127)',
            'smallInteger' => '->integer()->minValue(-32768)->maxValue(32767)',
            'mediumInteger' => '->integer()->minValue(-8388608)->maxValue(8388607)',
            'integer' => '->integer()->minValue(-2147483648)->maxValue(2147483647)',

            // Unsigned integers (must reject negative values)
            'unsignedTinyInteger' => '->integer()->minValue(0)->maxValue(255)',
            'unsignedSmallInteger' => '->integer()->minValue(0)->maxValue(65535)',
            'unsignedMediumInteger' => '->integer()->minValue(0)->maxValue(16777215)',
            'unsignedInteger' => '->integer()->minValue(0)->maxValue(4294967295)',

            // Big integers (no max validation to avoid overflow issues)
            'bigInteger' => '->integer()',
            'unsignedBigInteger' => '->integer()->minValue(0)',

            // Floating point / decimal numbers
            'float', 'double', 'decimal' => '->numeric()',

            // Year type (MySQL year: 1901-2155, but allow 1900-2100 for safety)
            'year' => '->integer()->minValue(1900)->maxValue(2100)',

            // JSON must be valid JSON string
            'json', 'jsonb' => '->json()',

            // UUID must be valid UUID format
            'uuid' => '->uuid()',

            // IP Address must be valid IPv4 or IPv6
            'ipAddress' => '->ip()',

            // Boolean, enum, date, datetime, time, timestamp, binary, text, string, longText
            // These are handled by their respective Filament components or HTML input types
            default => '',
        };
    }

    /**
     * Returns validation rules for choice-based inputs (Select, Radio, CheckboxList).
     * These use ->rules([...]) instead of component-specific methods like ->integer()
     * because not all components support those methods directly.
     */
    private function getChoiceValidationForDbType(string $type, bool $isArray = false): string
    {
        $rules = match ($type) {
            'tinyInteger', 'smallInteger', 'mediumInteger', 'integer', 'bigInteger',
            'unsignedTinyInteger', 'unsignedSmallInteger', 'unsignedMediumInteger',
            'unsignedInteger', 'unsignedBigInteger' => ['integer'],
            'year' => ['integer', 'min:1900', 'max:2100'],
            'float', 'double', 'decimal' => ['numeric'],
            'json', 'jsonb' => ['json'],
            'uuid' => ['uuid'],
            'ipAddress' => ['ip'],
            default => [],
        };

        if (empty($rules)) {
            return '';
        }

        if ($isArray) {
            // For multiselect / checkbox, the value is an array.
            // If the DB type expects a scalar (e.g. integer), validation will fail
            // gracefully with a clear message instead of a raw SQL error.
            array_unshift($rules, 'array');
        }

        $rulesString = implode("', '", $rules);
        return "->rules(['{$rulesString}'])";
    }
}
