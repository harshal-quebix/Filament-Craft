<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;

class TwoFactorMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Check if 2FA is enabled in system settings
        $twoFactorRequired = Setting::where('key', 'two_factor_required')->value('value');

        // Only redirect to 2FA if system setting is enabled (true or '1')
        if ($user && $user->two_factor_enabled && !session('2fa_verified') && ($twoFactorRequired === true || $twoFactorRequired === '1')) {
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
