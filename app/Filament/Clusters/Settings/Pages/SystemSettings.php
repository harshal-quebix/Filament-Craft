<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class SystemSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.settings.pages.system-settings';
    protected static ?string $cluster = SettingsCluster::class;
    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('System Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('System Settings');
    }

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public ?array $data = [];

    public function mount(): void
    {
        // Load existing settings for current user from database
        $settings = Setting::where('created_by', Auth::id())->pluck('value', 'key')->toArray();

        // Set defaults for missing settings
        $defaults = [
            'font_family'      => 'Inter',
            'date_format'      => 'Y-m-d',
            'time_format'      => 'H:i',
            'default_timezone' => 'UTC',
            'support_email'    => '',
            'user_registration'  => true,
            'email_verification' => false,
            'two_factor_required' => false,
        ];

        foreach ($defaults as $key => $defaultValue) {
            if (!isset($settings[$key])) {
                $settings[$key] = $defaultValue;
            }
        }

        // Convert string values back to proper types
        foreach ($settings as $key => $value) {
            if ($value === '1') {
                $settings[$key] = true;
            } elseif ($value === '0') {
                $settings[$key] = false;
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
                    TextInput::make('support_email')
                        ->label(__('Support Email'))
                        ->email()
                        ->placeholder('support@example.com')
                        ->helperText(__('Displayed on Privacy Policy, Terms, and Contact pages.'))
                        ->columnSpanFull(),
                    Select::make('date_format')
                        ->label(__('Date Format'))
                        ->options([
                            'Y-m-d' => __('YYYY-MM-DD'),
                            'd/m/Y' => __('DD/MM/YYYY'),
                            'm/d/Y' => __('MM/DD/YYYY'),
                            'd-m-Y' => __('DD-MM-YYYY'),
                        ])
                        ->default('Y-m-d')
                        ->searchable()
                        ->markAsRequired()
                        ->rules(['required']),
                    Select::make('time_format')
                        ->label(__('Time Format'))
                        ->options([
                            'H:i' => __('24 Hour (HH:MM)'),
                            'h:i A' => __('12 Hour (hh:mm AM/PM)'),
                        ])
                        ->default('H:i')
                        ->searchable()
                        ->markAsRequired()
                        ->rules(['required']),
                    Select::make('default_timezone')
                        ->label(__('Default Timezone'))
                        ->options([
                            'UTC' => __('UTC'),
                            'Asia/Karachi' => __('Asia/Karachi'),
                            'America/New_York' => __('America/New_York'),
                            'Europe/London' => __('Europe/London'),
                            'Asia/Dubai' => __('Asia/Dubai'),
                        ])
                        ->default('UTC')
                        ->searchable()
                        ->markAsRequired()
                        ->rules(['required']),
                    Select::make('font_family')
                        ->label(__('Font Family'))
                        ->options([
                            'Inter' => __('Inter'),
                            'Roboto' => __('Roboto'),
                            'Open Sans' => __('Open Sans'),
                            'Lato' => __('Lato'),
                            'Montserrat' => __('Montserrat'),
                            'Source Sans Pro' => __('Source Sans Pro'),
                            'Raleway' => __('Raleway'),
                            'Poppins' => __('Poppins'),
                            'Nunito' => __('Nunito'),
                            'Ubuntu' => __('Ubuntu'),
                            'Uni Neue' => __('Uni Neue'),
                            'Pacifico' => __('Pacifico'),
                            'Dancing Script' => __('Dancing Script'),
                            'Courier New' => __('Courier New'),
                            'Comic Sans MS' => __('Comic Sans MS'),
                            'Serif' => __('Serif'),
                            'Sans-serif' => __('Sans-serif'),
                            'Monospace' => __('Monospace'),
                            'Red Hat Display' => __('Red Hat Display'),
                        ])
                        ->default('Inter')
                        ->searchable()
                        ->markAsRequired()
                        ->rules(['required'])
                        ->live()
                        ->afterStateUpdated(function ($state) {
                            Setting::updateOrCreate(
                                ['key' => 'font_family', 'created_by' => Auth::id()],
                                ['value' => $state]
                            );

                            $this->dispatch('font-family-changed', font: $state);
                        }),
                    ComponentsGrid::make(3)
                        ->schema([
                            Toggle::make('email_verification')
                                ->label(__('Email Verification'))
                                ->default(false)
                                ->visible(fn() => Auth::user()->hasRole('admin')),
                            Toggle::make('user_registration')
                                ->label(__('User Registration'))
                                ->default(true)
                                ->visible(fn() => Auth::user()->hasRole('admin')),
                            Toggle::make('two_factor_required')
                                ->label(__('Two-Factor Authentication'))
                                ->default(false),
                        ])
                        ->columnSpanFull(),
                ]),
        ];
    }

    public function save(): void
    {
        $this->form->validate();

        $formData = $this->form->getState();
        $data = $formData['data'];

        // Check email verification validation before saving
        if (isset($data['email_verification']) && $data['email_verification']) {
            $emailSettings = Setting::whereIn('key', ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_address'])
                ->where('created_by', Auth::id())
                ->pluck('value', 'key');

            $requiredFields = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_address'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (empty($emailSettings[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                Notification::make()
                    ->danger()
                    ->title(__('Email Configuration Required'))
                    ->body(__('Please add your email configuration first in Settings > Email Settings before enabling email verification.'))
                    ->persistent()
                    ->send();
                return;
            }
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'created_by' => Auth::id()],
                ['value' => $value]
            );
        }

        Notification::make()
            ->success()
            ->title(__('System settings have been saved successfully!'))
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Settings'))
                ->action('save')
                ->extraAttributes(['class' => 'mt-5']),
        ];
    }
}
