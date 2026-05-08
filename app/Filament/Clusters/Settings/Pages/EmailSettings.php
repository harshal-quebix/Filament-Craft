<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Setting;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Schemas\Components\Grid as ComponentsGrid;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\TestMail;
use App\Helpers\Helper;

class EmailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.settings.pages.email-settings';
    protected static ?string $cluster = SettingsCluster::class;
    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Email Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Email Settings');
    }
    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public ?array $data = [];
    public ?string $testEmail = null;

    public function mount(): void
    {
        $settings = Setting::where('created_by', Auth::id())->pluck('value', 'key')->toArray();
        $this->data = $settings;
        $this->form->fill(['data' => $this->data]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(__('Email Configuration'))
                ->schema([
                    ComponentsGrid::make(2)
                        ->statePath('data')
                        ->schema([
                    Select::make('email_provider')
                        ->label(__('Email Provider'))
                        ->options([
                            'smtp'     => __('SMTP'),
                            'mailgun'  => __('Mailgun'),
                            'ses'      => __('Amazon SES'),
                            'sendmail' => __('Sendmail'),
                        ])
                        ->default('smtp')
                        ->searchable()
                        ->markAsRequired()
                        ->rules(['required'])
                        ->validationMessages([
                            'required' => __('The Email Provider field is required.'),
                        ]),
                    Select::make('mail_driver')
                        ->label(__('Mail Driver'))
                        ->options([
                            'smtp'     => __('SMTP'),
                            'mail'     => __('PHP Mail'),
                            'sendmail' => __('Sendmail'),
                        ])
                        ->default('smtp')
                        ->searchable()
                        ->markAsRequired()
                        ->rules(['required'])
                        ->validationMessages([
                            'required' => __('The Mail Driver field is required.'),
                        ]),
                    TextInput::make('smtp_host')
                        ->label(__('SMTP Host'))
                        ->placeholder(__('smtp.gmail.com'))
                        ->markAsRequired()
                        ->minLength(3)
                        ->maxLength(255)
                        ->rules(['required', 'string', 'min:3', 'max:255'])
                        ->validationMessages([
                            'required' => __('The SMTP Host field is required.'),
                            'min'      => __('The SMTP Host must be at least 3 characters.'),
                            'max'      => __('The SMTP Host may not be greater than 255 characters.'),
                        ]),
                    TextInput::make('smtp_port')
                        ->label(__('SMTP Port'))
                        ->numeric()
                        ->placeholder(__('587'))
                        ->markAsRequired()
                        ->minValue(1)
                        ->maxValue(65535)
                        ->rules(['required'])
                        ->validationMessages([
                            'required' => __('The SMTP Port field is required.'),
                            'min'      => __('The SMTP Port must be at least 1.'),
                            'max'      => __('The SMTP Port may not be greater than 65535.'),
                        ]),
                    TextInput::make('smtp_username')
                        ->label(__('SMTP Username'))
                        ->email()
                        ->placeholder(__('your-email@gmail.com'))
                        ->markAsRequired()
                        ->maxLength(255)
                        ->rules(['required', 'email', 'max:255'])
                        ->validationMessages([
                            'required' => __('The SMTP Username field is required.'),
                            'email'    => __('The SMTP Username must be a valid email address.'),
                            'max'      => __('The SMTP Username may not be greater than 255 characters.'),
                        ]),
                    TextInput::make('smtp_password')
                        ->label(__('SMTP Password'))
                        ->password()
                        ->placeholder(__('your-app-password'))
                        ->markAsRequired()
                        ->minLength(6)
                        ->rules(['required', 'min:6'])
                        ->validationMessages([
                            'required' => __('The SMTP Password field is required.'),
                            'min'      => __('The SMTP Password must be at least 6 characters.'),
                        ]),
                    Select::make('mail_encryption')
                        ->label(__('Mail Encryption'))
                        ->options([
                            'tls'  => __('TLS'),
                            'ssl'  => __('SSL'),
                            'null' => __('None'),
                        ])
                        ->default('tls')
                        ->searchable()
                        ->markAsRequired()
                        ->rules(['required'])
                        ->validationMessages([
                            'required' => __('The Mail Encryption field is required.'),
                        ]),
                    TextInput::make('from_address')
                        ->label(__('From Address'))
                        ->email()
                        ->placeholder(__('noreply@example.com'))
                        ->markAsRequired()
                        ->maxLength(255)
                        ->rules(['required', 'email', 'max:255'])
                        ->validationMessages([
                            'required' => __('The From Address field is required.'),
                            'email'    => __('The From Address must be a valid email address.'),
                            'max'      => __('The From Address may not be greater than 255 characters.'),
                        ]),
                            TextInput::make('from_name')
                                ->label(__('From Name'))
                                ->placeholder(__('Your App Name'))
                                ->markAsRequired()
                                ->minLength(2)
                                ->maxLength(100)
                                ->rules(['required', 'string', 'min:2', 'max:100'])
                                ->validationMessages([
                                    'required' => __('The From Name field is required.'),
                                    'min'      => __('The From Name must be at least 2 characters.'),
                                    'max'      => __('The From Name may not be greater than 100 characters.'),
                                ])
                                ->columnSpanFull(),
                        ]),
                ]),
            Section::make(__('Test Email'))
                ->schema([
                    TextInput::make('testEmail')
                        ->label(__('Test Email Address'))
                        ->email()
                        ->placeholder(__('test@example.com'))
                        ->helperText(__('Enter email address to send test email'))
                        ->markAsRequired()
                        ->rules(['required', 'email', 'max:255']),
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
        if (isset($data['smtp_username']) && !filter_var($data['smtp_username'], FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->danger()
                ->title(__('Invalid Email Format'))
                ->body(__('SMTP Username must be a valid email address.'))
                ->send();
            return;
        }

        if (isset($data['from_address']) && !filter_var($data['from_address'], FILTER_VALIDATE_EMAIL)) {
            Notification::make()
                ->danger()
                ->title(__('Invalid Email Format'))
                ->body(__('From Address must be a valid email address.'))
                ->send();
            return;
        }

        if (isset($data['smtp_host']) && (strlen($data['smtp_host']) < 3 || strlen($data['smtp_host']) > 255)) {
            Notification::make()
                ->danger()
                ->title(__('Validation Error'))
                ->body(__('SMTP Host must be between 3 and 255 characters.'))
                ->send();
            return;
        }

        if (isset($data['smtp_port']) && ($data['smtp_port'] < 1 || $data['smtp_port'] > 65535)) {
            Notification::make()
                ->danger()
                ->title(__('Validation Error'))
                ->body(__('SMTP Port must be between 1 and 65535.'))
                ->send();
            return;
        }

        if (isset($data['smtp_password']) && strlen($data['smtp_password']) < 6) {
            Notification::make()
                ->danger()
                ->title(__('Validation Error'))
                ->body(__('SMTP Password must be at least 6 characters.'))
                ->send();
            return;
        }

        if (isset($data['from_name']) && (strlen($data['from_name']) < 2 || strlen($data['from_name']) > 100)) {
            Notification::make()
                ->danger()
                ->title(__('Validation Error'))
                ->body(__('From Name must be between 2 and 100 characters.'))
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
            ->title(__('Email settings updated successfully!'))
            ->send();
    }

    public function sendTestEmail(): void
    {
        // Validate only the testEmail field via Livewire
        $this->validate(
            ['testEmail' => ['required', 'email', 'max:255']],
            // [
            //     'testEmail.required' => __('Please enter test email address.'),
            //     'testEmail.email'    => __('Please enter a valid email address.'),
            // ]
        );

        try {
            // First check if settings are saved in database
            $savedSettings = Setting::whereIn('key', ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_address'])
                ->where('created_by', Auth::id())
                ->pluck('value', 'key');

            $requiredFields = ['smtp_host', 'smtp_port', 'smtp_username', 'smtp_password', 'from_address'];
            $missingFields = [];

            foreach ($requiredFields as $field) {
                if (empty($savedSettings[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                Notification::make()
                    ->warning()
                    ->title(__('Email Settings Required'))
                    ->body(__('Please add first your mail credentials and save settings before sending test email.'))
                    ->persistent()
                    ->send();
                return;
            }

            Helper::configureMailSettings();
            Mail::to($this->testEmail)->send(new TestMail());

            $sentToEmail = $this->testEmail;
            $this->testEmail = null;

            Notification::make()
                ->success()
                ->title(__('Test email sent successfully!'))
                ->body(__('Check inbox at ') . $sentToEmail)
                ->send();
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();

            if (str_contains($errorMessage, 'getaddrinfo') || str_contains($errorMessage, 'Name or service not known')) {
                $errorMessage = __('Invalid SMTP host. Please check your SMTP host configuration.');
            } elseif (str_contains($errorMessage, 'Connection refused')) {
                $errorMessage = __('Connection refused. Please check your SMTP host and port configuration.');
            } elseif (str_contains($errorMessage, 'Authentication failed')) {
                $errorMessage = __('Authentication failed. Please check your SMTP username and password.');
            }

            Notification::make()
                ->danger()
                ->title(__('Failed to send test email'))
                ->body($errorMessage)
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Email Settings'))
                ->action('save'),
            Action::make('test')
                ->label(__('Send Test Email'))
                ->color('info')
                ->icon('heroicon-o-paper-airplane')
                ->action('sendTestEmail'),
        ];
    }
}
