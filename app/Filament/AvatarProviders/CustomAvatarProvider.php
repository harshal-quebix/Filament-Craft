<?php

namespace App\Filament\AvatarProviders;

use Filament\AvatarProviders\Contracts\AvatarProvider;

class CustomAvatarProvider implements AvatarProvider
{
    public function get(Model | Authenticatable $user): ?string
    {
        if (method_exists($user, 'getFilamentAvatarUrl')) {
            return $user->getFilamentAvatarUrl();
        }
        return null;
    }
}
