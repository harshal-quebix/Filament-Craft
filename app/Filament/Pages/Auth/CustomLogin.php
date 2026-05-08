<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

class CustomLogin extends BaseLogin
{
    public function mount(): void
    {
        // Check if app is installed
        if (!file_exists(storage_path('installed'))) {
            redirect(url('/install'));
            return;
        }
        
        parent::mount();
    }
}