<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Models\CmsSetting;
use App\Models\Menu;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LandingPageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.settings.pages.landing-page-settings';
    protected static ?string $title = null;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedWindow;

    public function getTitle(): string
    {
        return __('Landing Page Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Landing Page Settings');
    }

    protected static ?int $navigationSort = 111;

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public ?array $data = [];

    public function mount(): void
    {
        // Load all settings into data array
        $this->data = [
            // Global Settings
            'main_bg_color' => CmsSetting::get('main_bg_color', 'global', '#7369dd'),
            'light_bg_color' => CmsSetting::get('light_bg_color', 'global', '#f9fafb'),
            'text_color' => CmsSetting::get('text_color', 'global', '#111827'),
            'heading_color' => CmsSetting::get('heading_color', 'global', '#111827'),
            'font_family' => CmsSetting::get('font_family', 'global', 'DM Sans'),

            // Hero Settings
            'hero_badge_text' => CmsSetting::get('badge_text', 'hero', ''),
            'hero_title' => CmsSetting::get('title', 'hero', ''),
            'hero_description' => CmsSetting::get('description', 'hero', ''),
            'hero_primary_button_text' => CmsSetting::get('primary_button_text', 'hero', ''),
            'hero_primary_button_url' => CmsSetting::get('primary_button_url', 'hero', ''),
            'hero_secondary_button_text' => CmsSetting::get('secondary_button_text', 'hero', ''),
            'hero_secondary_button_url' => CmsSetting::get('secondary_button_url', 'hero', ''),
            'hero_stats' => CmsSetting::get('stats', 'hero', []),
            'hero_image' => CmsSetting::get('image', 'hero', ''),

            // Features
            'features' => CmsSetting::get('items', 'features', []),

            // Steps
            'steps' => CmsSetting::get('items', 'steps', []),

            // CTA Settings
            'cta_title' => CmsSetting::get('title', 'cta', ''),
            'cta_subtitle' => CmsSetting::get('subtitle', 'cta', ''),
            'cta_button_text' => CmsSetting::get('button_text', 'cta', ''),
            'cta_button_url' => CmsSetting::get('button_url', 'cta', ''),

            // About Page
            'about_title' => CmsSetting::get('title', 'about', ''),
            'about_subtitle' => CmsSetting::get('subtitle', 'about', ''),
            'about_story_title' => CmsSetting::get('story_title', 'about', ''),
            'about_story_content' => CmsSetting::get('story_content', 'about', ''),
            'about_mission_title' => CmsSetting::get('mission_title', 'about', ''),
            'about_mission_content' => CmsSetting::get('mission_content', 'about', ''),
            'about_why_title' => CmsSetting::get('why_title', 'about', ''),
            'about_why_items' => CmsSetting::get('why_items', 'about', []),

            // Guide Page
            'guide_title' => CmsSetting::get('title', 'guide', ''),
            'guide_subtitle' => CmsSetting::get('subtitle', 'guide', ''),
            'guide_hero_icon' => CmsSetting::get('hero_icon', 'guide', ''),
            'guide_steps_title' => CmsSetting::get('steps_title', 'guide', ''),
            'guide_steps' => CmsSetting::get('steps', 'guide', []),
            'guide_field_types' => CmsSetting::get('field_types', 'guide', []),
            'guide_relations' => CmsSetting::get('relations', 'guide', []),
            'guide_best_practices' => CmsSetting::get('best_practices', 'guide', []),
            'guide_help_title' => CmsSetting::get('help_title', 'guide', ''),
            'guide_help_content' => CmsSetting::get('help_content', 'guide', ''),

            // Contact Page
            'contact_title' => CmsSetting::get('title', 'contact', ''),
            'contact_subtitle' => CmsSetting::get('subtitle', 'contact', ''),
            'contact_heading' => CmsSetting::get('heading', 'contact', ''),
            'contact_intro' => CmsSetting::get('intro', 'contact', ''),
            'contact_info_items' => CmsSetting::get('info_items', 'contact', []),
            'contact_form_title' => CmsSetting::get('form_title', 'contact', ''),
            'contact_form_subtitle' => CmsSetting::get('form_subtitle', 'contact', ''),

            // Legal Pages
            'privacy_title' => CmsSetting::get('title', 'privacy', ''),
            'privacy_subtitle' => CmsSetting::get('subtitle', 'privacy', ''),
            'privacy_content' => CmsSetting::get('content', 'privacy', ''),
            'terms_title' => CmsSetting::get('title', 'terms', ''),
            'terms_subtitle' => CmsSetting::get('subtitle', 'terms', ''),
            'terms_content' => CmsSetting::get('content', 'terms', ''),

            // Auth Section
            'auth_header_button_text' => CmsSetting::get('header_button_text', 'auth', ''),
            'auth_login_layout' => CmsSetting::get('login_layout', 'auth', 'simple'),

            // Login
            'auth_login_heading' => CmsSetting::get('login_heading', 'auth', ''),
            'auth_login_description' => CmsSetting::get('login_description', 'auth', ''),
            'auth_login_right_heading' => CmsSetting::get('login_right_heading', 'auth', ''),
            'auth_login_right_description' => CmsSetting::get('login_right_description', 'auth', ''),

            // Register
            'auth_register_heading' => CmsSetting::get('register_heading', 'auth', ''),
            'auth_register_description' => CmsSetting::get('register_description', 'auth', ''),
            'auth_register_right_heading' => CmsSetting::get('register_right_heading', 'auth', ''),
            'auth_register_right_description' => CmsSetting::get('register_right_description', 'auth', ''),

            // Forgot Password
            'auth_forgot_heading' => CmsSetting::get('forgot_heading', 'auth', ''),
            'auth_forgot_description' => CmsSetting::get('forgot_description', 'auth', ''),
            'auth_forgot_right_heading' => CmsSetting::get('forgot_right_heading', 'auth', ''),
            'auth_forgot_right_description' => CmsSetting::get('forgot_right_description', 'auth', ''),

            // Reset Password
            'auth_reset_heading' => CmsSetting::get('reset_heading', 'auth', ''),
            'auth_reset_description' => CmsSetting::get('reset_description', 'auth', ''),
            'auth_reset_right_heading' => CmsSetting::get('reset_right_heading', 'auth', ''),
            'auth_reset_right_description' => CmsSetting::get('reset_right_description', 'auth', ''),

            // Footer Settings
            'footer_description' => CmsSetting::get('description', 'footer', ''),
            'footer_copyright_text' => CmsSetting::get('copyright_text', 'footer', ''),
            'footer_copyright_pages' => CmsSetting::get('copyright_pages', 'footer', []),
            'footer_social_icons' => CmsSetting::get('social_icons', 'footer', []),

            // Menu Previews (computed)
            'header_menus_preview' => $this->getHeaderMenusPreview(),
            'footer_menus_preview' => $this->getFooterMenusPreview(),
        ];

        $this->form->fill(['data' => $this->data]);
    }

    private function getHeaderMenusPreview(): string
    {
        $menus = Menu::getHeaderMenus();
        if ($menus->isEmpty()) {
            return 'No header menus created yet.';
        }
        return $menus->map(fn ($m) => ($m->is_active ? '✓' : '✗') . ' ' . $m->page_name . ' (' . $m->page_type . ')')->join("\n");
    }

    private function getFooterMenusPreview(): string
    {
        $menus = Menu::getFooterMenus();
        if ($menus->isEmpty()) {
            return 'No footer menus created yet.';
        }
        return $menus->map(fn ($m) => ($m->is_active ? '✓' : '✗') . ' ' . $m->page_name . ' (' . $m->page_type . ')')->join("\n");
    }

    private function getActiveTab(): int
    {
        $tabParam = request()->query('tab');

        return match ($tabParam) {
            'menu-management' => 12,
            'footer-settings' => 11,
            'auth-section' => 10,
            'legal-pages' => 9,
            'contact-page' => 8,
            'guide-page' => 7,
            'about-page' => 6,
            'cta-section' => 5,
            'how-it-works' => 4,
            'features' => 3,
            'hero-section' => 2,
            default => 1,
        };
    }

    protected function getFormSchema(): array
    {
        return [
            Tabs::make('Landing Page Settings')
                ->tabs([
                    // ============================================
                    // Tab 1: Global Settings
                    // ============================================
                    Tab::make(__('Global Settings'))
                        ->icon(Heroicon::PaintBrush)
                        ->schema([
                            Grid::make(2)
                                ->statePath('data')
                                ->schema([
                                    ColorPicker::make('main_bg_color')
                                        ->label(__('Main Background Color'))
                                        ->helperText(__('Used for hero and CTA sections'))
                                        ->required(),

                                    ColorPicker::make('light_bg_color')
                                        ->label(__('Light Background Color'))
                                        ->helperText(__('Used for features and other sections'))
                                        ->required(),

                                    ColorPicker::make('text_color')
                                        ->label(__('Text Color'))
                                        ->required(),

                                    ColorPicker::make('heading_color')
                                        ->label(__('Heading Color'))
                                        ->required(),

                                    Select::make('font_family')
                                        ->label(__('Font Family'))
                                        ->options([
                                            'DM Sans' => 'DM Sans',
                                            'Inter' => 'Inter',
                                            'Roboto' => 'Roboto',
                                            'Open Sans' => 'Open Sans',
                                            'Lato' => 'Lato',
                                            'Montserrat' => 'Montserrat',
                                            'Poppins' => 'Poppins',
                                            'Nunito' => 'Nunito',
                                            'Source Sans Pro' => 'Source Sans Pro',
                                            'Raleway' => 'Raleway',
                                        ])
                                        ->searchable()
                                        ->required(),
                                ]),
                        ]),

                    // ============================================
                    // Tab 2: Hero Section
                    // ============================================
                    Tab::make(__('Hero Section'))
                        ->icon(Heroicon::Home)
                        ->schema([
                            Section::make(__('Hero Image'))
                                ->statePath('data')
                                ->schema([
                                    FileUpload::make('hero_image')
                                        ->label(__('Hero Background Image'))
                                        ->helperText(__('Upload an image for the hero section background (optional)'))
                                        ->image()
                                        ->imageEditor()
                                        ->disk('public')
                                        ->directory('hero')
                                        ->visibility('public')
                                        ->preserveFilenames(false)
                                        ->maxSize(2048)
                                        ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                                        ->downloadable()
                                        ->openable()
                                        ->columnSpanFull(),
                                ]),

                            Section::make(__('Badge & Title'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('hero_badge_text')
                                        ->label(__('Badge Text'))
                                        ->placeholder('⚡ Build faster with Laravel & Filament'),

                                    TextInput::make('hero_title')
                                        ->label(__('Title'))
                                        ->required(),

                                    Textarea::make('hero_description')
                                        ->label(__('Description'))
                                        ->rows(3)
                                        ->required(),
                                ]),

                            Section::make(__('Buttons'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('hero_primary_button_text')
                                        ->label(__('Primary Button Text'))
                                        ->required(),

                                    TextInput::make('hero_primary_button_url')
                                        ->label(__('Primary Button URL'))
                                        ->required(),

                                    TextInput::make('hero_secondary_button_text')
                                        ->label(__('Secondary Button Text'))
                                        ->required(),

                                    TextInput::make('hero_secondary_button_url')
                                        ->label(__('Secondary Button URL'))
                                        ->required(),
                                ])
                                ->columns(2),

                            Section::make(__('Stats'))
                                ->statePath('data')
                                ->schema([
                                    Repeater::make('hero_stats')
                                        ->label(__('Statistics'))
                                        ->schema([
                                            TextInput::make('label')
                                                ->label(__('Label'))
                                                ->placeholder('10x')
                                                ->required(),

                                            TextInput::make('text')
                                                ->label(__('Text'))
                                                ->placeholder('Faster Development')
                                                ->required(),
                                        ])
                                        ->columns(2)
                                        ->addActionLabel(__('Add Stat'))
                                        ->default([])
                                        ->collapsible(),
                                ]),
                        ]),

                    // ============================================
                    // Tab 3: Features Section
                    // ============================================
                    Tab::make(__('Features'))
                        ->icon(Heroicon::Squares2x2)
                        ->schema([
                            Section::make(__('Feature Cards'))
                                ->description(__('Manage the feature cards displayed on the landing page'))
                                ->statePath('data')
                                ->schema([
                                    Repeater::make('features')
                                        ->label(__('Features'))
                                        ->schema([
                                            Textarea::make('icon')
                                                ->label(__('Icon SVG Path'))
                                                ->helperText(__('Paste the SVG path element only'))
                                                ->rows(3)
                                                ->required(),

                                            TextInput::make('title')
                                                ->label(__('Title'))
                                                ->required(),

                                            Textarea::make('description')
                                                ->label(__('Description'))
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(1)
                                        ->addActionLabel(__('Add Feature'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                ]),
                        ]),

                    // ============================================
                    // Tab 4: How It Works Section
                    // ============================================
                    Tab::make(__('How It Works'))
                        ->icon(Heroicon::ListBullet)
                        ->schema([
                            Section::make(__('Steps'))
                                ->description(__('Manage the step-by-step process'))
                                ->statePath('data')
                                ->schema([
                                    Repeater::make('steps')
                                        ->label(__('Steps'))
                                        ->schema([
                                            TextInput::make('number')
                                                ->label(__('Step Number'))
                                                ->numeric()
                                                ->required(),

                                            TextInput::make('title')
                                                ->label(__('Title'))
                                                ->required(),

                                            Textarea::make('description')
                                                ->label(__('Description'))
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel(__('Add Step'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => ($state['number'] ?? '') . '. ' . ($state['title'] ?? '')),
                                ]),
                        ]),

                    // ============================================
                    // Tab 5: CTA Section
                    // ============================================
                    Tab::make(__('CTA Section'))
                        ->icon(Heroicon::Megaphone)
                        ->schema([
                            Section::make(__('Call to Action'))
                                ->description(__('Configure the bottom CTA section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('cta_title')
                                        ->label(__('Title'))
                                        ->required(),

                                    Textarea::make('cta_subtitle')
                                        ->label(__('Subtitle'))
                                        ->rows(2)
                                        ->required(),

                                    TextInput::make('cta_button_text')
                                        ->label(__('Button Text'))
                                        ->required(),

                                    TextInput::make('cta_button_url')
                                        ->label(__('Button URL'))
                                        ->required(),
                                ]),
                        ]),

                    // ============================================
                    // Tab 6: About Page
                    // ============================================
                    Tab::make(__('About Page'))
                        ->icon(Heroicon::InformationCircle)
                        ->schema([
                            Section::make(__('Hero Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('about_title')
                                        ->label(__('Page Title'))
                                        ->placeholder('About Us'),

                                    Textarea::make('about_subtitle')
                                        ->label(__('Subtitle'))
                                        ->rows(2),
                                ]),

                            Section::make(__('Story Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('about_story_title')
                                        ->label(__('Story Title'))
                                        ->placeholder('Our Story'),

                                    Textarea::make('about_story_content')
                                        ->label(__('Story Content'))
                                        ->rows(3),
                                ]),

                            Section::make(__('Mission Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('about_mission_title')
                                        ->label(__('Mission Title'))
                                        ->placeholder('Our Mission'),

                                    Textarea::make('about_mission_content')
                                        ->label(__('Mission Content'))
                                        ->rows(3),
                                ]),

                            Section::make(__('Why Choose Us Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('about_why_title')
                                        ->label(__('Section Title'))
                                        ->placeholder('Why Choose Us?'),

                                    Repeater::make('about_why_items')
                                        ->label(__('Why Items'))
                                        ->schema([
                                            TextInput::make('title')
                                                ->label(__('Title'))
                                                ->required(),

                                            Textarea::make('description')
                                                ->label(__('Description'))
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(1)
                                        ->addActionLabel(__('Add Item'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                ]),
                        ]),

                    // ============================================
                    // Tab 7: Guide Page
                    // ============================================
                    Tab::make(__('Guide Page'))
                        ->icon(Heroicon::BookOpen)
                        ->schema([
                            Section::make(__('Hero Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('guide_title')
                                        ->label(__('Page Title'))
                                        ->placeholder('CRUD Builder Guide'),

                                    Textarea::make('guide_subtitle')
                                        ->label(__('Subtitle'))
                                        ->rows(2),

                                    Textarea::make('guide_hero_icon')
                                        ->label(__('Hero Icon SVG Path'))
                                        ->helperText(__('SVG path element for the hero icon'))
                                        ->rows(2),
                                ]),

                            Section::make(__('Getting Started Steps'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('guide_steps_title')
                                        ->label(__('Section Title'))
                                        ->placeholder('Getting Started'),

                                    Repeater::make('guide_steps')
                                        ->label(__('Steps'))
                                        ->schema([
                                            TextInput::make('number')
                                                ->label(__('Step Number'))
                                                ->numeric()
                                                ->required(),

                                            TextInput::make('title')
                                                ->label(__('Title'))
                                                ->required(),

                                            Textarea::make('description')
                                                ->label(__('Description'))
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel(__('Add Step'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => ($state['number'] ?? '') . '. ' . ($state['title'] ?? '')),
                                ]),

                            Section::make(__('Field Types'))
                                ->statePath('data')
                                ->schema([
                                    Repeater::make('guide_field_types')
                                        ->label(__('Field Types'))
                                        ->schema([
                                            Textarea::make('icon')
                                                ->label(__('Icon SVG Path'))
                                                ->rows(2)
                                                ->required(),

                                            TextInput::make('title')
                                                ->label(__('Title'))
                                                ->required(),

                                            Textarea::make('description')
                                                ->label(__('Description'))
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(1)
                                        ->addActionLabel(__('Add Field Type'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                ]),

                            Section::make(__('Relationships'))
                                ->statePath('data')
                                ->schema([
                                    Repeater::make('guide_relations')
                                        ->label(__('Relationships'))
                                        ->schema([
                                            TextInput::make('type')
                                                ->label(__('Relationship Type'))
                                                ->placeholder('BelongsTo')
                                                ->required(),

                                            TextInput::make('title')
                                                ->label(__('Title'))
                                                ->required(),

                                            Textarea::make('description')
                                                ->label(__('Description'))
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(1)
                                        ->addActionLabel(__('Add Relationship'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['type'] ?? null),
                                ]),

                            Section::make(__('Best Practices'))
                                ->statePath('data')
                                ->schema([
                                    Repeater::make('guide_best_practices')
                                        ->label(__('Best Practices'))
                                        ->schema([
                                            Textarea::make('text')
                                                ->label(__('Practice Text'))
                                                ->rows(2)
                                                ->required(),
                                        ])
                                        ->columns(1)
                                        ->addActionLabel(__('Add Practice'))
                                        ->default([])
                                        ->collapsible(),
                                ]),

                            Section::make(__('Help Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('guide_help_title')
                                        ->label(__('Help Section Title'))
                                        ->placeholder('Need Help?'),

                                    Textarea::make('guide_help_content')
                                        ->label(__('Help Content'))
                                        ->rows(2),
                                ]),
                        ]),

                    // ============================================
                    // Tab 8: Contact Page
                    // ============================================
                    Tab::make(__('Contact Page'))
                        ->icon(Heroicon::Envelope)
                        ->schema([
                            Section::make(__('Hero Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('contact_title')
                                        ->label(__('Page Title'))
                                        ->placeholder('Get in Touch'),

                                    Textarea::make('contact_subtitle')
                                        ->label(__('Subtitle'))
                                        ->rows(2),
                                ]),

                            Section::make(__('Contact Info Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('contact_heading')
                                        ->label(__('Heading'))
                                        ->placeholder("Let's Connect"),

                                    Textarea::make('contact_intro')
                                        ->label(__('Introduction Text'))
                                        ->rows(2),

                                    Repeater::make('contact_info_items')
                                        ->label(__('Contact Info Items'))
                                        ->schema([
                                            TextInput::make('icon')
                                                ->label(__('Icon Type'))
                                                ->helperText(__('Use: mail, location, clock, or custom'))
                                                ->required(),

                                            TextInput::make('title')
                                                ->label(__('Title'))
                                                ->required(),

                                            Textarea::make('line1')
                                                ->label(__('Line 1'))
                                                ->rows(1)
                                                ->required(),

                                            Textarea::make('line2')
                                                ->label(__('Line 2 (Optional)'))
                                                ->rows(1),
                                        ])
                                        ->columns(1)
                                        ->addActionLabel(__('Add Contact Info'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null),
                                ]),

                            Section::make(__('Contact Form'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('contact_form_title')
                                        ->label(__('Form Title'))
                                        ->placeholder('Send us a Message'),

                                    Textarea::make('contact_form_subtitle')
                                        ->label(__('Form Subtitle'))
                                        ->rows(2),
                                ]),
                        ]),

                    // ============================================
                    // Tab 9: Legal Pages
                    // ============================================
                    Tab::make(__('Legal Pages'))
                        ->icon(Heroicon::ShieldCheck)
                        ->schema([
                            Section::make(__('Privacy Policy'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('privacy_title')
                                        ->label(__('Page Title'))
                                        ->placeholder('Privacy Policy'),

                                    Textarea::make('privacy_subtitle')
                                        ->label(__('Subtitle'))
                                        ->placeholder('Your privacy is important to us')
                                        ->rows(2),

                                    RichEditor::make('privacy_content')
                                        ->label(__('Page Content'))
                                        ->placeholder('Enter your privacy policy content here...')
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
                                ]),

                            Section::make(__('Terms & Conditions'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('terms_title')
                                        ->label(__('Page Title'))
                                        ->placeholder('Terms & Conditions'),

                                    Textarea::make('terms_subtitle')
                                        ->label(__('Subtitle'))
                                        ->placeholder('Please read these terms carefully before using our service')
                                        ->rows(2),

                                    RichEditor::make('terms_content')
                                        ->label(__('Page Content'))
                                        ->placeholder('Enter your terms and conditions content here...')
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
                                ]),
                        ]),

                    // ============================================
                    // Tab 10: Auth Section
                    // ============================================
                    Tab::make(__('Auth Section'))
                        ->icon(Heroicon::LockClosed)
                        ->schema([
                            Section::make(__('Header Button'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('auth_header_button_text')
                                        ->label(__('Button Text'))
                                        ->placeholder('Get Started')
                                        ->helperText(__('Text displayed on the header "Get Started" button')),
                                ]),

                            Section::make(__('Auth Pages Layout'))
                                ->statePath('data')
                                ->schema([
                                    Select::make('auth_login_layout')
                                        ->label(__('Layout Style for All Auth Pages'))
                                        ->options([
                                            'simple' => __('Simple (centered card)'),
                                            'advanced' => __('Advanced (split-screen design)'),
                                        ])
                                        ->default('simple')
                                        ->live()
                                        ->required(),
                                ]),

                            Section::make(__('Login Page Content'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('auth_login_heading')
                                        ->label(__('Form Heading'))
                                        ->placeholder('Sign In'),

                                    Textarea::make('auth_login_description')
                                        ->label(__('Form Description'))
                                        ->placeholder('Enter your credentials to access your account')
                                        ->rows(2),

                                    TextInput::make('auth_login_right_heading')
                                        ->label(__('Right Side Heading'))
                                        ->placeholder('Secure & Reliable')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),

                                    Textarea::make('auth_login_right_description')
                                        ->label(__('Right Side Description'))
                                        ->placeholder('Your data is protected...')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->rows(2)
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),
                                ]),

                            Section::make(__('Register Page Content'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('auth_register_heading')
                                        ->label(__('Form Heading'))
                                        ->placeholder('Create Account'),

                                    Textarea::make('auth_register_description')
                                        ->label(__('Form Description'))
                                        ->placeholder('Fill in your details to create a new account')
                                        ->rows(2),

                                    TextInput::make('auth_register_right_heading')
                                        ->label(__('Right Side Heading'))
                                        ->placeholder('Join Us Today')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),

                                    Textarea::make('auth_register_right_description')
                                        ->label(__('Right Side Description'))
                                        ->placeholder('Create your account and start building...')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->rows(2)
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),
                                ]),

                            Section::make(__('Forgot Password Content'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('auth_forgot_heading')
                                        ->label(__('Form Heading'))
                                        ->placeholder('Reset Password'),

                                    Textarea::make('auth_forgot_description')
                                        ->label(__('Form Description'))
                                        ->placeholder('Enter your email to receive a password reset link')
                                        ->rows(2),

                                    TextInput::make('auth_forgot_right_heading')
                                        ->label(__('Right Side Heading'))
                                        ->placeholder('Recover Access')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),

                                    Textarea::make('auth_forgot_right_description')
                                        ->label(__('Right Side Description'))
                                        ->placeholder('No worries. Enter your email...')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->rows(2)
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),
                                ]),

                            Section::make(__('Reset Password Content'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('auth_reset_heading')
                                        ->label(__('Form Heading'))
                                        ->placeholder('Reset Password'),

                                    Textarea::make('auth_reset_description')
                                        ->label(__('Form Description'))
                                        ->placeholder('Enter your new password below')
                                        ->rows(2),

                                    TextInput::make('auth_reset_right_heading')
                                        ->label(__('Right Side Heading'))
                                        ->placeholder('Set New Password')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),

                                    Textarea::make('auth_reset_right_description')
                                        ->label(__('Right Side Description'))
                                        ->placeholder('Choose a strong password...')
                                        ->helperText(__('Shown in Advanced layout'))
                                        ->rows(2)
                                        ->hidden(fn (Get $get) => $get('auth_login_layout') !== 'advanced'),
                                ]),
                        ]),

                    // ============================================
                    // Tab 11: Footer Settings
                    // ============================================
                    Tab::make(__('Footer Settings'))
                        ->icon(Heroicon::SquaresPlus)
                        ->schema([
                            Section::make(__('Brand Description'))
                                ->statePath('data')
                                ->schema([
                                    Textarea::make('footer_description')
                                        ->label(__('Footer Description'))
                                        ->placeholder('Build faster with Laravel & Filament. Generate production-ready CRUD operations in seconds.')
                                        ->rows(3),
                                ]),

                            Section::make(__('Copyright Section'))
                                ->statePath('data')
                                ->schema([
                                    TextInput::make('footer_copyright_text')
                                        ->label(__('Copyright Text'))
                                        ->placeholder('© 2026 Craft Laravel. All rights reserved.')
                                        ->helperText(__('Use :year for current year auto-replacement')),

                                    Select::make('footer_copyright_pages')
                                        ->label(__('Copyright Page Links'))
                                        ->helperText(__('Select pages to display as links in the copyright bar'))
                                        ->multiple()
                                        ->options(function () {
                                            return Menu::where('is_active', true)
                                                ->orderBy('page_name')
                                                ->pluck('page_name', 'id')
                                                ->toArray();
                                        })
                                        ->searchable(),
                                ]),

                            Section::make(__('Social Media Icons'))
                                ->statePath('data')
                                ->schema([
                                    Repeater::make('footer_social_icons')
                                        ->label(__('Social Icons'))
                                        ->schema([
                                            TextInput::make('platform')
                                                ->label(__('Platform Name'))
                                                ->placeholder('Facebook, Twitter, LinkedIn, etc.'),

                                            TextInput::make('url')
                                                ->label(__('Profile URL'))
                                                ->placeholder('https://facebook.com/yourpage'),

                                            Textarea::make('icon_svg')
                                                ->label(__('Icon SVG Path'))
                                                ->placeholder('<path d="..."/>')
                                                ->helperText(__('Paste only the SVG path element'))
                                                ->rows(2),

                                            \Filament\Forms\Components\Toggle::make('is_active')
                                                ->label(__('Active'))
                                                ->default(true),
                                        ])
                                        ->columns(2)
                                        ->addActionLabel(__('Add Social Icon'))
                                        ->default([])
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => ($state['platform'] ?? '') . ' (' . ($state['is_active'] ? 'Active' : 'Inactive') . ')'),
                                ]),
                        ]),

                    // ============================================
                    // Tab 11: Menu Management
                    // ============================================
                    Tab::make(__('Menu Management'))
                        ->icon(Heroicon::Bars3)
                        ->schema([
                            Section::make(__('Manage Header & Footer Menus'))
                                ->description(__('Create and manage menu items for header and footer navigation'))
                                ->headerActions([
                                    Action::make('manageMenus')
                                        ->label(__('Manage Menus'))
                                        ->icon('heroicon-o-arrow-top-right-on-square')
                                        ->url(fn () => \App\Filament\Resources\Menus\MenuResource::getUrl())
                                        ->openUrlInNewTab(false),
                                ])
                                ->schema([
                                    Grid::make(2)
                                        ->statePath('data')
                                        ->schema([
                                            Section::make(__('Header Menus'))
                                                ->schema([
                                                    Textarea::make('header_menus_preview')
                                                        ->label(false)
                                                        ->rows(6)
                                                        ->disabled()
                                                        ->dehydrated(false)
                                                ]),

                                            Section::make(__('Footer Menus'))
                                                ->schema([
                                                    Textarea::make('footer_menus_preview')
                                                        ->label(false)
                                                        ->rows(6)
                                                        ->disabled()
                                                        ->dehydrated(false)
                                                ]),
                                        ]),
                                ]),
                        ]),
                ])
                ->activeTab($this->getActiveTab())
                ->columnSpanFull(),
        ];
    }

    public function save(): void
    {
        if (! is_dir(storage_path('app/uploads/hero'))) {
            mkdir(storage_path('app/uploads/hero'), 0755, true);
        }

        $data = $this->form->getState()['data'];

        try {
            // ============================================
            // Save Global Settings
            // ============================================
            CmsSetting::set('main_bg_color', $data['main_bg_color'], 'global', 'color');
            CmsSetting::set('light_bg_color', $data['light_bg_color'], 'global', 'color');
            CmsSetting::set('text_color', $data['text_color'], 'global', 'color');
            CmsSetting::set('heading_color', $data['heading_color'], 'global', 'color');
            CmsSetting::set('font_family', $data['font_family'], 'global', 'text');

            // ============================================
            // Save Hero Settings
            // ============================================
            CmsSetting::set('badge_text', $data['hero_badge_text'] ?? '', 'hero', 'text');
            CmsSetting::set('title', $data['hero_title'] ?? '', 'hero', 'text');
            CmsSetting::set('description', $data['hero_description'] ?? '', 'hero', 'text');
            CmsSetting::set('primary_button_text', $data['hero_primary_button_text'] ?? '', 'hero', 'text');
            CmsSetting::set('primary_button_url', $data['hero_primary_button_url'] ?? '', 'hero', 'text');
            CmsSetting::set('secondary_button_text', $data['hero_secondary_button_text'] ?? '', 'hero', 'text');
            CmsSetting::set('secondary_button_url', $data['hero_secondary_button_url'] ?? '', 'hero', 'text');
            CmsSetting::set('stats', $data['hero_stats'] ?? [], 'hero', 'json');

            // Save hero image path
            if (!empty($data['hero_image'])) {
                // Get the first image path from the array
                $imagePath = is_array($data['hero_image']) ? $data['hero_image'][0] : $data['hero_image'];
                CmsSetting::set('image', $imagePath, 'hero', 'text');
            } else {
                CmsSetting::where('key', 'image')->where('group', 'hero')->delete();
            }

            // ============================================
            // Save Features
            // ============================================
            CmsSetting::set('items', $data['features'] ?? [], 'features', 'json');

            // ============================================
            // Save Steps
            // ============================================
            CmsSetting::set('items', $data['steps'] ?? [], 'steps', 'json');

            // ============================================
            // Save CTA Settings
            // ============================================
            CmsSetting::set('title', $data['cta_title'] ?? '', 'cta', 'text');
            CmsSetting::set('subtitle', $data['cta_subtitle'] ?? '', 'cta', 'text');
            CmsSetting::set('button_text', $data['cta_button_text'] ?? '', 'cta', 'text');
            CmsSetting::set('button_url', $data['cta_button_url'] ?? '', 'cta', 'text');

            // ============================================
            // Save About Page Settings
            // ============================================
            CmsSetting::set('title', $data['about_title'] ?? '', 'about', 'text');
            CmsSetting::set('subtitle', $data['about_subtitle'] ?? '', 'about', 'text');
            CmsSetting::set('story_title', $data['about_story_title'] ?? '', 'about', 'text');
            CmsSetting::set('story_content', $data['about_story_content'] ?? '', 'about', 'text');
            CmsSetting::set('mission_title', $data['about_mission_title'] ?? '', 'about', 'text');
            CmsSetting::set('mission_content', $data['about_mission_content'] ?? '', 'about', 'text');
            CmsSetting::set('why_title', $data['about_why_title'] ?? '', 'about', 'text');
            CmsSetting::set('why_items', $data['about_why_items'] ?? [], 'about', 'json');

            // ============================================
            // Save Guide Page Settings
            // ============================================
            CmsSetting::set('title', $data['guide_title'] ?? '', 'guide', 'text');
            CmsSetting::set('subtitle', $data['guide_subtitle'] ?? '', 'guide', 'text');
            CmsSetting::set('hero_icon', $data['guide_hero_icon'] ?? '', 'guide', 'text');
            CmsSetting::set('steps_title', $data['guide_steps_title'] ?? '', 'guide', 'text');
            CmsSetting::set('steps', $data['guide_steps'] ?? [], 'guide', 'json');
            CmsSetting::set('field_types', $data['guide_field_types'] ?? [], 'guide', 'json');
            CmsSetting::set('relations', $data['guide_relations'] ?? [], 'guide', 'json');
            CmsSetting::set('best_practices', $data['guide_best_practices'] ?? [], 'guide', 'json');
            CmsSetting::set('help_title', $data['guide_help_title'] ?? '', 'guide', 'text');
            CmsSetting::set('help_content', $data['guide_help_content'] ?? '', 'guide', 'text');

            // ============================================
            // Save Contact Page Settings
            // ============================================
            CmsSetting::set('title', $data['contact_title'] ?? '', 'contact', 'text');
            CmsSetting::set('subtitle', $data['contact_subtitle'] ?? '', 'contact', 'text');
            CmsSetting::set('heading', $data['contact_heading'] ?? '', 'contact', 'text');
            CmsSetting::set('intro', $data['contact_intro'] ?? '', 'contact', 'text');
            CmsSetting::set('info_items', $data['contact_info_items'] ?? [], 'contact', 'json');
            CmsSetting::set('form_title', $data['contact_form_title'] ?? '', 'contact', 'text');
            CmsSetting::set('form_subtitle', $data['contact_form_subtitle'] ?? '', 'contact', 'text');

            // ============================================
            // Save Legal Pages Settings
            // ============================================
            CmsSetting::set('title', $data['privacy_title'] ?? '', 'privacy', 'text');
            CmsSetting::set('subtitle', $data['privacy_subtitle'] ?? '', 'privacy', 'text');
            CmsSetting::set('content', $data['privacy_content'] ?? '', 'privacy', 'text');
            CmsSetting::set('title', $data['terms_title'] ?? '', 'terms', 'text');
            CmsSetting::set('subtitle', $data['terms_subtitle'] ?? '', 'terms', 'text');
            CmsSetting::set('content', $data['terms_content'] ?? '', 'terms', 'text');

            // ============================================
            // Save Auth Section
            // ============================================
            CmsSetting::set('header_button_text', $data['auth_header_button_text'] ?? '', 'auth', 'text');
            CmsSetting::set('login_layout', $data['auth_login_layout'] ?? 'simple', 'auth', 'text');

            // Login
            CmsSetting::set('login_heading', $data['auth_login_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('login_description', $data['auth_login_description'] ?? '', 'auth', 'text');
            CmsSetting::set('login_right_heading', $data['auth_login_right_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('login_right_description', $data['auth_login_right_description'] ?? '', 'auth', 'text');

            // Register
            CmsSetting::set('register_heading', $data['auth_register_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('register_description', $data['auth_register_description'] ?? '', 'auth', 'text');
            CmsSetting::set('register_right_heading', $data['auth_register_right_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('register_right_description', $data['auth_register_right_description'] ?? '', 'auth', 'text');

            // Forgot Password
            CmsSetting::set('forgot_heading', $data['auth_forgot_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('forgot_description', $data['auth_forgot_description'] ?? '', 'auth', 'text');
            CmsSetting::set('forgot_right_heading', $data['auth_forgot_right_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('forgot_right_description', $data['auth_forgot_right_description'] ?? '', 'auth', 'text');

            // Reset Password
            CmsSetting::set('reset_heading', $data['auth_reset_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('reset_description', $data['auth_reset_description'] ?? '', 'auth', 'text');
            CmsSetting::set('reset_right_heading', $data['auth_reset_right_heading'] ?? '', 'auth', 'text');
            CmsSetting::set('reset_right_description', $data['auth_reset_right_description'] ?? '', 'auth', 'text');

            // ============================================
            // Save Footer Settings
            // ============================================
            CmsSetting::set('description', $data['footer_description'] ?? '', 'footer', 'text');
            CmsSetting::set('copyright_text', $data['footer_copyright_text'] ?? '', 'footer', 'text');
            CmsSetting::set('copyright_pages', $data['footer_copyright_pages'] ?? [], 'footer', 'json');
            CmsSetting::set('social_icons', $data['footer_social_icons'] ?? [], 'footer', 'json');

            Notification::make()
                ->success()
                ->title(__('Landing page settings saved successfully!'))
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title(__('Error saving settings'))
                ->body($e->getMessage())
                ->send();
        }
    }
}
