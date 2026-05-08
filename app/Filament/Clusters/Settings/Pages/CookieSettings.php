<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CookieSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.settings.pages.cookie-settings';
    protected static ?string $cluster = SettingsCluster::class;
    public function getTitle(): string
    {
        return __('Cookie Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Cookie Settings');
    }

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $settings = Setting::where('created_by', Auth::id())->pluck('value', 'key')->toArray();

        // Set defaults for missing settings
        $defaults = [
            'enable_logging' => '1',
            'strictly_necessary_cookies' => '1',
            'cookie_title' => __('Cookie Consent'),
            'strictly_cookie_title' => __('Strictly Necessary Cookies'),
            'cookie_description' => __('We use cookies to enhance your browsing experience and provide personalized content.'),
            'strictly_cookie_description' => __('These cookies are essential for the website to function properly.'),
            'contact_us_description' => __('If you have any questions about our cookie policy, please contact us.'),
            'contact_us_url' => 'https://example.com/contact',
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultValue;
            }
        }

        $this->data = $settings;
        $this->form->fill(['data' => $this->data]);
    }

    protected function getFormSchema(): array
    {
        return [
            ComponentsGrid::make(2)
                ->statePath('data')
                ->schema([
                    Toggle::make('enable_logging')
                        ->label(__('Enable Logging'))
                        ->default(true),
                    Toggle::make('strictly_necessary_cookies')
                        ->label(__('Strictly Necessary Cookies'))
                        ->default(true),
                    TextInput::make('cookie_title')
                        ->label(__('Cookie Title'))
                        ->placeholder(__('Cookie Settings'))
                        ->default(__('Cookie Consent'))
                        ->markAsRequired()
                        ->minLength(3)
                        ->maxLength(100)
                        ->rules(['required', 'string', 'min:3', 'max:100']),
                    TextInput::make('strictly_cookie_title')
                        ->label(__('Strictly Cookie Title'))
                        ->placeholder(__('Strictly Necessary Cookies'))
                        ->default(__('Strictly Necessary Cookies'))
                        ->markAsRequired()
                        ->minLength(3)
                        ->maxLength(100)
                        ->rules(['required', 'string', 'min:3', 'max:100']),
                    TextInput::make('cookie_description')
                        ->label(__('Cookie Description'))
                        ->placeholder(__('We use cookies to improve your experience'))
                        ->default(__('We use cookies to enhance your browsing experience and provide personalized content.'))
                        ->markAsRequired()
                        ->minLength(10)
                        ->maxLength(500)
                        ->rules(['required', 'string', 'min:10', 'max:500']),
                    TextInput::make('strictly_cookie_description')
                        ->label(__('Strictly Cookie Description'))
                        ->placeholder(__('These cookies are essential for the website to function'))
                        ->default(__('These cookies are essential for the website to function properly.'))
                        ->markAsRequired()
                        ->minLength(10)
                        ->maxLength(500)
                        ->rules(['required', 'string', 'min:10', 'max:500']),
                    TextInput::make('contact_us_description')
                        ->label(__('Contact Us Description'))
                        ->placeholder(__('For more information about our cookie policy'))
                        ->default(__('If you have any questions about our cookie policy, please contact us.'))
                        ->markAsRequired()
                        ->minLength(10)
                        ->maxLength(500)
                        ->rules(['required', 'string', 'min:10', 'max:500']),
                    TextInput::make('contact_us_url')
                        ->label(__('Contact Us URL'))
                        ->url()
                        ->placeholder(__('https://example.com/contact'))
                        ->default('https://example.com/contact')
                        ->markAsRequired()
                        ->rules(['required', 'url', 'max:255']),
                ]),
        ];
    }

    public function save(): void
    {
        // Validate form
        $this->form->validate();

        $formData = $this->form->getState();
        $data = $formData['data'];

        // Server-side validation
        $textFields = [
            'cookie_title' => ['min' => 3, 'max' => 100],
            'strictly_cookie_title' => ['min' => 3, 'max' => 100],
            'cookie_description' => ['min' => 10, 'max' => 500],
            'strictly_cookie_description' => ['min' => 10, 'max' => 500],
            'contact_us_description' => ['min' => 10, 'max' => 500],
        ];

        foreach ($textFields as $field => $limits) {
            if (isset($data[$field])) {
                $length = strlen($data[$field]);
                if ($length < $limits['min'] || $length > $limits['max']) {
                    Notification::make()
                        ->danger()
                        ->title(__('Validation Error'))
                        ->body(__(ucfirst(str_replace('_', ' ', $field)) . ' must be between ' . $limits['min'] . ' and ' . $limits['max'] . ' characters.'))
                        ->send();
                    return;
                }
            }
        }

        if (isset($data['contact_us_url']) && !filter_var($data['contact_us_url'], FILTER_VALIDATE_URL)) {
            Notification::make()
                ->danger()
                ->title(__('Validation Error'))
                ->body(__('Contact Us URL must be a valid URL.'))
                ->send();
            return;
        }

        foreach ($data as $key => $value) {
            $stringValue = is_bool($value) ? ($value ? '1' : '0') : (string)$value;

            Setting::updateOrCreate(
                ['key' => $key, 'created_by' => Auth::id()],
                ['value' => $stringValue]
            );
        }

         Notification::make()
            ->success()
            ->title(__('Cookie settings updated successfully!'))
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Cookie Settings'))
                ->action('save')
                ->extraAttributes(['class' => 'mt-5']),
        ];
    }
}
