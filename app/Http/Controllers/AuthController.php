<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\Setting;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect(route('filament.admin.pages.dashboard'));
        }

        if (!file_exists(storage_path('installed'))) {
            return redirect(url('/install'));
        }

        $layout = cms('login_layout', 'auth', 'simple');
        $view = $layout === 'advanced'
            ? 'filament.pages.auth.login-advanced'
            : 'filament.pages.auth.custom-login';

        return view($view);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {

            // Check if user has 2FA enabled and system setting allows it
            if (Auth::user()->two_factor_enabled) {
                // Check if 2FA is enabled in system settings
                $twoFactorRequired = Setting::where('key', 'two_factor_required')->value('value');

                if ($twoFactorRequired === true || $twoFactorRequired === '1') {
                    return redirect()->route('2fa.verify');
                }
            }
            return redirect()->route('filament.admin.pages.dashboard');
        }

        return back()->withErrors([
            'email' => __('The provided credentials do not match our records.'),
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect(route('filament.admin.pages.dashboard'));
        }

        // Check if user registration is enabled
        $userRegistration = Setting::where('key', 'user_registration')->value('value');
        if ($userRegistration === false || $userRegistration === '0') {
            abort(404);
        }

        $layout = cms('login_layout', 'auth', 'simple');
        $view = $layout === 'advanced'
            ? 'filament.pages.auth.register-advanced'
            : 'filament.pages.auth.custom-register';

        return view($view);
    }

    public function register(Request $request)
    {
        // Check if user registration is enabled
        $userRegistration = Setting::where('key', 'user_registration')->value('value');
        if ($userRegistration === false || $userRegistration === '0') {
            abort(404);
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default user role
        $user->assignRole('user');

        Auth::login($user);
        return redirect()->route('filament.admin.pages.dashboard');
    }

    public function showPasswordReset()
    {
        $layout = cms('login_layout', 'auth', 'simple');
        $view = $layout === 'advanced'
            ? 'filament.pages.auth.password-reset-advanced'
            : 'filament.pages.auth.password-reset';

        return view($view);
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
                    ? back()->with(['status' => __($status)])
                    : back()->withErrors(['email' => __($status)]);
    }

    public function showPasswordResetForm(Request $request, $token)
    {
        $layout = cms('login_layout', 'auth', 'simple');
        $view = $layout === 'advanced'
            ? 'filament.pages.auth.reset-password-advanced'
            : 'filament.pages.auth.reset-password';

        return view($view, ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(\Illuminate\Support\Str::random(60));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
                    ? redirect()->route('filament.admin.auth.login')->with('status', __($status))
                    : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->forget('2fa_verified');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect(route('filament.admin.auth.login'));
    }

    public function showEmailVerification()
    {
        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect()->route('filament.admin.pages.dashboard');
    }

    public function sendEmailVerification(Request $request)
    {
        Helper::configureMailSettings();
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', __('Verification link sent!'));
    }

    public function show2FAVerify()
    {
        if (!Auth::check() || !Auth::user()->two_factor_enabled) {
            return redirect()->route('login');
        }

        return view('filament.pages.auth.2fa-verify');
    }

    public function verify2FA(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|digits:6',
        ]);

        $user = Auth::user();

        if (!$user->two_factor_secret) {
            return back()->withErrors(['verification_code' => __('Two-factor authentication is not properly set up.')]);
        }

        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($user->two_factor_secret, $request->verification_code);

        if (!$valid) {
            return back()->withErrors(['verification_code' => __('Invalid verification code.')]);
        }

        session(['2fa_verified' => true]);
        return redirect()->intended(route('filament.admin.pages.dashboard'));
    }
}
