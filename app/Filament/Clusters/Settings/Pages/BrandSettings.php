<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\ToggleButtons;

class BrandSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.settings.pages.brand-settings';
    protected static ?string $cluster = SettingsCluster::class;
    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Brand Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Brand Settings');
    }
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public ?array $data = [];
    public $logo_dark = [];
    public $logo_light = [];
    public $favicon = [];
    public $auth_page_image = [];

    public function mount(): void
    {

        $settings = Setting::whereIn('key', ['site_title', 'footer_text', 'theme_color', 'logo_dark', 'logo_light', 'favicon', 'auth_page_image'])
                          ->where('created_by', Auth::id())
                          ->pluck('value', 'key')->toArray();

        // Set defaults for missing text settings
        $defaults = [
            'site_title' => __('Craft Laravel'),
            'footer_text' => __('© 2026 Craft Laravel. All rights reserved.'),
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultValue;
            }
        }

        $this->data = $settings;

        // Copy default images to storage disk so FileUpload can preview them
        $defaultFiles = [
            'logo_dark' => ['source' => public_path('default-img/dark_logo.png'), 'dest' => 'brand/default_dark_logo.png'],
            'logo_light' => ['source' => public_path('default-img/light_logo.png'), 'dest' => 'brand/default_light_logo.png'],
            'favicon' => ['source' => public_path('default-img/favicon.png'), 'dest' => 'brand/default_favicon.png'],
        ];

        foreach ($defaultFiles as $key => $fileInfo) {
            if (empty($settings[$key]) && file_exists($fileInfo['source'])) {
                $fullDestPath = storage_path('app/uploads/' . $fileInfo['dest']);
                if (!file_exists($fullDestPath)) {
                    if (!is_dir(dirname($fullDestPath))) {
                        mkdir(dirname($fullDestPath), 0755, true);
                    }
                    copy($fileInfo['source'], $fullDestPath);
                }
                $settings[$key] = $fileInfo['dest'];
            }
        }

        $this->form->fill([
            'logo_dark' => $settings['logo_dark'] ?? null,
            'logo_light' => $settings['logo_light'] ?? null,
            'favicon' => $settings['favicon'] ?? null,
            'auth_page_image' => $settings['auth_page_image'] ?? null,
            'data' => $this->data,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    FileUpload::make('logo_dark')
                        ->label(__('Logo (Dark Mode)'))
                        ->disk('public')
                        ->directory('brand')
                        ->image()
                        ->imagePreviewHeight('250'),
                    FileUpload::make('logo_light')
                        ->label(__('Logo (Light Mode)'))
                        ->disk('public')
                        ->directory('brand')
                        ->image()
                        ->imagePreviewHeight('250'),
                    FileUpload::make('favicon')
                        ->label(__('Favicon'))
                        ->disk('public')
                        ->directory('brand')
                        ->image()
                        ->imagePreviewHeight('250'),
                ]),
            Grid::make(3)
                ->statePath('data')
                ->schema([
                    TextInput::make('site_title')
                        ->label(__('Site Title'))
                        ->default(__('Craft Laravel'))
                        ->markAsRequired()
                        ->minLength(3)
                        ->maxLength(100)
                        ->rules(['required', 'string', 'min:3', 'max:100']),
                    TextInput::make('footer_text')
                        ->label(__('Footer Text'))
                        ->default(__('© 2026 Craft Laravel. All rights reserved.'))
                        ->markAsRequired()
                        ->minLength(3)
                        ->maxLength(255)
                        ->rules(['required', 'string', 'min:3', 'max:255']),
                    ToggleButtons::make('theme_color')
                        ->label(__('Theme Color'))
                        ->options([
                            'slate' => ' ',
                            'gray' => ' ',
                            'zinc' => ' ',
                            'neutral' => ' ',
                            'stone' => ' ',
                            'red' => ' ',
                            'orange' => ' ',
                            'amber' => ' ',
                            'yellow' => ' ',
                            'lime' => ' ',
                            'green' => ' ',
                            'emerald' => ' ',
                            'teal' => ' ',
                            'cyan' => ' ',
                            'sky' => ' ',
                            'blue' => ' ',
                            'indigo' => ' ',
                            'violet' => ' ',
                            'purple' => ' ',
                            'fuchsia' => ' ',
                            'pink' => ' ',
                            'rose' => ' ',
                        ])
                         ->dehydrated(true)
                        ->extraAttributes([
                            'class' => 'theme-color-toggle',
                        ])
                        ->reactive()
                        ->afterStateUpdated(function ($state) {
                            Setting::updateOrCreate(
                                ['key' => 'theme_color', 'created_by' => Auth::id()],
                                ['value' => $state]
                            );
                            $this->dispatch('theme-color-changed', color: $state);
                        })
                        ->inline(),
                ]),

            Grid::make(1)
                ->schema([
                    FileUpload::make('auth_page_image')
                        ->label(__('Auth Page Image'))
                        ->disk('public')
                        ->directory('brand')
                        ->image()
                        ->imagePreviewHeight('250'),
                ]),
        ];
    }

    public function save(): void
    {
        if (! is_dir(storage_path('app/uploads/brand'))) {
            mkdir(storage_path('app/uploads/brand'), 0755, true);
        }

        // Validate form
        $this->form->validate();

        $formData = $this->form->getState();
        $data = $formData['data'] ?? [];
        $files = $formData;

        // Server-side validation
        if (isset($data['site_title'])) {
            if (strlen($data['site_title']) < 3 || strlen($data['site_title']) > 100) {
                Notification::make()
                    ->danger()
                    ->title(__('Validation Error'))
                    ->body(__('Site Title must be between 3 and 100 characters.'))
                    ->send();
                return;
            }
        }

        if (isset($data['footer_text'])) {
            if (strlen($data['footer_text']) < 3 || strlen($data['footer_text']) > 255) {
                Notification::make()
                    ->danger()
                    ->title(__('Validation Error'))
                    ->body(__('Footer Text must be between 3 and 255 characters.'))
                    ->send();
                return;
            }
        }

        // Save text and color settings
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'created_by' => Auth::id()],
                ['value' => $value]
            );
        }

        // Save file paths from uploads
        foreach (['logo_dark', 'logo_light', 'favicon', 'auth_page_image'] as $fileKey) {
            if (!empty($files[$fileKey])) {
                $path = is_array($files[$fileKey]) ? $files[$fileKey][0] : $files[$fileKey];
                if ($path) {
                    // Don't save default image paths to DB
                    if (str_starts_with($path, 'brand/default_')) {
                        continue;
                    }
                    Setting::updateOrCreate(
                        ['key' => $fileKey, 'created_by' => Auth::id()],
                        ['value' => $path]
                    );
                }
            }
            // Note: We do NOT delete settings when files are cleared.
            // The getLogo(), getLogoUrl(), and getSettingImageUrl() helpers
            // already fall back to default images when no setting exists.
        }

        Notification::make()
            ->success()
            ->title(__('Brand settings have been saved successfully!'))
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Brand Settings'))
                ->action('save')
                ->extraAttributes(['class' => 'mt-5']),
        ];
    }

}
