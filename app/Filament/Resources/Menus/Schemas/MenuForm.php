<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Menu Information')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('page_name')
                                    ->label('Page Name')
                                    ->placeholder('e.g., About Us, Contact')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        // Only auto-generate slug if it's empty or hasn't been manually edited
                                        if (empty($get('slug'))) {
                                            $set('slug', \Illuminate\Support\Str::slug($state));
                                        }
                                    })
                                    ->maxLength(255),

                                TextInput::make('sort_order')
                                    ->label('Sort Order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                            ]),

                        Radio::make('page_type')
                            ->label('Page Type')
                            ->options([
                                'url' => 'Page URL',
                                'content' => 'Page Content',
                            ])
                            ->default('url')
                            ->inline()
                            ->live()
                            ->required(),

                        // Show when Page URL is selected
                        TextInput::make('url')
                            ->label('URL')
                            ->placeholder('https://example.com/page or /about')
                            ->required(fn (callable $get) => $get('page_type') === 'url')
                            ->visible(fn (callable $get) => $get('page_type') === 'url')
                            ->url(fn (callable $get) => $get('page_type') === 'url')
                            ->prefixIcon('heroicon-o-link'),

                        // Show when Page Content is selected
                        TextInput::make('slug')
                            ->label('Page Slug (Auto-generated)')
                            ->placeholder('about-us')
                            ->helperText('You can edit this or leave it blank to auto-generate from Page Name. URL will be: /page/your-slug')
                            ->required(fn (callable $get) => $get('page_type') === 'content')
                            ->visible(fn (callable $get) => $get('page_type') === 'content')
                            ->unique('menus', 'slug', ignoreRecord: true)
                            ->prefixIcon('heroicon-o-document-text')
                            ->suffixAction(
                                \Filament\Actions\Action::make('generateSlug')
                                    ->label('Generate')
                                    ->icon('heroicon-o-sparkles')
                                    ->tooltip('Generate slug from Page Name')
                                    ->action(function (callable $set, callable $get) {
                                        $pageName = $get('page_name');
                                        if (!empty($pageName)) {
                                            $set('slug', \Illuminate\Support\Str::slug($pageName));
                                        }
                                    })
                            ),

                        RichEditor::make('content')
                            ->label('Page Content')
                            ->placeholder('Enter your page content here...')
                            ->required(fn (callable $get) => $get('page_type') === 'content')
                            ->visible(fn (callable $get) => $get('page_type') === 'content')
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'orderedList',
                                'bulletList',
                                'h2',
                                'h3',
                                'blockquote',
                                'redo',
                                'undo',
                            ]),
                    ])
                    ->columnSpanFull(),

                Section::make('Menu Placement')
                    ->schema([
                        Radio::make('placement')
                            ->label('Where should this menu appear?')
                            ->options([
                                'header' => 'Header Menu',
                                'footer' => 'Footer Menu',
                            ])
                            ->default('header')
                            ->inline()
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive menus will not be displayed on the frontend')
                            ->default(true),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
