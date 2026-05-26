<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use App\Services\CustomLivewireScriptService;
use App\Helpers\ErrorHelper;
use RachidLaasri\LaravelInstaller\Events\LaravelInstallerFinished;
use RachidLaasri\LaravelInstaller\Events\EnvironmentSaved;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the custom Livewire script service
        $this->app->singleton(CustomLivewireScriptService::class);

        // Override the installer's EnvironmentManager with our patched version
        // that writes correct Laravel 12 env keys instead of old Laravel 5.x keys.
        $this->app->bind(
            \RachidLaasri\LaravelInstaller\Helpers\EnvironmentManager::class,
            \App\Services\PatchedEnvironmentManager::class
        );

        // Override the installer's FinalInstallManager for robust key generation
        // that handles empty or missing APP_KEY in .env.
        $this->app->bind(
            \RachidLaasri\LaravelInstaller\Helpers\FinalInstallManager::class,
            \App\Services\PatchedFinalInstallManager::class
        );

        // Override the installer's EnvironmentController so database
        // credentials are validated with a real query (not just getPdo).
        $this->app->bind(
            \RachidLaasri\LaravelInstaller\Controllers\EnvironmentController::class,
            \App\Http\Controllers\Installer\EnvironmentController::class
        );

        // Override the installer's DatabaseController so migration/seed failures
        // block the installer from marking the app as installed.
        $this->app->bind(
            \RachidLaasri\LaravelInstaller\Controllers\DatabaseController::class,
            \App\Http\Controllers\Installer\DatabaseController::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force public disk URL to always use APP_URL — works on localhost subfolders,
        // live domains, vhosts, and switches automatically to S3/Wasabi when configured.
        $this->forceStorageUrl();

        // Run storage:link after installer finishes
        Event::listen(LaravelInstallerFinished::class, function () {
            $link = public_path('storage');
            if (!is_link($link)) {
                try {
                    symlink(storage_path('app/public'), $link);
                } catch (\Throwable $e) {
                    ErrorHelper::handleSilent($e, 'AppServiceProvider::storageLink', 'warning');
                }
            }
        });

        // Fix .env after installer wizard saves it — the package uses old Laravel 5.x keys.
        // We patch the file to add the correct Laravel 12 keys.
        Event::listen(EnvironmentSaved::class, function () {
            $envPath = base_path('.env');

            try {
                $content = file_get_contents($envPath);

                $replacements = [
                    'BROADCAST_DRIVER='  => 'BROADCAST_CONNECTION=',
                    'CACHE_DRIVER='      => 'CACHE_STORE=',
                    'QUEUE_DRIVER='      => 'QUEUE_CONNECTION=',
                    'MAIL_DRIVER='       => 'MAIL_MAILER=',
                ];

                foreach ($replacements as $old => $new) {
                    $content = str_replace($old, $new, $content);
                }

                $extras = [
                    'APP_LOCALE'        => 'en',
                    'APP_TIMEZONE'      => 'UTC',
                    'FILESYSTEM_DISK'   => 'local',
                    'VITE_APP_NAME'     => '${APP_NAME}',
                    'MAIL_FROM_ADDRESS' => config('mail.from.address', 'hello@example.com'),
                    'MAIL_FROM_NAME'    => '${APP_NAME}',
                ];

                foreach ($extras as $key => $default) {
                    if (!str_contains($content, $key . '=')) {
                        $content .= "\n{$key}={$default}";
                    }
                }

                file_put_contents($envPath, $content);
            } catch (\Throwable $e) {
                ErrorHelper::handleSilent($e, 'AppServiceProvider::patchEnv', 'warning');
            }
        });

        \Illuminate\Support\Facades\View::composer('layouts.auth', function ($view) {
            try {
                $seoSettings = \App\Models\Setting::whereIn('key', ['meta_keywords', 'meta_description', 'meta_image'])->pluck('value', 'key')->toArray();
                $metaTitle = \App\Models\Setting::where('key', 'site_title')->value('value') ?? config('app.name', 'Craft Laravel');
                $metaDescription = $seoSettings['meta_description'] ?? __('Secure admin portal');
                $metaImage = $seoSettings['meta_image'] ?? null;

                $metaImageUrl = $metaImage ? getImageUrl($metaImage) : null;
            } catch (\Exception $e) {
                ErrorHelper::handleSilent($e, 'AppServiceProvider::authViewComposer', 'warning');
                $metaTitle = config('app.name', 'Craft Laravel');
                $metaDescription = __('Secure admin portal');
                $metaImageUrl = null;
            }

            $themeColor = getAuthThemeColor();

            $view->with(compact('metaTitle', 'metaDescription', 'metaImageUrl', 'themeColor'));
        });

        \Illuminate\Support\Facades\View::composer('components.cookie-banner', function ($view) {
            try {
                $cookieSettings = \App\Models\Setting::whereIn('key', [
                    'enable_logging',
                    'strictly_necessary_cookies',
                    'cookie_title',
                    'cookie_description',
                    'strictly_cookie_title',
                    'strictly_cookie_description',
                    'contact_us_description',
                    'contact_us_url'
                ])->pluck('value', 'key')->toArray();
            } catch (\Exception $e) {
                ErrorHelper::handleSilent($e, 'AppServiceProvider::cookieViewComposer', 'warning');
                $cookieSettings = [];
            }

            $strictlyNecessaryCookies = ($cookieSettings['strictly_necessary_cookies'] ?? '1') === '1';
            $enableLogging = ($cookieSettings['enable_logging'] ?? '0') === '1';
            $cookieTitle = $cookieSettings['cookie_title'] ?? __('Cookie Consent');
            $cookieDescription = $cookieSettings['cookie_description'] ?? __('We use cookies to enhance your browsing experience and provide personalized content.');
            $strictlyTitle = $cookieSettings['strictly_cookie_title'] ?? __('Strictly Necessary Cookies');
            $strictlyDescription = $cookieSettings['strictly_cookie_description'] ?? __('These cookies are essential for the website to function properly.');
            $contactUrl = $cookieSettings['contact_us_url'] ?? '#';
            $contactDescription = $cookieSettings['contact_us_description'] ?? __('If you have any questions about our cookie policy, please contact us.');

            $view->with(compact(
                'strictlyNecessaryCookies', 'enableLogging', 'cookieTitle', 'cookieDescription',
                'strictlyTitle', 'strictlyDescription', 'contactUrl', 'contactDescription'
            ));
        });

        // Register custom Blade directive for Livewire scripts
        $this->registerLivewireScriptsDirective();

        // Register custom Livewire update route for localhost
        $this->registerCustomLivewireRoute();
    }

    /**
     * Register custom Blade directive for Livewire scripts with custom update URI
     */
    private function registerLivewireScriptsDirective(): void
    {
        Blade::directive('customLivewireScripts', function () {
            return "<?php echo app('App\Services\CustomLivewireScriptService')->renderScripts(); ?>";
        });
    }

    /**
     * Register custom Livewire update route derived from APP_URL base path
     */
    private function registerCustomLivewireRoute(): void
    {
        $appUrl = config('app.url', '');
        $parsed = parse_url($appUrl);
        $basePath = trim($parsed['path'] ?? '', '/');

        if ($basePath) {
            Route::post("/{$basePath}/livewire/update", function () {
                return app(\Livewire\Mechanisms\HandleRequests\HandleRequests::class)->handleUpdate();
            })->middleware(['web']);
        }
    }

    /**
     * Force the public disk URL to always match APP_URL at runtime.
     * This ensures media URLs are correct on:
     *   - localhost with subfolder (e.g. /Product/filament-craft)
     *   - live domains (https://example.com)
     *   - vhosts
     *   - S3 / Wasabi (those disks have their own URL config, untouched)
     */
    private function forceStorageUrl(): void
    {
        $disk = config('filesystems.default', 'local');

        // Only override for local public disk — S3/Wasabi manage their own URLs
        if (in_array($disk, ['s3', 'wasabi'])) {
            return;
        }

        $appUrl = rtrim(config('app.url', url('/')), '/');

        // Override the public disk URL so file uploads and asset() always use APP_URL
        config(['filesystems.disks.public.url' => $appUrl . '/storage']);
    }
}
