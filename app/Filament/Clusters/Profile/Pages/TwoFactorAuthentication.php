<?php

namespace App\Filament\Clusters\Profile\Pages;

use App\Filament\Clusters\Profile\ProfileCluster;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;
use App\Models\Setting;

class TwoFactorAuthentication extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected string $view = 'filament.clusters.profile.pages.two-factor-authentication';
    protected static ?string $cluster = ProfileCluster::class;
    protected static ?string $title = null;
    protected static ?int $navigationSort = 3;

    public function getTitle(): string
    {
        return __('Two-Factor Authentication');
    }

    public static function getNavigationLabel(): string
    {
        return __('Two-Factor Authentication');
    }

    protected function getHeaderActions(): array
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return [
                Action::make('disable_2fa')
                    ->label('')
                    ->extraAttributes(['class' => 'twofa-action-btn'])
                    ->modalWidth('sm')
                    ->form([
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->markAsRequired()
                            ->rules(['required', 'current_password'])
                    ])
                    ->modalHeading(__('Disable Two-Factor Authentication'))
                    ->modalDescription(__('Please enter your password to disable two-factor authentication.'))
                    ->action(function (array $data) {
                        Auth::user()->update([
                            'two_factor_enabled' => false,
                            'two_factor_secret' => null,
                            'two_factor_confirmed_at' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('Two-Factor Authentication disabled!'))
                            ->body(__('Your account is no longer protected with 2FA.'))
                            ->send();

                        $this->mount();
                    }),
            ];
        } else {
            return [
                Action::make('confirm_2fa')
                    ->label(__('Confirm & Enable 2FA'))
                    ->extraAttributes(['class' => 'twofa-action-btn'])
                    ->action('save'),
            ];
        }
    }
    public static function shouldRegisterNavigation(): bool
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return false;
            }

            $setting = Setting::where('key', 'two_factor_required')
                ->where('created_by', $user->id)
                ->first();

            return $setting ? (bool) $setting->value : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function canAccess(): bool
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return false;
            }

            $setting = Setting::where('key', 'two_factor_required')
                ->where('created_by', $user->id)
                ->first();

            return $setting ? (bool) $setting->value : false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public ?array $data = [];
    public $qrCode = null;
    public $secretKey = null;

    public function mount(): void
    {
        $user = Auth::user();
        $themeColor = $this->getThemeColor();

        if ($user->two_factor_enabled && $user->two_factor_secret) {
            $this->secretKey = $user->two_factor_secret;
            $this->qrCode = $this->generateQrCodeUrl($this->secretKey, $user->email);
        } else {
            $google2fa = new Google2FA();
            $secret = $google2fa->generateSecretKey();
            $this->qrCode = $this->generateQrCodeUrl($secret, $user->email);
            $this->secretKey = $secret;
        }

        $this->data = [
            'two_factor_enabled' => $user->two_factor_enabled ?? false,
            'verification_code' => '',
            'theme_color' => $themeColor,
        ];
        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(__('Two-Factor Authentication'))
                ->description(__('Add an extra layer of security to your account by enabling two-factor authentication.'))
                ->statePath('data')
                ->columns(2)
                ->schema([
                    Placeholder::make('setup_instructions')
                        ->label(__('Setup Instructions'))
                        ->visible(fn() => !Auth::user()->two_factor_enabled)
                        ->content(fn() => $this->renderSetupInstructions()),

                    Placeholder::make('qr_code')
                        ->label(__('QR Code'))
                        ->visible(fn() => !Auth::user()->two_factor_enabled)
                        ->content(fn() => $this->renderQrCode())
                        ->columnSpan(2),

                    Placeholder::make('manual_entry')
                        ->label(__('Manual entry'))
                        ->columnSpan(1)
                        ->visible(fn() => !Auth::user()->two_factor_enabled)
                        ->content(fn() => $this->renderManualEntry()),

                    TextInput::make('verification_code')
                        ->columnSpan(1)
                        ->label(__('Enter Verification Code'))
                        ->placeholder(__('Enter 6-digit code from your authenticator app'))
                        ->maxLength(6)
                        ->minLength(6)
                        ->numeric()
                        ->markAsRequired()
                        ->rules(['required', 'numeric', 'digits:6'])
                        ->visible(fn() => !Auth::user()->two_factor_enabled)
                        ->live(),

                    Placeholder::make('status')
                        ->label(__('Status'))
                        ->columnSpanFull()
                        ->visible(fn() => Auth::user()->two_factor_enabled)
                        ->content(fn() => $this->renderStatusBanner()),
                ])
        ];
    }

    public function generateSecret(): void
    {
        $google2fa = new Google2FA();
        $this->secretKey = $google2fa->generateSecretKey();

        $user = Auth::user();
        $this->qrCode = $this->generateQrCodeUrl($this->secretKey, $user->email);

        $user->update([
            'two_factor_secret' => $this->secretKey,
        ]);
    }

    public function save(): void
    {
        $this->form->validate();

        $formData = $this->form->getState();
        $user = Auth::user();

        $verificationCode = $formData['data']['verification_code'] ?? null;

        if (!$this->secretKey) {
            $this->generateSecret();
        }

        if ($this->secretKey && $verificationCode) {
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($this->secretKey, (string)$verificationCode);

            if (!$valid) {
                Notification::make()
                    ->danger()
                    ->title(__('Invalid verification code!'))
                    ->body(__('Please check your authenticator app and try again.'))
                    ->send();
                return;
            }

            $user->update([
                'two_factor_enabled' => true,
                'two_factor_secret' => $this->secretKey,
                'two_factor_confirmed_at' => now(),
            ]);

            Notification::make()
                ->success()
                ->title(__('Two-Factor Authentication enabled successfully!'))
                ->body(__('Your account is now protected with 2FA.'))
                ->send();

            $this->mount();
        } else {
            Notification::make()
                ->warning()
                ->title(__('Verification code required!'))
                ->body(__('Please enter the 6-digit code from your authenticator app.'))
                ->send();
        }
    }

    protected function getActions(): array
    {
        $user = Auth::user();

        if ($user->two_factor_enabled) {
            return [
                Action::make('disable_2fa')
                    ->label(__('Disable Two-Factor Authentication'))
                    ->color('danger')
                    ->modalWidth('sm')
                    ->form([
                        TextInput::make('password')
                            ->label(__('Password'))
                            ->password()
                            ->markAsRequired()
                            ->rules(['required', 'current_password'])
                    ])
                    ->modalHeading(__('Disable Two-Factor Authentication'))
                    ->modalDescription(__('Please enter your password to disable two-factor authentication.'))
                    ->action(function (array $data) {
                        Auth::user()->update([
                            'two_factor_enabled' => false,
                            'two_factor_secret' => null,
                            'two_factor_confirmed_at' => null,
                        ]);

                        Notification::make()
                            ->success()
                            ->title(__('Two-Factor Authentication disabled!'))
                            ->body(__('Your account is no longer protected with 2FA.'))
                            ->send();

                        $this->mount();
                    }),
            ];
        } else {
            return [
                Action::make('confirm_2fa')
                    ->label(__('Confirm & Enable 2FA'))
                    ->action('save'),
            ];
        }
    }

    private function generateQrCodeUrl(string $secret, string $email): string
    {
        $appName = config('app.name', 'Laravel App');
        $qrCodeUrl = (new Google2FA())->getQRCodeUrl($appName, $email, $secret);
        return $qrCodeUrl;
    }

    private function getThemeColor(): string
    {
        try {
            $setting = Setting::where('key', 'theme_color')->first();
            return $setting?->value ?? 'blue';
        } catch (\Exception $e) {
            return 'blue';
        }
    }

    private function getThemeColorHex(): string
    {
        $colorMap = [
            'slate' => '#64748b',
            'gray' => '#6b7280',
            'zinc' => '#71717a',
            'neutral' => '#737373',
            'stone' => '#78716c',
            'red' => '#ef4444',
            'orange' => '#f97316',
            'amber' => '#f59e0b',
            'yellow' => '#eab308',
            'lime' => '#84cc16',
            'green' => '#22c55e',
            'emerald' => '#10b981',
            'teal' => '#14b8a6',
            'cyan' => '#06b6d4',
            'sky' => '#0ea5e9',
            'blue' => '#3b82f6',
            'indigo' => '#6366f1',
            'violet' => '#8b5cf6',
            'purple' => '#a855f7',
            'fuchsia' => '#d946ef',
            'pink' => '#ec4899',
            'rose' => '#f43f5e',
        ];

        $themeColor = $this->getThemeColor();
        return $colorMap[$themeColor] ?? '#3b82f6';
    }

    private function renderSetupInstructions(): \Illuminate\Support\HtmlString
    {
        $color = $this->getThemeColorHex();

        return new \Illuminate\Support\HtmlString('
            <div class="twofa-setup-list" style="--twofa-theme-color: ' . $color . '">
                <div class="twofa-setup-item">
                    <div class="twofa-setup-number">1</div>
                    <span class="twofa-setup-text">' . __('Install Google Authenticator on your iOS or Android device') . '</span>
                </div>
                <div class="twofa-setup-item">
                    <div class="twofa-setup-number">2</div>
                    <span class="twofa-setup-text">' . __('Scan the QR code with your authenticator app') . '</span>
                </div>
                <div class="twofa-setup-item">
                    <div class="twofa-setup-number">3</div>
                    <span class="twofa-setup-text">' . __('Enter the 6-digit code from your app below') . '</span>
                </div>
            </div>
        ');
    }

    private function renderQrCode(): \Illuminate\Support\HtmlString
    {
        if (!$this->qrCode) {
            return new \Illuminate\Support\HtmlString('<p>' . __('No QR Code') . '</p>');
        }

        return new \Illuminate\Support\HtmlString('
            <div class="twofa-qr-code">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($this->qrCode) . '" alt="QR Code">
            </div>
        ');
    }

    private function renderManualEntry(): \Illuminate\Support\HtmlString
    {
        if (!$this->secretKey) {
            return new \Illuminate\Support\HtmlString('');
        }

        return new \Illuminate\Support\HtmlString('
            <div class="twofa-manual-entry">
                <code class="twofa-secret-key">' . $this->secretKey . '</code>
                <button onclick="copyKey(\'' . $this->secretKey . '\')" class="twofa-copy-btn">' . __('Copy') . '</button>
            </div>
            <p class="twofa-manual-hint">' . __('Use this key if you cannot scan the QR code') . '</p>
        ');
    }

    private function renderStatusBanner(): \Illuminate\Support\HtmlString
    {
        return new \Illuminate\Support\HtmlString('
            <div class="twofa-status-banner">
                <svg fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="twofa-status-text">' . __('Two-Factor Authentication is enabled and active') . '</span>
            </div>
        ');
    }
}
