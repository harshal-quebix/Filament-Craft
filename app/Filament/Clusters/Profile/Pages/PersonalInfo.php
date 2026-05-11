<?php

namespace App\Filament\Clusters\Profile\Pages;

use App\Filament\Clusters\Profile\ProfileCluster;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

class PersonalInfo extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.profile.pages.personal-info';
    protected static ?string $cluster = ProfileCluster::class;
    protected static ?string $title = null;

    public function getTitle(): string
    {
        return __('Personal Information');
    }

    public static function getNavigationLabel(): string
    {
        return __('Personal Information');
    }
    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    public $profile_photo = [];

    public function mount(): void
    {
        $user = auth()->user();

        $rawPhoto = $user->getAttributes()['profile_photo'] ?? null;

        // Add default profile photo if none exists
        if (! $rawPhoto) {
            try {
                $defaultPath = public_path('default-img/profile.png');
                $storedPath = uploadFileFromPath($defaultPath, 'profile-photos');
                if ($storedPath) {
                    $user->update(['profile_photo' => $storedPath]);
                    $rawPhoto = $storedPath;
                }
            } catch (\Exception $e) {
                // Skip if disk not ready
            }
        }

        $this->profile_photo = $rawPhoto ? [$rawPhoto] : [];

        $this->data = [
            'name' => $user->name,
            'email' => $user->email,
            'mobile_number' => $user->mobile_number,
        ];

        $this->form->fill([
            'profile_photo' => $this->profile_photo,
            'data' => $this->data
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(__('Personal Information'))
                ->description(__('Update your personal details and profile photo'))
                ->schema([
                    Grid::make(4)
                        ->schema([
                            FileUpload::make('profile_photo')
                                ->label(__('Profile Photo'))
                                ->disk('public')
                                ->directory('profile-photos')
                                ->avatar()
                                ->imagePreviewHeight('100')
                                ->maxSize(2048)
                                ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/jpg'])
                                ->columnSpan(1),
                            Grid::make(2)
                                ->statePath('data')
                                ->columnSpan(3)
                                ->schema([
                                    TextInput::make('name')
                                        ->label(__('Full Name'))
                                        ->markAsRequired()
                                        ->rules(['required', 'string', 'max:255'])
                                        ->maxLength(255),
                                    TextInput::make('email')
                                        ->label(__('Email Address'))
                                        ->email()
                                        ->markAsRequired()
                                        ->rules(['required', 'email', 'max:255'])
                                        ->maxLength(255),
                                    TextInput::make('mobile_number')
                                        ->label(__('Mobile Number'))
                                        ->tel()
                                        ->maxLength(15)
                                        ->placeholder(__('Mobile Number Placeholder')),
                                ])
                        ])
                ])
        ];
    }

    public function save(): void
    {
        if (! is_dir(storage_path('app/uploads/profile-photos'))) {
            mkdir(storage_path('app/uploads/profile-photos'), 0755, true);
        }

        $formData = $this->form->getState();
        $data = $formData['data'];

        $user = auth()->user();

        $updateData = [
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile_number' => $data['mobile_number'] ?? null,
        ];

        if (!empty($formData['profile_photo'])) {
            $path = is_array($formData['profile_photo']) ? $formData['profile_photo'][0] : $formData['profile_photo'];
            if ($path) {
                $updateData['profile_photo'] = $path;
            }
        } else {
            $updateData['profile_photo'] = null;
        }

        $user->update($updateData);

        Notification::make()
            ->success()
            ->title(__('Personal information updated successfully!'))
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Save Changes'))
                ->action('save'),
        ];
    }
}
