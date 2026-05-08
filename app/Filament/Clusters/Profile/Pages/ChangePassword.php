<?php

namespace App\Filament\Clusters\Profile\Pages;

use App\Filament\Clusters\Profile\ProfileCluster;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Filament\Schemas\Components\Grid;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Page implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'filament.clusters.profile.pages.change-password';
    protected static ?string $cluster = ProfileCluster::class;
    protected static ?string $title = null;
    protected static ?int $navigationSort = 2;

     public function getTitle(): string
    {
        return __('Change Password');
    }

    public static function getNavigationLabel(): string
    {
        return __('Change Password');
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(['data' => []]);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(__('Change Password'))
                ->description(__('Update your account password'))
                ->schema([
                    Grid::make(12)
                        ->statePath('data')
                        ->schema([
                            TextInput::make('current_password')
                                ->label(__('Current Password'))
                                ->password()
                                ->revealable()
                                ->markAsRequired()
                                ->rules(['required', 'current_password'])
                                ->columnSpan(7),
                            TextInput::make('password')
                                ->label(__('New Password'))
                                ->password()
                                ->revealable()
                                ->markAsRequired()
                                ->rules(['required', 'confirmed'])
                                ->rule(Password::default())
                                ->columnSpan(7),
                            TextInput::make('password_confirmation')
                                ->label(__('Confirm New Password'))
                                ->password()
                                ->revealable()
                                ->markAsRequired()
                                ->rules(['required'])
                                ->columnSpan(7),
                        ])
                ])
        ];
    }

    public function save(): void
    {
        $this->validate([
            'data.current_password' => ['required', 'current_password'],
            'data.password' => ['required', 'min:8', 'confirmed'],
            'data.password_confirmation' => ['required'],
        ]);

        $formData = $this->form->getState();
        $data = $formData['data'];

        // Verify current password
        if (!Hash::check($data['current_password'], Auth::user()->password)) {
            Notification::make()
                ->danger()
                ->title(__('Current password is incorrect!'))
                ->send();
            return;
        }

        // Update password
        Auth::user()->update([
            'password' => Hash::make($data['password']),
        ]);

        // Clear form
        $this->form->fill(['data' => []]);

        Notification::make()
            ->success()
            ->title(__('Password changed successfully!'))
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('Change Password'))
                ->action('save'),
        ];
    }
}
