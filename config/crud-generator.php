<?php

return [
    'generators' => [
        'model' => \App\Services\CrudGenerator\Generators\ModelGenerator::class,
        'migration' => \App\Services\CrudGenerator\Generators\MigrationGenerator::class,
        'form_schema' => \App\Services\CrudGenerator\Generators\FormSchemaGenerator::class,
        'table_schema' => \App\Services\CrudGenerator\Generators\TableSchemaGenerator::class,
        'resource' => \App\Services\CrudGenerator\Generators\ResourceGenerator::class,
        'resource_page' => \App\Services\CrudGenerator\Generators\ResourcePageGenerator::class,
        'permission' => \App\Services\CrudGenerator\Generators\PermissionGenerator::class,
    ],
    
    'plural_names' => [
        'Category' => 'Categories',
        'City' => 'Cities',
        'Party' => 'Parties',
        'Company' => 'Companies',
        'Country' => 'Countries',
        'Industry' => 'Industries',
        'Agency' => 'Agencies',
        'Policy' => 'Policies',
        'Story' => 'Stories',
        'Entry' => 'Entries',
        'Query' => 'Queries',
        'Activity' => 'Activities',
        'Priority' => 'Priorities',
        'Authority' => 'Authorities',
        'Community' => 'Communities',
        'University' => 'Universities',
        'Library' => 'Libraries',
        'Gallery' => 'Galleries',
        'Battery' => 'Batteries',
        'Factory' => 'Factories',
        'History' => 'Histories',
        'Memory' => 'Memories',
        'Territory' => 'Territories',
        'Directory' => 'Directories',
        'Repository' => 'Repositories',
        'Laboratory' => 'Laboratories',
        'Secretary' => 'Secretaries',
        'Dictionary' => 'Dictionaries',
        'Commentary' => 'Commentaries',
        'Documentary' => 'Documentaries',
        'Inventory' => 'Inventories',
        'Anniversary' => 'Anniversaries',
    ],
    
    'field_types' => [
        'string' => [
            'migration_type' => 'string',
            'cast' => null,
            'html_types' => ['text', 'email', 'url', 'password'],
            'validation' => ['string', 'max:255'],
        ],
        'text' => [
            'migration_type' => 'text',
            'cast' => null,
            'html_types' => ['textarea'],
            'validation' => ['string'],
        ],
        'integer' => [
            'migration_type' => 'integer',
            'cast' => 'integer',
            'html_types' => ['number'],
            'validation' => ['integer'],
        ],
        'boolean' => [
            'migration_type' => 'boolean',
            'cast' => 'boolean',
            'html_types' => ['toggle', 'checkbox'],
            'validation' => ['boolean'],
        ],
        'date' => [
            'migration_type' => 'date',
            'cast' => 'datetime',
            'html_types' => ['date'],
            'validation' => ['date'],
        ],
        'dateTime' => [
            'migration_type' => 'dateTime',
            'cast' => 'datetime',
            'html_types' => ['datetime'],
            'validation' => ['date'],
        ],
        'time' => [
            'migration_type' => 'time',
            'cast' => null,
            'html_types' => ['time'],
            'validation' => ['date_format:H:i:s'],
        ],
        'json' => [
            'migration_type' => 'json',
            'cast' => 'array',
            'html_types' => ['tags', 'multiselect'],
            'validation' => ['array'],
        ],
    ],
    
    'templates' => [
        'path' => app_path('Services/CrudGenerator/Stubs'),
        'cache' => env('CRUD_GENERATOR_CACHE_STUBS', true),
    ],
    
    'permissions' => [
        'auto_create' => true,
        'assign_to_creator' => true,
    ],
];
