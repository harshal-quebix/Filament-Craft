<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class SeoSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.settings.pages.seo-settings';
    protected static ?string $cluster = SettingsCluster::class;
    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('SEO Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('SEO Settings');
    }
    protected static ?int $navigationSort = 5;

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public ?array $data = [];
    public $meta_image = [];

    public function mount(): void
    {
        $existingMetaImage = Setting::where('key', 'meta_image')->where('created_by', Auth::id())->value('value');
        $this->meta_image = $existingMetaImage ? [$existingMetaImage] : [];

        $settings = Setting::where('created_by', Auth::id())->pluck('value', 'key')->toArray();

        // Set defaults for missing settings
        $defaults = [
            'meta_title' => __('Craft Laravel - Modern Web Development'),
            'meta_keywords' => __('laravel, filament, web development, php'),
            'meta_description' => __('A powerful web application built with Laravel and Filament for modern web development.'),
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultValue;
            }
        }

        $this->data = $settings;
        $this->form->fill([
            'data' => $this->data,
            'meta_image' => $this->meta_image
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            ComponentsGrid::make(2)
                ->statePath('data')
                ->schema([
                    TextInput::make('meta_title')
                        ->label(__('Meta Title'))
                        ->placeholder(__('Your Website Title'))
                        ->default(__('Craft Laravel - Modern Web Development'))
                        ->markAsRequired()
                        ->minLength(10)
                        ->maxLength(60)
                        ->rules(['required', 'string', 'min:10', 'max:60']),
                    TextInput::make('meta_keywords')
                        ->label(__('Meta Keywords'))
                        ->placeholder(__('keyword1, keyword2, keyword3'))
                        ->default(__('laravel, filament, web development, php'))
                        ->markAsRequired()
                        ->minLength(5)
                        ->maxLength(255)
                        ->rules(['required', 'string', 'min:5', 'max:255']),
                    Textarea::make('meta_description')
                        ->label(__('Meta Description'))
                        ->placeholder(__('Enter meta description for your website'))
                        ->rows(3)
                        ->default(__('A powerful web application built with Laravel and Filament for modern web development.'))
                        ->markAsRequired()
                        ->minLength(50)
                        ->maxLength(160)
                        ->rules(['required', 'string', 'min:50', 'max:160'])
                        ->columnSpanFull(),
                    FileUpload::make('meta_image')
                        ->label(__('Meta Image'))
                        ->disk('public')
                        ->directory('seo')
                        ->image()
                        ->imagePreviewHeight('250'),
                ]),
        ];
    }

    public function save(): void
    {
        if (! is_dir(storage_path('app/uploads/seo'))) {
            mkdir(storage_path('app/uploads/seo'), 0755, true);
        }

        // Validate form
        $this->form->validate();

        $formData = $this->form->getState();
        $data = $formData['data'] ?? [];

        // Server-side validation
        if (isset($data['meta_title'])) {
            $length = strlen($data['meta_title']);
            if ($length < 10 || $length > 60) {
                Notification::make()
                    ->danger()
                    ->title(__('Validation Error'))
                    ->body(__('Meta Title must be between 10 and 60 characters.'))
                    ->send();
                return;
            }
        }

        if (isset($data['meta_keywords'])) {
            $length = strlen($data['meta_keywords']);
            if ($length < 5 || $length > 255) {
                Notification::make()
                    ->danger()
                    ->title(__('Validation Error'))
                    ->body(__('Meta Keywords must be between 5 and 255 characters.'))
                    ->send();
                return;
            }
        }

        if (isset($data['meta_description'])) {
            $length = strlen($data['meta_description']);
            if ($length < 50 || $length > 160) {
                Notification::make()
                    ->danger()
                    ->title(__('Validation Error'))
                    ->body(__('Meta Description must be between 50 and 160 characters.'))
                    ->send();
                return;
            }
        }

        foreach ($data as $key => $value) {
            $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string)$value;

            Setting::updateOrCreate(
                ['key' => $key, 'created_by' => Auth::id()],
                ['value' => $stringValue]
            );
        }

        // Store meta image path in settings
        if (!empty($formData['meta_image'])) {
            $path = is_array($formData['meta_image']) ? $formData['meta_image'][0] : $formData['meta_image'];
            if ($path) {
                Setting::updateOrCreate(
                    ['key' => 'meta_image', 'created_by' => Auth::id()],
                    ['value' => $path]
                );
            }
        } else {
            Setting::where('key', 'meta_image')->where('created_by', Auth::id())->delete();
        }

         Notification::make()
            ->success()
            ->title(__('SEO settings updated successfully!'))
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save SEO Settings'))
                ->action('save')
                ->extraAttributes(['class' => 'mt-5']),
        ];
    }
}
