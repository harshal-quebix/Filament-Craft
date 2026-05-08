<?php

namespace App\Filament\Clusters\Settings\Pages;

use App\Filament\Clusters\Settings\SettingsCluster;
use App\Models\Setting;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class AuthPageSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.settings.pages.auth-page-settings';
    protected static ?string $cluster = SettingsCluster::class;
    protected static ?string $title = null;
    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return __('Auth Page Settings');
    }

    public static function getNavigationLabel(): string
    {
        return __('Auth Page Settings');
    }

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return Auth::user()->hasRole('admin');
    }

    public $auth_page_image = [];

    public function mount(): void
    {
        $existing = Setting::where('key', 'auth_page_image')->where('created_by', Auth::id())->value('value');
        $this->auth_page_image = $existing ? [$existing] : [];

        $this->form->fill([
            'auth_page_image' => $this->auth_page_image,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            FileUpload::make('auth_page_image')
                ->label(__('Auth Page Image'))
                ->disk('public')
                ->directory('brand')
                ->image()
                ->imagePreviewHeight('350')
                ->helperText(__('Upload an image to replace the default SVG on the login page'))
                ->columnSpan(6),
        ];
    }

    public function save(): void
    {
        if (! is_dir(storage_path('app/uploads/brand'))) {
            mkdir(storage_path('app/uploads/brand'), 0755, true);
        }

        $data = $this->form->getState();

        if (!empty($data['auth_page_image'])) {
            $path = is_array($data['auth_page_image']) ? $data['auth_page_image'][0] : $data['auth_page_image'];
            if ($path) {
                Setting::updateOrCreate(
                    ['key' => 'auth_page_image', 'created_by' => Auth::id()],
                    ['value' => $path]
                );
            }
        }
        // Note: We do NOT delete the auth_page_image setting when cleared.
        // Auth pages already fall back to a default SVG illustration
        // when no auth page image is set.

        Notification::make()
            ->success()
            ->title(__('Auth page settings have been saved successfully!'))
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Settings'))
                ->action('save'),
        ];
    }
}
