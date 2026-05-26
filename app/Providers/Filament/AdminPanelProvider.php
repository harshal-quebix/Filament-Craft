<?php

namespace App\Providers\Filament;

use App\Models\Setting;
use App\Helpers\ErrorHelper;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use App\Filament\Pages\Dashboard as CustomDashboard;
use App\Http\Middleware\CheckAppInstalled;
use App\Http\Middleware\ConfigureMailSettings;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\MenuItem;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\TwoFactorMiddleware;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $panel = $panel
            ->default()
            ->id('admin')
            ->path('')
            ->homeUrl(null)
            ->darkMode()
            ->userMenuItems([
                MenuItem::make()
                     ->label(__('Profile'))
                    ->icon('heroicon-o-user-circle')
                    ->url(fn() => filament()->getPanel('admin')->getUrl() . '/profile/personal-info'),
            ])
            ->login()
            ->passwordReset();

        if ($this->isRegistrationEnabled()) {
            $panel = $panel->registration();
        }

        return $panel
        ->emailVerification(\App\Filament\Pages\Auth\CustomEmailVerificationPrompt::class)
            ->spa()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->sidebarWidth('220px')
            ->maxContentWidth('full')
            ->favicon(fn() => $this->getLogoUrl('favicon') ?? asset('default-img/favicon.png'))
            ->brandName(fn() => \App\Models\Setting::where('key', 'site_title')->value('value') ?? config('app.name'))
            ->renderHook('panels::head.end', fn() => $this->getAdminStyles())
            ->renderHook('panels::head.start', fn() => $this->getSeoMetaTags())
            ->renderHook('panels::head.start', fn() => $this->getThemeDefaultScript())
            ->renderHook('panels::head.end', fn() => $this->getLivewireStyles())
            ->renderHook('panels::body.end', fn() => $this->getCustomLivewireScripts())
            ->renderHook('panels::global-search.before', fn() => view('filament.hooks.language-dropdown', $this->getLanguageDropdownData())->render())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->pages([
                CustomDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                CheckAppInstalled::class,
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
                ConfigureMailSettings::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                TwoFactorMiddleware::class,
            ])
            ->authGuard('web');
    }

    private function getAdminStyles(): string
    {
        $lightLogo = $this->getLogoUrl('logo_light') ?? asset('default-img/light_logo.png');
        $darkLogo = $this->getLogoUrl('logo_dark') ?? asset('default-img/dark_logo.png');
        $fontFamily = $this->getFontFamily();
        $themeColor = $this->getThemeColor();
        $landingRoute = route('landing');

        $cssVars = '';
        if ($themeColor) {
            $cssVars = ':root {';
            foreach ([50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950] as $shade) {
                $cssVars .= '--primary-' . $shade . ': ' . $themeColor[$shade] . ';';
            }
            $cssVars .= '}';
        }

        return '<link rel="stylesheet" href="' . asset('css/custom.css') . '">' .
               '<style>' .
               '.fi-logo { background-image: url("' . $darkLogo . '"); }' .
               '.dark .fi-logo { background-image: url("' . $lightLogo . '"); }' .
               ':root { --admin-font-family: "' . $fontFamily . '"; }' .
               $cssVars .
               '</style>' .
               '<script>window.landingRoute = "' . $landingRoute . '";</script>' .
               '<script src="' . asset('js/admin-common.js') . '"></script>';
    }

    private function getThemeColor(): array
    {
        try {
            $query = Setting::where('key', 'theme_color');
            $userId = auth()->id();
            if ($userId) {
                $query->where('created_by', $userId);
            }
            $setting = $query->first();
            $colorName = $setting?->value ?? 'blue';

            $colorClass = 'Filament\\Support\\Colors\\Color';
            $colorConstant = ucfirst($colorName);

            if (defined("{$colorClass}::{$colorConstant}")) {
                return constant("{$colorClass}::{$colorConstant}");
            }
        } catch (\Exception $e) {
            ErrorHelper::handleSilent($e, 'AdminPanelProvider::getThemeColor', 'warning');
        }

        return Color::Blue;
    }

    private function getLogoUrl(string $key): ?string
    {
        try {
            $query = Setting::where('key', $key);
            $userId = auth()->id();
            if ($userId) {
                $query->where('created_by', $userId);
            }
            $path = $query->value('value');

            if ($path) {
                $url = getImageUrl($path);
                if ($url) {
                    return $url;
                }
            }
        } catch (\Exception $e) {
            ErrorHelper::handleSilent($e, 'AdminPanelProvider::getLogoUrl("' . $key . '")', 'warning');
        }

        $defaultFile = match ($key) {
            'logo_light' => 'default-img/light_logo.png',
            'logo_dark'  => 'default-img/dark_logo.png',
            'favicon'    => 'default-img/favicon.png',
            default      => null,
        };

        if ($defaultFile) {
            return asset($defaultFile);
        }

        return null;
    }

    private function getSeoMetaTags(): string
    {
        try {
            $metaTitle = Setting::where('key', 'meta_title')
                               ->where('created_by', auth()->id())
                               ->value('value');
            $metaKeywords = Setting::where('key', 'meta_keywords')
                                  ->where('created_by', auth()->id())
                                  ->value('value');
            $metaDescription = Setting::where('key', 'meta_description')
                                     ->where('created_by', auth()->id())
                                     ->value('value');
            $metaImageId = Setting::where('key', 'meta_image')
                                 ->where('created_by', auth()->id())
                                 ->value('value');

            $tags = '';

            if ($metaTitle) {
                $tags .= '<title>' . e($metaTitle) . '</title>';
                $tags .= '<meta property="og:title" content="' . e($metaTitle) . '">';
                $tags .= '<meta name="twitter:title" content="' . e($metaTitle) . '">';
            }

            if ($metaKeywords) {
                $tags .= '<meta name="keywords" content="' . e($metaKeywords) . '">';
            }

            if ($metaDescription) {
                $tags .= '<meta name="description" content="' . e($metaDescription) . '">';
                $tags .= '<meta property="og:description" content="' . e($metaDescription) . '">';
            }

            $tags .= '<meta property="og:type" content="website">';
            $tags .= '<meta property="og:url" content="' . e(request()->url()) . '">';
            $tags .= '<meta name="twitter:card" content="summary_large_image">';

            if ($metaImageId) {
                $imageUrl = getImageUrl($metaImageId);
                if ($imageUrl) {
                    $tags .= '<meta property="og:image" content="' . e($imageUrl) . '">';
                    $tags .= '<meta name="twitter:image" content="' . e($imageUrl) . '">';
                }
            }

            return $tags;
        } catch (\Exception $e) {
            ErrorHelper::handleSilent($e, 'AdminPanelProvider::getSeoMetaTags', 'warning');
            return '';
        }
    }

    private function getLanguageDropdownData(): array
    {
        $currentLocale = app()->getLocale();
        $languages = [
            'en' => ['name' => 'English', 'flag' => '🇺🇸'],
            'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
            'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
        ];

        return [
            'currentLocale' => $currentLocale,
            'currentLang' => $languages[$currentLocale] ?? $languages['en'],
            'languages' => $languages,
        ];
    }

    private function getFontFamily(): string
    {
        try {
            $query = Setting::where('key', 'font_family');
            $userId = auth()->id();
            if ($userId) {
                $query->where('created_by', $userId);
            }
            $setting = $query->first();
            return $setting?->value ?? 'Uni Neue';
        } catch (\Exception $e) {
            ErrorHelper::handleSilent($e, 'AdminPanelProvider::getFontFamily', 'warning');
            return 'Uni Neue';
        }
    }

    private function isRegistrationEnabled(): bool
    {
        try {
            $setting = Setting::where('key', 'user_registration')->first();
            return $setting ? (bool) $setting->value : true;
        } catch (\Exception $e) {
            ErrorHelper::handleSilent($e, 'AdminPanelProvider::isRegistrationEnabled', 'warning');
            return true;
        }
    }



    private function getThemeColorName(): string
    {
        try {
            $userId = auth()->id();
            $query = Setting::where('key', 'theme_color');
            if ($userId) {
                $query->where('created_by', $userId);
            }
            $setting = $query->first();
            return $setting?->value ?? 'blue';
        } catch (\Exception $e) {
            ErrorHelper::handleSilent($e, 'AdminPanelProvider::getThemeColorName', 'warning');
            return 'blue';
        }
    }

    private function getCustomLivewireScripts(): string
    {
        return app(\App\Services\CustomLivewireScriptService::class)->renderScripts();
    }

    private function getThemeDefaultScript(): string
    {
        // Force light mode as the default on fresh installs (no stored preference).
        // Dark mode remains available via the theme toggle.
        return '<script>(function(){var t=localStorage.getItem("theme");if(!t){localStorage.setItem("theme","light");document.documentElement.classList.remove("dark");}})();</script>';
    }

    private function getLivewireStyles(): string
    {
        return \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles();
    }
}
