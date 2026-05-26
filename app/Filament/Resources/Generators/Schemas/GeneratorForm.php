<?php

namespace App\Filament\Resources\Generators\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use App\Filament\Forms\Components\SafeRepeater as Repeater;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TagsInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Str;
use App\Models\Generator;
use App\Services\CrudGeneratorService;
use Illuminate\Validation\Rules\Regex;

class GeneratorForm
{
    /**
     * System/internal models that should NEVER be overwritten by the CRUD generator.
     * These models are essential for the application core functionality.
     */
    private const PROTECTED_SYSTEM_MODELS = [
        'User',
        'Role',
        'Permission',
        'Setting',
        'CmsSetting',
        'Menu',
        'Generator',
        'ContactUs',
    ];

    /**
     * System/internal models that should NEVER appear in the Related Model dropdown.
     * Only generator-created CRUD modules should be selectable as relationships.
     */
    private const EXCLUDED_SYSTEM_MODELS = [
        'User',
        'Role',
        'Permission',
        'Setting',
        'CmsSetting',
        'Menu',
        'Generator',
        'ContactUs',
    ];

    /**
     * User-friendly descriptions for each relationship type.
     * Helps non-technical users understand what each relationship means.
     */
    private const RELATIONSHIP_TYPE_DESCRIPTIONS = [
        'belongsTo'     => 'One record belongs to another module (e.g., a Post belongs to one Category)',
        'hasMany'       => 'One record can have many related records (e.g., a Category has many Posts)',
        'hasOne'        => 'One record has exactly one related record (e.g., a User has one Profile)',
        'belongsToMany' => 'Both modules can have many related records (e.g., a Post has many Tags, and a Tag has many Posts)',
    ];

    /**
     * Valid database column naming pattern for primary keys and field names.
     * Must start with letter/underscore, contain only alphanumeric and underscores.
     */
    private const DB_COLUMN_PATTERN = '/^[a-zA-Z_][a-zA-Z0-9_]*$/';

    /**
     * Valid primary key naming pattern.
     * Must be 'id' or end with '_id' (e.g., post_id, user_id).
     * This ensures consistency and prevents random strings as primary keys.
     */
    private const PRIMARY_KEY_PATTERN = '/^(id|.*_id)$/';

    // ─────────────────────────────────────────────────────────────────────────
    // Shared configure modal form schema used for BOTH fields and relationships.
    // Relationship fields hide Auto Generate, Max Length, Placeholder, and Note.
    // ─────────────────────────────────────────────────────────────────────────
    private static function configureModalForm(): array
    {
        return [
            Hidden::make('html_type'),
            Hidden::make('field_name'),
            Hidden::make('is_relationship'), // flag: true = relationship, false/null = field

            Checkbox::make('required')
                ->label(__('Required'))
                ->default(true),

            Checkbox::make('unique')
                ->label(__('Unique'))
                ->default(false),

            Checkbox::make('searchable')
                ->label(__('Searchable'))
                ->default(true),

            Checkbox::make('in_form')
                ->label(__('Show in Form'))
                ->helperText(__('Include this field in create/edit forms'))
                ->default(true),

            Checkbox::make('in_table')
                ->label(__('Show in Table'))
                ->helperText(__('Include this field in the table listing'))
                ->default(true),

            Checkbox::make('sortable')
                ->label(__('Sortable'))
                ->helperText(__('Allow sorting by this column in the table'))
                ->default(true)
                ->visible(fn(Get $get) => (bool) $get('is_relationship')),

            // ── Field-only options ──────────────────────────────────────────
            Checkbox::make('auto_generate')
                ->label(__('Auto Generate'))
                ->helperText(__('Auto-generate a random value for this field'))
                ->default(false)
                ->visible(fn(Get $get) => ! (bool) $get('is_relationship')),

            TextInput::make('max_length')
                ->label(__('Max Length'))
                ->numeric()
                ->placeholder(__('255'))
                ->visible(fn(Get $get) => ! (bool) $get('is_relationship')),

            Checkbox::make('enable_placeholder')
                ->label(__('Enable Placeholder'))
                ->default(true)
                ->live()
                ->visible(fn(Get $get) => ! (bool) $get('is_relationship'))
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    if ($state && empty($get('placeholder'))) {
                        $set('placeholder', self::defaultPlaceholderText($get('field_name'), $get('html_type')));
                    }
                    if (! $state) {
                        $set('placeholder', null);
                    }
                }),

            TextInput::make('placeholder')
                ->label(__('Placeholder Text'))
                ->placeholder(__('e.g., Enter "Title"'))
                ->visible(fn(Get $get) => ! (bool) $get('is_relationship') && (bool) $get('enable_placeholder')),

            Checkbox::make('enable_note')
                ->label(__('Enable Note / Helper Text'))
                ->default(false)
                ->live()
                ->visible(fn(Get $get) => ! (bool) $get('is_relationship'))
                ->afterStateUpdated(function (Set $set, Get $get, $state) {
                    if ($state && empty($get('note'))) {
                        $set('note', self::humanizeFieldLabel($get('field_name')));
                    }
                    if (! $state) {
                        $set('note', null);
                    }
                }),

            TextInput::make('note')
                ->label(__('Note / Helper Text'))
                ->placeholder(__('e.g., Enter "Title"'))
                ->visible(fn(Get $get) => ! (bool) $get('is_relationship') && (bool) $get('enable_note')),
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Fill form data for the configure modal (shared for field & relationship).
    // ─────────────────────────────────────────────────────────────────────────
    private static function fillConfigureForm(Get $get, $livewire, bool $isRelationship): array
    {
        $fieldName      = $get('name');
        $htmlType       = $get('html_type');
        $generatorId    = $livewire->record->id ?? null;
        $placeholder    = $get('placeholder');
        $note           = $get('note');

        $defaults = [
            'is_relationship'    => $isRelationship,
            'html_type'          => $htmlType,
            'field_name'         => $fieldName,
            'required'           => $get('required')           ?? true,
            'unique'             => $get('unique')             ?? false,
            'searchable'         => $get('searchable')         ?? true,
            'in_form'            => $get('in_form')            ?? true,
            'in_table'           => $get('in_table')           ?? true,
            'sortable'           => $get('sortable')           ?? true,
            // field-only
            'auto_generate'      => $get('auto_generate')      ?? false,
            'max_length'         => $get('max_length')         ?? null,
            'placeholder'        => $placeholder ?: self::defaultPlaceholderText($fieldName, $htmlType),
            'note'               => $note,
            'enable_placeholder' => true,
            'enable_note'        => ! empty($note),
        ];

        if (! $generatorId || ! $fieldName) {
            return $defaults;
        }

        $generator   = \App\Models\Generator::find($generatorId);
        $savedType   = $isRelationship ? 'relationship' : 'field';

        foreach ($generator->fields ?? [] as $field) {
            if (($field['field_type'] ?? 'field') !== $savedType) continue;
            if ($field['name'] !== $fieldName) continue;

            return [
                'is_relationship'    => $isRelationship,
                'html_type'          => $field['html_type']     ?? $htmlType,
                'field_name'         => $fieldName,
                'required'           => $field['required']      ?? true,
                'unique'             => $field['unique']        ?? false,
                'searchable'         => $field['searchable']    ?? true,
                'in_form'            => $field['in_form']       ?? true,
                'in_table'           => $field['in_table']      ?? true,
                'sortable'           => $field['sortable']      ?? true,
                // field-only
                'auto_generate'      => $field['auto_generate'] ?? false,
                'max_length'         => $field['max_length']    ?? null,
                'placeholder'        => $field['placeholder']   ?? self::defaultPlaceholderText($fieldName, $field['html_type'] ?? $htmlType),
                'note'               => $field['note']          ?? null,
                'enable_placeholder' => true,
                'enable_note'        => ! empty($field['note']),
            ];
        }

        return $defaults;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Persist configure modal data back to the repeater item and DB record.
    // ─────────────────────────────────────────────────────────────────────────
    private static function saveConfigureForm(
        array  $data,
        Set    $set,
        Get    $get,
               $livewire,
        bool   $isRelationship,
        array  $skipKeys = ['html_type', 'field_name', 'is_relationship']
    ): void {
        foreach ($data as $key => $value) {
            if (! in_array($key, $skipKeys)) {
                $set($key, $value);
            }
        }

        $generatorId = $livewire->record->id ?? null;
        $fieldName   = $data['field_name'] ?? null;

        if (! $generatorId || ! $fieldName) return;

        $generator = \App\Models\Generator::find($generatorId);
        $fields    = $generator->fields ?? [];
        $savedType = $isRelationship ? 'relationship' : 'field';

        foreach ($fields as $index => $field) {
            if (($field['field_type'] ?? 'field') !== $savedType) continue;
            if ($field['name'] !== $fieldName) continue;

            foreach ($data as $key => $value) {
                if (! in_array($key, $skipKeys)) {
                    $fields[$index][$key] = $value;
                }
            }
            break;
        }

        $generator->update(['fields' => $fields]);

        if (! $isRelationship) {
            app(CrudGeneratorService::class)
                ->regenerateFormWithUpdatedConfig($generator);
        }
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Wizard::make([

                    // ─────────────────────────────────────────────────────────
                    // STEP 1 – Basic Information
                    // ─────────────────────────────────────────────────────────
                    Wizard\Step::make(__('Basic Information'))
                        ->icon('heroicon-m-cog-6-tooth')
                        ->schema([
                            TextInput::make('name')
                                ->label(__('Generator Name'))
                                ->markAsRequired()
                                ->rules(['required', 'string', 'max:255'])
                                ->placeholder(__('e.g., Blog Post Generator'))
                                ->unique('generators', 'name'),

                            TextInput::make('model_name')
                                ->label(__('Model Name'))
                                ->markAsRequired()
                                ->placeholder(__('e.g., BlogPost'))
                                ->helperText(__('CamelCase model class name (e.g., BlogPost, UserProfile). Protected names: User, Role, Permission, Setting, Menu, Generator.'))
                                ->rules([
                                    'required',
                                    'regex:/^[A-Z][a-zA-Z0-9]*$/',
                                    fn (): \Closure => function (string $attribute, $value, \Closure $fail) {
                                        if (in_array($value, self::PROTECTED_SYSTEM_MODELS, true)) {
                                            $fail(__(':name is a protected system model and cannot be used. Please choose a different name.', ['name' => $value]));
                                        }
                                    },
                                ])
                                ->validationMessages([
                                    'regex' => __('Model name must be CamelCase (e.g., BlogPost, UserProfile)'),
                                ])
                                ->readOnly(fn ($livewire) => $livewire instanceof \Filament\Resources\Pages\EditRecord)
                                ->unique('generators', 'model_name'),

                            TextInput::make('primary_key')
                                ->label(__('Primary Key'))
                                ->default('id')
                                ->placeholder(__('e.g., id, post_id, user_id'))
                                ->helperText(__('Database column name. Use only letters, numbers, and underscores. Must start with a letter or underscore. Must be "id" or end with "_id" (e.g., post_id, user_id).'))
                                ->required()
                                ->string()
                                ->maxLength(64)
                                ->regex(self::DB_COLUMN_PATTERN)
                                ->regex(self::PRIMARY_KEY_PATTERN)
                                ->validationMessages([
                                    'required' => __('Primary key is required.'),
                                    'regex'    => __('Primary key must be "id" or end with "_id" (e.g., id, post_id, user_id). Random strings are not allowed.'),
                                    'maxLength' => __('Primary key must not exceed 64 characters.'),
                                ])
                                ->live(onBlur: true),

                            Select::make('primary_key_type')
                                ->native(false)
                                ->selectablePlaceholder()
                                ->label(__('Primary Key Type'))
                                ->options([
                                    'int'    => __('Integer (Auto-incrementing number)'),
                                    'bigint' => __('Big Integer (For very large datasets)'),
                                    'uuid'   => __('UUID (Universally Unique Identifier)'),
                                ])
                                ->default('int')
                                ->markAsRequired()
                                ->rules(['required'])
                                ->validationMessages([
                                    'required' => __('Please select a primary key type. This is required for database creation.'),
                                ])
                                ->helperText(__('Choose the data type for your primary key column. This cannot be changed later without recreating the table.')),

                            Toggle::make('timestamps')
                                ->label(__('Timestamps'))
                                ->default(true),

                            Toggle::make('soft_deletes')
                                ->label(__('Soft Deletes'))
                                ->helperText(__('Enable soft deletes for this module (records are marked as deleted but not permanently removed)'))
                                ->default(false),
                        ])->columns(2),

                    // ─────────────────────────────────────────────────────────
                    // STEP 2 – Fields Configuration
                    // ─────────────────────────────────────────────────────────
                    Wizard\Step::make(__('Fields Configuration'))
                        ->icon('heroicon-m-document-text')
                        ->afterValidation(function ($livewire) {
                            $fields = $livewire->data['fields'] ?? [];

                            if (empty($fields) || ! is_array($fields)) {
                                $livewire->data['table_columns'] = [];
                                return;
                            }

                            $livewire->data['table_columns'] = self::keyRepeaterItems(
                                self::buildDefaultColumns(self::normalizeFieldRepeaterItems($fields)),
                            );
                        })
                        ->schema([
                            Select::make('default_card_size')
                                ->native(false)
                                ->selectablePlaceholder()
                                ->label(__('Default Card Size'))
                                ->options(self::columnSpanOptions())
                                ->default('6')
                                ->columnSpanFull()
                                ->markAsRequired()
                                ->rules(['required'])
                                ->validationMessages([
                                    'required' => __('Please select a default card size.'),
                                ])
                                ->helperText(__('Default card width for all fields (can be overridden per field)')),

                            Repeater::make('fields')
                                ->label(__('Fields & Relationships'))
                                ->markAsRequired()
                                ->rules(['required', 'array', 'min:1'])
                                ->validationMessages([
                                    'required' => __('At least one field or relationship must be configured before proceeding.'),
                                    'min'      => __('At least one field or relationship must be configured before proceeding.'),
                                ])
                                ->schema([

                                    // ── Type selector ────────────────────────
                                    Select::make('field_type')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Type'))
                                        ->options(['field' => __('Field'), 'relationship' => __('Relationship')])
                                        ->default('field')
                                        ->live()
                                        ->columnSpan(1),

                                    // ── FIELD-ONLY INPUTS ────────────────────
                                    TextInput::make('name')
                                        ->label(__('Field Name'))
                                        ->placeholder(__('e.g., title'))
                                        ->required(fn ($get) => ($get('field_type') ?? 'field') === 'field')
                                        ->visible(fn ($get)  => ($get('field_type') ?? 'field') === 'field')
                                        ->rules([
                                            'regex:/^[a-zA-Z_][a-zA-Z0-9_]*$/',
                                        ])
                                        ->validationMessages([
                                            'regex' => __('Field name must start with a letter or underscore and contain only letters, numbers, and underscores.'),
                                        ])
                                        ->columnSpan(1),

                                    Select::make('type')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('DB Type'))
                                        ->required(fn ($get) => ($get('field_type') ?? 'field') === 'field')
                                        ->options(self::dbTypeOptions())
                                        ->live()
                                        ->visible(fn ($get) => ($get('field_type') ?? 'field') === 'field')
                                        ->columnSpan(1),

                                    Select::make('html_type')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Input Type'))
                                        ->required(fn ($get) => ($get('field_type') ?? 'field') === 'field')
                                        ->options(fn (Get $get) => self::filteredHtmlTypeOptions($get('type')))
                                        ->default('text')
                                        ->live()
                                        ->visible(fn ($get) => ($get('field_type') ?? 'field') === 'field')
                                        ->columnSpan(1)
                                        ->helperText(fn (Get $get) => self::htmlTypeHelperText($get('type'))),

                                    // column_span + unified Configure button
                                    Select::make('column_span')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Column Width'))
                                        ->options(self::columnSpanOptions())
                                        ->required(fn ($get) => ($get('field_type') ?? 'field') === 'field')
                                        ->visible(fn ($get)  => ($get('field_type') ?? 'field') === 'field')
                                        ->columnSpan(1)
                                        ->hintAction(
                                            \Filament\Actions\Action::make('configure_field')
                                                ->label(__('Configure'))
                                                ->icon('heroicon-m-cog-6-tooth')
                                                ->modalHeading(__('Field Configuration'))
                                                ->modalWidth('2xl')
                                                ->fillForm(fn (Get $get, $livewire) => self::fillConfigureForm($get, $livewire, false))
                                                ->form(self::configureModalForm())
                                                ->action(fn (array $data, Set $set, Get $get, $livewire) => self::saveConfigureForm($data, $set, $get, $livewire, false))
                                        ),

                                    // Options for select / radio / checkbox / multiselect
                                    TagsInput::make('options')
                                        ->label(__('Options'))
                                        ->separator(',')
                                        ->splitKeys([',', 'Tab', 'Enter'])
                                        ->placeholder(__('Type and press comma, tab, or enter to add'))
                                        ->helperText(__('Use comma, tab, or enter to add each option'))
                                        ->columnSpan(2)
                                        ->visible(
                                            fn ($get) => ($get('field_type') ?? 'field') === 'field'
                                                && in_array($get('html_type'), ['select', 'multiselect', 'radio', 'checkbox'])
                                        ),

                                    // ── RELATIONSHIP-ONLY INPUTS ─────────────
                                    TextInput::make('name')
                                        ->label(__('Relationship Name'))
                                        ->placeholder(__('e.g., category'))
                                        ->helperText(__('Method name for the relationship'))
                                        ->required(fn ($get) => $get('field_type') === 'relationship')
                                        ->visible(fn ($get)  => $get('field_type') === 'relationship')
                                        ->columnSpan(1),

                                    Select::make('rel_type')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Relationship Type'))
                                        ->required(fn ($get) => $get('field_type') === 'relationship')
                                        ->options([
                                            'belongsTo'     => __('Belongs To (Many to One)'),
                                            'hasMany'       => __('Has Many (One to Many)'),
                                            'hasOne'        => __('Has One (One to One)'),
                                            'belongsToMany' => __('Belongs To Many (Many to Many)'),
                                        ])
                                        ->default('belongsTo')
                                        ->live()
                                        ->visible(fn ($get) => $get('field_type') === 'relationship')
                                        ->columnSpan(1)
                                        ->helperText(function (Get $get): ?string {
                                            $type = $get('rel_type');
                                            if (empty($type)) {
                                                return __('Select how this module connects to another module.');
                                            }
                                            return __(self::RELATIONSHIP_TYPE_DESCRIPTIONS[$type] ?? '');
                                        }),

                                    Select::make('related_model')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Related Model'))
                                        ->required(fn ($get) => $get('field_type') === 'relationship')
                                        ->options(fn () => self::availableGeneratorModels())
                                        ->searchable()
                                        ->placeholder(__('Select a CRUD module'))
                                        ->helperText(__('Only CRUD modules created through this generator are shown. System modules are excluded.'))
                                        ->live()
                                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                            if (empty($state)) return;
                                            $set('display_column', self::resolveDisplayColumnFromTable($state));
                                            
                                            // Auto-generate foreign_key and local_key
                                            $relType = $get('rel_type');
                                            if (in_array($relType, ['belongsTo', 'hasMany', 'hasOne'])) {
                                                $fk = Str::snake(Str::singular($state)) . '_id';
                                                $set('foreign_key', $fk);
                                                $set('local_key', 'id');
                                            }
                                        })
                                        ->visible(fn ($get) => $get('field_type') === 'relationship')
                                        ->columnSpan(1),

                                    Select::make('display_column')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Display Column'))
                                        ->options(fn (Get $get) => self::displayColumnOptions($get('related_model')))
                                        ->default('name')
                                        ->searchable()
                                        ->live()
                                        ->helperText(__('Used for the form dropdown label AND the table column display.'))
                                        ->visible(
                                            fn ($get) =>
                                                $get('field_type') === 'relationship'
                                                && ! empty($get('related_model'))
                                                && in_array($get('rel_type') ?? 'belongsTo', ['belongsTo', 'hasOne', 'belongsToMany'])
                                        )
                                        ->columnSpan(1),

                                    TextInput::make('foreign_key')
                                        ->label(__('Foreign Key'))
                                        ->placeholder(__('e.g., category_id'))
                                        ->helperText(__('Auto-generated based on relationship'))
                                        ->disabled()
                                        ->dehydrated(true)
                                        ->visible(
                                            fn ($get) =>
                                                $get('field_type') === 'relationship'
                                                && in_array($get('rel_type'), ['belongsTo', 'hasMany', 'hasOne'])
                                        )
                                        ->columnSpan(1),

                                    TextInput::make('local_key')
                                        ->label(__('Local Key'))
                                        ->placeholder(__('e.g., id'))
                                        ->helperText(__('Auto-generated based on relationship'))
                                        ->disabled()
                                        ->dehydrated(true)
                                        ->visible(
                                            fn ($get) =>
                                                $get('field_type') === 'relationship'
                                                && in_array($get('rel_type'), ['belongsTo', 'hasMany', 'hasOne'])
                                        )
                                        ->columnSpan(1),

                                    TextInput::make('pivot_table')
                                        ->label(__('Pivot Table Name'))
                                        ->placeholder(__('e.g., post_tags'))
                                        ->helperText(__('Leave empty for auto-generation'))
                                        ->visible(
                                            fn ($get) =>
                                                $get('field_type') === 'relationship'
                                                && $get('rel_type') === 'belongsToMany'
                                        )
                                        ->columnSpan(1),

                                    Checkbox::make('add_foreign_key_field')
                                        ->label(__('Add Foreign Key Field'))
                                        ->default(true)
                                        ->helperText(__('Automatically add foreign key field to form and table'))
                                        ->visible(
                                            fn ($get) =>
                                                $get('field_type') === 'relationship'
                                                && $get('rel_type') === 'belongsTo'
                                        ),

                                    // rel_column_span + unified Configure button for relationships
                                    Select::make('rel_column_span')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Column Width'))
                                        ->options(self::columnSpanOptions())
                                        ->default('6')
                                        ->helperText(__('Column width for relationship field in forms'))
                                        ->visible(fn ($get) => $get('field_type') === 'relationship')
                                        ->columnSpan(1)
                                        ->hintAction(
                                            \Filament\Actions\Action::make('configure_relationship')
                                                ->label(__('Configure'))
                                                ->icon('heroicon-m-cog-6-tooth')
                                                ->modalHeading(__('Relationship Configuration'))
                                                ->modalWidth('2xl')
                                                ->fillForm(fn (Get $get, $livewire) => self::fillConfigureForm($get, $livewire, true))
                                                ->form(self::configureModalForm())
                                                ->action(fn (array $data, Set $set, Get $get, $livewire) => self::saveConfigureForm($data, $set, $get, $livewire, true))
                                        ),
                                ])
                                ->columns(4)
                                // Use an explicit array default; `defaultItems(1)` can still
                                // produce an integer raw state and crash Filament rendering.
                                ->default([[]])
                                ->addActionLabel(__('Add Field'))
                                ->collapsible()
                                ->reorderable()
                                ->orderColumn('order')
                                ->afterStateHydrated(function ($state, $set) {
                                    if (! is_array($state)) {
                                        $set('', []);
                                        return;
                                    }

                                    if (self::needsRepeaterKeys($state)) {
                                        $set('', self::keyRepeaterItems($state));
                                    }
                                })
                        ]),

                    // ─────────────────────────────────────────────────────────
                    // STEP 3 – Query Conditions
                    // ─────────────────────────────────────────────────────────
                    Wizard\Step::make(__('Query Conditions'))
                        ->icon('heroicon-m-funnel')
                        ->schema([
                            // ── Short Info Note ────────────────────────────────
                            \Filament\Forms\Components\Placeholder::make('query_conditions_note')
                                ->label('')
                                ->content(function (Get $get) {
                                    $hasRelationships = false;
                                    foreach ($get('fields') ?? [] as $field) {
                                        if (($field['field_type'] ?? 'field') === 'relationship') {
                                            $hasRelationships = true;
                                            break;
                                        }
                                    }
                                    $relMsg = $hasRelationships
                                        ? '<span style="color:var(--primary-600);">✅ Relationship fields detected. Module dropdown will show related modules.</span>'
                                        : '<span style="color:var(--primary-600);">⚠️ Add a Relationship field in Step 2 first.</span>';

                                    return new \Illuminate\Support\HtmlString('
                                        <div style="padding:10px 14px;background:var(--primary-50);border-left:4px solid var(--primary-500);border-radius:4px;font-size:13px;color:var(--primary-900);">
                                            <strong style="color:var(--primary-700);">Filter relationship dropdowns & table data.</strong> Example: Module=Category, Field=status, Operator==, Value=Active → only active categories appear in dropdown.
                                            Leave empty to show all records. ' . $relMsg . '
                                        </div>
                                    ');
                                })
                                ->columnSpanFull(),

                            Repeater::make('query_conditions')
                                ->label(__('Custom Query Conditions'))
                                ->schema([
                                    Select::make('module')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Module'))
                                        ->markAsRequired()
                                        ->rules(['required'])
                                        ->live()
                                        ->options(fn (Get $get) => self::queryConditionModuleOptions(
                                            $get('../../model_name'),
                                            $get('../../fields') ?? [],
                                        ))
                                        ->afterStateHydrated(function (Get $get, Set $set) {
                                            if (filled($get('module'))) {
                                                return;
                                            }

                                            $module = self::inferQueryConditionModule($get('field'));

                                            if ($module !== null) {
                                                $set('module', $module);
                                            }

                                            if (blank($get('relationship'))) {
                                                $set('relationship', self::relationshipValueForModule($module));
                                            }
                                        })
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $set('field', null);
                                            $set('value', null);
                                            $set('relationship', self::relationshipValueForModule($state));
                                        })
                                        ->placeholder(__('Select module'))
                                        ->helperText(__('The related module from Step 2 (e.g., Category, User)')),

                                    Select::make('field')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Field Name'))
                                        ->markAsRequired()
                                        ->rules(['required'])
                                        ->options(fn (Get $get) => self::queryConditionFieldOptions(
                                            $get('module'),
                                            $get('../../model_name'),
                                            $get('../../fields') ?? [],
                                        ))
                                        ->searchable()
                                        ->preload()
                                        ->live()
                                        ->afterStateUpdated(function (Get $get, Set $set) {
                                            $set('value', null);
                                            $set('relationship', self::relationshipValueForModule($get('module')));
                                        })
                                        ->placeholder(__('Select field name'))
                                        ->helperText(__('Column to filter by (e.g., status, is_active)')),

                                    Select::make('operator')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Operator'))
                                        ->markAsRequired()
                                        ->rules(['required'])
                                        ->options([
                                            '='        => __('Equals (=)'),
                                            '!='       => __('Not Equals (!=)'),
                                            '>'        => __('Greater Than (>)'),
                                            '<'        => __('Less Than (<)'),
                                            '>='       => __('Greater Than or Equal (>=)'),
                                            '<='       => __('Less Than or Equal (<=)'),
                                            'like'     => __('Like'),
                                            'not like' => __('Not Like'),
                                            'in'       => __('In'),
                                            'not in'   => __('Not In'),
                                            'between'  => __('Between'),
                                            'null'     => __('Is Null'),
                                            'not null' => __('Is Not Null'),
                                        ])
                                        ->default('=')
                                        ->live()
                                        ->helperText(__('How to compare: = (equal), != (not equal), like (contains)')),

                                    Select::make('value')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Value'))
                                        ->options(fn (Get $get) => self::queryConditionValueOptions(
                                            $get('module'),
                                            $get('field'),
                                            $get('../../model_name'),
                                            $get('../../fields') ?? [],
                                        ))
                                        ->searchable()
                                        ->preload()
                                        ->allowHtml(false)
                                        ->visible(fn ($get) => ! in_array($get('operator'), ['null', 'not null']))
                                        ->multiple(fn ($get) => in_array($get('operator'), ['in', 'not in']))
                                        ->markAsRequired()
                                        ->rules([
                                            'required',
                                            fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                $operator = $get('operator');
                                                if (in_array($operator, ['null', 'not null'])) {
                                                    return;
                                                }
                                                if (blank($value)) {
                                                    $fail(__('A value is required for this operator.'));
                                                }
                                                if (in_array($operator, ['in', 'not in']) && (! is_array($value) || count($value) === 0)) {
                                                    $fail(__('Please select at least one value.'));
                                                }
                                            },
                                        ])
                                        ->helperText(__('Value to match (e.g., Active, 1, published)')),

                                    Select::make('condition_type')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Condition Type'))
                                        ->options([
                                            'where'           => __('Where'),
                                            'orWhere'         => __('Or Where'),
                                            'whereHas'        => __('Where Has (Relationship)'),
                                            'whereDoesntHave' => __('Where Doesnt Have (Relationship)'),
                                        ])
                                        ->default('where')
                                        ->helperText(__('Where=AND, OrWhere=OR, WhereHas=has related records')),

                                    Hidden::make('relationship'),

                                    Checkbox::make('in_form')
                                        ->label(__('Apply to Form Queries'))
                                        ->default(true)
                                        ->helperText(__('Filter dropdown options in Create/Edit forms')),

                                    Checkbox::make('in_table')
                                        ->label(__('Apply to Table Queries'))
                                        ->default(true)
                                        ->helperText(__('Filter records shown in the table listing')),
                                ])
                                ->columns(4)
                                ->default([])
                                ->afterStateHydrated(function ($state, $set) {
                                    if (! is_array($state)) {
                                        $set('', []);
                                        return;
                                    }

                                    $normalizedState = array_map(function ($item) {
                                        if (! is_array($item)) {
                                            return $item;
                                        }

                                        $module = $item['module'] ?? self::inferQueryConditionModule($item['field'] ?? null);

                                        if (($module !== null) && isset($item['field']) && is_string($item['field']) && str_contains($item['field'], '.')) {
                                            [, $field] = explode('.', $item['field'], 2);
                                            $item['field'] = $field;
                                        }

                                        $item['module'] = $module;
                                        $item['relationship'] = $item['relationship'] ?? self::relationshipValueForModule($module);

                                        return $item;
                                    }, $state);

                                    if ($normalizedState !== $state) {
                                        $set('', $normalizedState);
                                    }
                                })
                                ->addActionLabel(__('Add Query Condition'))
                                ->collapsible(),
                        ]),

                    // ─────────────────────────────────────────────────────────
                    // STEP 4 – Table Configuration
                    // Toggleable / Hidden By Default removed — controlled solely
                    // by Searchable and Sortable for a cleaner interface.
                    // ─────────────────────────────────────────────────────────
                    Wizard\Step::make(__('Table Configuration'))
                        ->icon('heroicon-m-table-cells')
                        ->beforeValidation(function ($livewire) {
                            if (! empty($livewire->data['table_columns'] ?? [])) {
                                return;
                            }

                            $fields = $livewire->data['fields'] ?? [];

                            if (empty($fields) || ! is_array($fields)) {
                                return;
                            }

                            $livewire->data['table_columns'] = self::keyRepeaterItems(
                                self::buildDefaultColumns(self::normalizeFieldRepeaterItems($fields)),
                            );
                        })
                        ->schema([
                            // ── Short Info Note ────────────────────────────────
                            \Filament\Forms\Components\Placeholder::make('table_config_note')
                                ->label('')
                                ->content(new \Illuminate\Support\HtmlString('
                                    <div style="padding:10px 14px;background:var(--primary-50);border-left:4px solid var(--primary-500);border-radius:4px;font-size:13px;color:var(--primary-900);">
                                        <strong style="color:var(--primary-700);">Manage table columns & their order.</strong> Columns auto-generated from Step 2 fields. <strong>Drag to reorder.</strong> Use dot-notation for relationships (e.g., <code>category.name</code>). Searchable = filter by column, Sortable = click header to sort.
                                    </div>
                                '))
                                ->columnSpanFull(),

                            Repeater::make('table_columns')
                                ->label(__('Table Columns'))
                                ->helperText(__('Drag items to reorder columns. The first item appears first in the table (after ID).'))
                                ->markAsRequired()
                                ->rules(['required', 'array', 'min:1'])
                                ->minItems(1)
                                ->schema([
                                    TextInput::make('name')
                                        ->label(__('Column Name'))
                                        ->markAsRequired()
                                        ->rules([
                                            'required',
                                            'string',
                                            'max:128',
                                            'regex:/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/',
                                        ])
                                        ->validationMessages([
                                            'regex' => __('Column name must be a valid database column name. Use dot-notation for relationships (e.g., category.name).'),
                                        ])
                                        ->placeholder(__('e.g., title  or  category.name'))
                                        ->helperText(__('For relationships use dot-notation: relationshipName.column (e.g. category.name). Only letters, numbers, and underscores allowed.'))
                                        ->columnSpan(2),

                                    Select::make('html_type')
                                        ->native(false)
                                        ->selectablePlaceholder()
                                        ->label(__('Column Type'))
                                        ->options([
                                            'text'     => __('Text'),
                                            'select'   => __('Dropdown / Select'),
                                            'toggle'   => __('Boolean / Icon'),
                                            'date'     => __('Date'),
                                            'datetime' => __('DateTime'),
                                            'time'     => __('Time'),
                                            'file'     => __('Image'),
                                        ])
                                        ->default('text')
                                        ->columnSpan(1),

                                    Checkbox::make('searchable')
                                        ->label(__('Searchable'))
                                        ->helperText(__('Allow searching by this column'))
                                        ->default(false),

                                    Checkbox::make('sortable')
                                        ->label(__('Sortable'))
                                        ->helperText(__('Allow sorting by this column'))
                                        ->default(false),
                                ])
                                ->columns(4)
                                // Filament Repeater expects an array state; avoid integer `0` raw state.
                                ->default([])
                                ->addActionLabel(__('Add Column'))
                                ->collapsible()
                                ->reorderable()
                                ->orderColumn('order')
                                ->afterStateHydrated(function ($state, $set, $livewire) {
                                    if (! is_array($state)) {
                                        $set('', []);
                                        return;
                                    }

                                    if (self::needsRepeaterKeys($state)) {
                                        $set('', self::keyRepeaterItems($state));
                                        return;
                                    }

                                    if (! empty($state)) {
                                        return;
                                    }

                                    $fields = $livewire->data['fields'] ?? $livewire->record?->fields ?? [];

                                    if (empty($fields) || ! is_array($fields)) {
                                        return;
                                    }

                                    $set('', self::keyRepeaterItems(
                                        self::buildDefaultColumns(self::normalizeFieldRepeaterItems($fields)),
                                    ));
                                })
                                ->afterStateUpdated(function ($state, Set $set) {
                                    if (! is_array($state)) {
                                        return;
                                    }

                                    if (self::needsRepeaterKeys($state)) {
                                        $set('', self::keyRepeaterItems($state));
                                        return;
                                    }

                                    $indexedState = array_values($state);
                                    foreach ($indexedState as $index => &$item) {
                                        if (is_array($item)) {
                                            $item['order'] = $index + 1;
                                        }
                                    }
                                }),
                        ]),

                ])->columnSpanFull()
            ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Query condition helpers
    // ─────────────────────────────────────────────────────────────────────────

    private static function queryConditionModuleOptions(?string $modelName, array $allFields): array
    {
        $options = [];

        foreach ($allFields as $field) {
            if (($field['field_type'] ?? 'field') !== 'relationship') {
                continue;
            }

            $moduleKey = self::relationshipModuleKey($field);

            if ($moduleKey === '') {
                continue;
            }

            $options[$moduleKey] = $field['related_model'] ?? $field['name'] ?? $moduleKey;
        }

        return $options;
    }

    private static function queryConditionFieldOptions(?string $module, ?string $modelName, array $allFields): array
    {
        if (blank($module)) {
            return [];
        }

        if ($module === 'self') {
            $options = [];

            foreach ($allFields as $field) {
                if (($field['field_type'] ?? 'field') !== 'field') {
                    continue;
                }

                $column = Str::snake($field['name'] ?? '');

                if ($column === '') {
                    continue;
                }

                $options[$column] = Str::title(str_replace('_', ' ', $column));
            }

            return $options;
        }

        $relationship = self::findRelationshipByModule($module, $allFields);

        if (! $relationship) {
            return [];
        }

        $relatedTable = Str::snake(Str::plural($relationship['related_model'] ?? ''));

        if (\Illuminate\Support\Facades\Schema::hasTable($relatedTable)) {
            return collect(\Illuminate\Support\Facades\Schema::getColumnListing($relatedTable))
                ->mapWithKeys(fn (string $column) => [$column => Str::title(str_replace('_', ' ', $column))])
                ->toArray();
        }

        return [
            'id' => 'ID',
            'name' => 'Name',
        ];
    }

    private static function queryConditionModelClass(?string $module, ?string $modelName, array $allFields): ?string
    {
        if ($module === 'self' && filled($modelName)) {
            $modelClass = "App\\Models\\{$modelName}";

            return class_exists($modelClass) ? $modelClass : null;
        }

        $relationship = self::findRelationshipByModule($module, $allFields);

        if (! $relationship || empty($relationship['related_model'])) {
            return null;
        }

        $modelClass = "App\\Models\\{$relationship['related_model']}";

        return class_exists($modelClass) ? $modelClass : null;
    }

    private static function queryConditionValueOptions(?string $module, ?string $field, ?string $modelName, array $allFields): array
    {
        if (blank($field)) {
            return [];
        }

        if ($module === 'self') {
            $fields = array_filter($allFields, fn ($configuredField) => ($configuredField['field_type'] ?? 'field') === 'field');

            foreach ($fields as $configuredField) {
                if (Str::snake($configuredField['name'] ?? '') !== $field) {
                    continue;
                }

                if (! empty($configuredField['options'])) {
                    $options = is_array($configuredField['options'])
                        ? $configuredField['options']
                        : explode(',', $configuredField['options']);

                    $options = array_values(array_filter(array_map('trim', $options), fn ($value) => $value !== ''));

                    return array_combine($options, $options) ?: [];
                }

                if (($configuredField['type'] ?? null) === 'enum') {
                    return ['active' => 'Active', 'inactive' => 'Inactive'];
                }

                if (($configuredField['type'] ?? null) === 'boolean') {
                    return ['1' => 'True/Yes', '0' => 'False/No'];
                }
            }
        }

        $modelClass = self::queryConditionModelClass($module, $modelName, $allFields);

        if (! $modelClass) {
            return [];
        }

        // Look up field configuration from the related module's generator so we can
        // map stored numeric keys back to text labels for choice fields.
        $fieldConfig = null;
        if ($module === 'self') {
            foreach ($allFields as $configuredField) {
                if (($configuredField['field_type'] ?? 'field') === 'field' && Str::snake($configuredField['name'] ?? '') === $field) {
                    $fieldConfig = $configuredField;
                    break;
                }
            }
        } else {
            $relationship = self::findRelationshipByModule($module, $allFields);
            $relatedModel = $relationship['related_model'] ?? null;
            if ($relatedModel) {
                $relatedGenerator = \App\Models\Generator::where('model_name', $relatedModel)->first();
                if ($relatedGenerator && is_array($relatedGenerator->fields)) {
                    foreach ($relatedGenerator->fields as $configuredField) {
                        if (($configuredField['field_type'] ?? 'field') === 'field' && Str::snake($configuredField['name'] ?? '') === $field) {
                            $fieldConfig = $configuredField;
                            break;
                        }
                    }
                }
            }
        }

        try {
            $values = $modelClass::query()
                ->whereNotNull($field)
                ->pluck($field)
                ->filter(fn ($value) => $value !== '')
                ->unique()
                ->sort()
                ->toArray();

            // If no values found in database, show a placeholder so users understand
            // they need to add records to the related module first.
            if (empty($values)) {
                return ['__empty__' => __('— No records found in this module —')];
            }

            // For choice fields with options, map stored values back to text labels.
            if (! empty($fieldConfig['options'])) {
                $dbType = $fieldConfig['type'] ?? 'string';
                $options = is_array($fieldConfig['options'])
                    ? $fieldConfig['options']
                    : explode(',', $fieldConfig['options']);
                $options = array_values(array_filter(array_map('trim', $options), fn ($v) => $v !== ''));

                $isInteger = in_array($dbType, [
                    'tinyInteger', 'unsignedTinyInteger', 'smallInteger', 'unsignedSmallInteger',
                    'mediumInteger', 'unsignedMediumInteger', 'integer', 'unsignedInteger',
                    'bigInteger', 'unsignedBigInteger', 'year',
                ]);

                $labelMap = [];
                foreach ($options as $index => $label) {
                    $key = ($isInteger && ! is_numeric($label)) ? ($index + 1) : $label;
                    $labelMap[(string) $key] = $label;
                }

                $mapped = [];
                foreach ($values as $value) {
                    $key = (string) $value;
                    $mapped[$key] = $labelMap[$key] ?? $key;
                }

                return $mapped;
            }

            return array_combine(
                array_map('strval', $values),
                array_map('strval', $values)
            );
        } catch (\Throwable $exception) {
            return [];
        }
    }

    private static function relationshipModuleKey(array $relationship): string
    {
        if (! empty($relationship['foreign_key']) && str_ends_with($relationship['foreign_key'], '_id')) {
            return Str::camel(Str::beforeLast($relationship['foreign_key'], '_id'));
        }

        if (! empty($relationship['related_model'])) {
            return Str::camel($relationship['related_model']);
        }

        return Str::camel($relationship['name'] ?? '');
    }

    private static function findRelationshipByModule(?string $module, array $allFields): ?array
    {
        if (blank($module) || $module === 'self') {
            return null;
        }

        foreach ($allFields as $field) {
            if (($field['field_type'] ?? 'field') !== 'relationship') {
                continue;
            }

            if (self::relationshipModuleKey($field) === $module) {
                return $field;
            }
        }

        return null;
    }

    private static function inferQueryConditionModule(?string $field): ?string
    {
        if (blank($field)) {
            return null;
        }

        if (! str_contains($field, '.')) {
            return null;
        }

        [$relationship] = explode('.', $field, 2);

        return $relationship;
    }

    private static function relationshipValueForModule(?string $module): ?string
    {
        if (blank($module) || $module === 'self') {
            return null;
        }

        return $module;
    }

    private static function humanizeFieldLabel(?string $fieldName): string
    {
        return Str::title(str_replace('_', ' ', (string) $fieldName));
    }

    private static function defaultPlaceholderText(?string $fieldName, ?string $htmlType): string
    {
        $label = self::humanizeFieldLabel($fieldName);

        return in_array($htmlType, ['select', 'multiselect', 'radio', 'checkbox'], true)
            ? "Select {$label}"
            : "Enter {$label}";
    }

    /**
     * @param  array<int|string, mixed>  $fields
     * @return array<int, array<string, mixed>>
     */
    private static function normalizeFieldRepeaterItems(array $fields): array
    {
        return array_values(array_filter($fields, function ($field): bool {
            if (! is_array($field)) {
                return false;
            }

            $fieldType = $field['field_type'] ?? 'field';

            if ($fieldType === 'relationship') {
                return filled($field['name'] ?? null) && filled($field['related_model'] ?? null);
            }

            return filled($field['name'] ?? null) && filled($field['type'] ?? null);
        }));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Build default table_columns from the unified fields array.
    // For relationships: reads display_column saved in field config → dot-notation.
    // toggleable / hidden_by_default are intentionally omitted.
    // ─────────────────────────────────────────────────────────────────────────
    public static function buildDefaultColumns(array $fields): array
    {
        $columns = [];

        // Ensure we have a valid array to work with
        if (empty($fields) || ! is_array($fields)) {
            return $columns;
        }

        foreach ($fields as $field) {
            $fieldType = $field['field_type'] ?? 'field';

            if (empty($field['name'])) continue;

            if ($fieldType === 'field') {
                if (isset($field['in_table']) && $field['in_table'] === false) continue;

                $htmlType = $field['html_type'] ?? 'text';
                // Normalize choice inputs to a single 'select' table type so Step 4
                // can display them properly and the table knows to map option labels.
                if (in_array($htmlType, ['select', 'radio', 'checkbox', 'multiselect'])) {
                    $htmlType = 'select';
                }

                $columns[] = [
                    'name'       => $field['name'],
                    'html_type'  => $htmlType,
                    'searchable' => true,
                    'sortable'   => true,
                ];
            } elseif ($fieldType === 'relationship') {
                $relType = $field['rel_type'] ?? $field['type'] ?? 'belongsTo';
                if (! in_array($relType, ['belongsTo', 'hasOne'])) continue;

                $inTable = $field['in_table'] ?? true;
                if ($inTable === false || $inTable === 0 || $inTable === '0') continue;

                $displayColumn = ! empty($field['display_column'])
                    ? $field['display_column']
                    : self::resolveDisplayColumnFromTable($field['related_model'] ?? '');

                $columns[] = [
                    'name'       => $field['name'] . '.' . $displayColumn,
                    'html_type'  => 'text',
                    'searchable' => $field['searchable'] ?? true,
                    'sortable'   => $field['sortable']   ?? true,
                ];
            }
        }

        return $columns;
    }

    /**
     * Filament drag-drop repeaters need stable string keys. Numeric list keys
     * can be rewritten to scalar indexes during reorder actions.
     *
     * @param  array<int|string, mixed>  $items
     * @return array<string, array<string, mixed>>
     */
    private static function keyRepeaterItems(array $items): array
    {
        $keyedItems = [];

        foreach ($items as $key => $item) {
            if (! is_array($item)) {
                continue;
            }

            $resolvedKey = is_string($key) && ($key !== '') ? $key : (string) Str::uuid();
            $keyedItems[$resolvedKey] = $item;
        }

        return $keyedItems;
    }

    /**
     * @param  array<int|string, mixed>  $items
     */
    private static function needsRepeaterKeys(array $items): bool
    {
        if ($items === []) {
            return false;
        }

        return array_is_list($items);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private static function columnSpanOptions(): array
    {
        $options = [];
        for ($i = 3; $i <= 12; $i++) {
            $options[(string) $i] = __("{$i} Col");
        }
        return $options;
    }

    private static function dbTypeOptions(): array
    {
        return [
            'string'          => __('String'),
            'text'            => __('Text'),
            'longText'        => __('Long Text'),
            'integer'         => __('Integer'),
            'bigInteger'      => __('Big Integer'),
            'tinyInteger'     => __('Tiny Integer'),
            'smallInteger'    => __('Small Integer'),
            'mediumInteger'   => __('Medium Integer'),
            'unsignedInteger' => __('Unsigned Integer'),
            'float'           => __('Float'),
            'double'          => __('Double'),
            'decimal'         => __('Decimal'),
            'boolean'         => __('Boolean'),
            'enum'            => __('Enum'),
            'date'            => __('Date'),
            'dateTime'        => __('DateTime'),
            'time'            => __('Time'),
            'timestamp'       => __('Timestamp'),
            'year'            => __('Year'),
            'binary'          => __('Binary'),
            'json'            => __('JSON'),
            'jsonb'           => __('JSONB'),
            'uuid'            => __('UUID'),
            'ipAddress'       => __('IP Address'),
        ];
    }

    private static function htmlTypeOptions(): array
    {
        return [
            'text'        => __('Text Input'),
            'textarea'    => __('Textarea'),
            'select'      => __('Select'),
            'multiselect' => __('Multi Select'),
            'checkbox'    => __('Checkbox'),
            'radio'       => __('Radio'),
            'email'       => __('Email'),
            'password'    => __('Password'),
            'number'      => __('Number'),
            'date'        => __('Date'),
            'datetime'    => __('DateTime'),
            'time'        => __('Time'),
            'file'        => __('File Upload'),
            'url'         => __('URL'),
            'color'       => __('Color'),
            'toggle'      => __('Toggle'),
            'tags'        => __('Tags'),
        ];
    }

    /**
     * Filter input type options based on the selected DB type.
     * Prevents invalid combinations like DB date with textarea input.
     */
    private static function filteredHtmlTypeOptions(?string $dbType): array
    {
        $allOptions = self::htmlTypeOptions();

        if (blank($dbType)) {
            return $allOptions;
        }

        // Map DB types to suitable input types
        $mapping = [
            // Text-based DB types
            'string'          => ['text', 'textarea', 'email', 'password', 'url', 'color', 'select', 'radio', 'checkbox', 'multiselect', 'tags'],
            'text'            => ['textarea', 'text', 'tags'],
            'longText'        => ['textarea', 'text'],
            'json'            => ['textarea', 'text'],
            'jsonb'           => ['textarea', 'text'],

            // Numeric DB types
            'integer'         => ['number', 'text', 'select'],
            'bigInteger'      => ['number', 'text', 'select'],
            'tinyInteger'     => ['number', 'toggle', 'select', 'checkbox'],
            'smallInteger'    => ['number', 'toggle', 'select', 'checkbox'],
            'mediumInteger'   => ['number', 'text', 'select'],
            'unsignedInteger' => ['number', 'text', 'select'],
            'float'           => ['number', 'text'],
            'double'          => ['number', 'text'],
            'decimal'         => ['number', 'text'],

            // Date/Time DB types
            'date'            => ['date'],
            'dateTime'        => ['datetime', 'date'],
            'time'            => ['time'],
            'timestamp'       => ['datetime', 'date'],
            'year'            => ['number', 'select'],

            // Boolean
            'boolean'         => ['toggle', 'checkbox', 'select'],

            // Enum
            'enum'            => ['select', 'radio', 'checkbox', 'multiselect'],

            // Other
            'binary'          => ['file'],
            'uuid'            => ['text'],
            'ipAddress'       => ['text'],
        ];

        $allowed = $mapping[$dbType] ?? array_keys($allOptions);
        return array_intersect_key($allOptions, array_flip($allowed));
    }

    /**
     * Show helper text when DB type is selected to guide user.
     */
    private static function htmlTypeHelperText(?string $dbType): ?string
    {
        if (blank($dbType)) {
            return null;
        }

        $messages = [
            'date'      => __('Date DB type only supports Date input.'),
            'dateTime'  => __('DateTime DB type supports DateTime or Date input.'),
            'time'      => __('Time DB type only supports Time input.'),
            'timestamp' => __('Timestamp DB type supports DateTime or Date input.'),
            'boolean'   => __('Boolean DB type supports Toggle, Checkbox, or Select.'),
            'tinyInteger'   => __('Small integer supports Toggle (0/1), Number, Select, or Checkbox.'),
            'smallInteger'  => __('Small integer supports Toggle (0/1), Number, Select, or Checkbox.'),
            'integer'   => __('Integer supports Number, Text, or Select.'),
            'text'      => __('Text DB type supports Textarea, Text, or Tags.'),
            'longText'  => __('Long Text supports Textarea or Text.'),
        ];

        return $messages[$dbType] ?? null;
    }

    /**
     * Get only generator-created models for the Related Model dropdown.
     * Excludes system/internal models to prevent users from creating
     * relationships with core system modules.
     */
    private static function availableGeneratorModels(): array
    {
        $models = [];

        // Get models from the database that were created via the CRUD Generator
        try {
            $generatorModels = Generator::query()
                ->where('status', 'active')
                ->pluck('model_name')
                ->filter()
                ->unique()
                ->values();

            foreach ($generatorModels as $modelName) {
                $models[$modelName] = $modelName;
            }
        } catch (\Exception $e) {
            // If database is not available yet, fall back to file scanning
        }

        // Fallback: scan Models directory but exclude system models
        $modelPath = app_path('Models');
        if (is_dir($modelPath) && empty($models)) {
            foreach (scandir($modelPath) as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                    $modelName = pathinfo($file, PATHINFO_FILENAME);
                    // Exclude system models and User
                    if (! in_array($modelName, self::EXCLUDED_SYSTEM_MODELS, true)) {
                        $models[$modelName] = $modelName;
                    }
                }
            }
        }

        return $models;
    }

    /**
     * @deprecated Use availableGeneratorModels() instead.
     * Kept for backward compatibility with any existing references.
     */
    private static function availableModels(): array
    {
        return self::availableGeneratorModels();
    }

    public static function resolveDisplayColumnFromTable(string $relatedModel): string
    {
        if (empty($relatedModel)) return 'name';

        $relatedTable = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($relatedModel));
        if (! \Illuminate\Support\Facades\Schema::hasTable($relatedTable)) return 'name';

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($relatedTable);
        foreach (['name', 'title', 'first_name', 'email'] as $preferred) {
            if (in_array($preferred, $columns)) return $preferred;
        }
        return 'id';
    }

    private static function displayColumnOptions(?string $relatedModel): array
    {
        if (empty($relatedModel)) return [];

        $relatedTable = \Illuminate\Support\Str::snake(\Illuminate\Support\Str::plural($relatedModel));

        if (! \Illuminate\Support\Facades\Schema::hasTable($relatedTable)) {
            return ['id' => 'id', 'name' => 'name', 'title' => 'title', 'email' => 'email'];
        }

        $excluded = ['deleted_at', 'remember_token', 'password', 'email_verified_at'];
        $options  = [];

        foreach (\Illuminate\Support\Facades\Schema::getColumnListing($relatedTable) as $column) {
            if (! in_array($column, $excluded)) {
                $options[$column] = \Illuminate\Support\Str::title(str_replace('_', ' ', $column));
            }
        }

        return $options;
    }
}
