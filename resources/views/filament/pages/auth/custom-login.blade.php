@php
use App\Models\Setting;

try {
$setting = Setting::where('key', 'theme_color')->first();
$colorName = $setting?->value ?? 'blue';
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
$themeColor = $colorMap[$colorName] ?? '#3b82f6';

$authImageUrl = null;
$authSetting = Setting::where('key', 'auth_page_image')->first();
if ($authSetting && $authSetting->value) {
$authImageUrl = getImageUrl($authSetting->value);
}

$darkLogo = null;
$logoSetting = Setting::where('key', 'logo_dark')->first();
if ($logoSetting && $logoSetting->value) {
$darkLogo = getImageUrl($logoSetting->value);
}
} catch (\Exception $e) {
$themeColor = '#3b82f6';
$authImageUrl = null;
$darkLogo = null;
}

$authData = authData();
@endphp

@extends('filament.pages.auth.layout')

@section('content')
<div class="w-full max-w-lg px-8 py-16 auth-theme-wrapper" style="--auth-theme-color: {{ $themeColor }}">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-10">
        @if ($darkLogo)
        <div class="text-center mb-8">
            <img src="{{ $darkLogo }}" alt="Logo" class="h-12 mx-auto">
        </div>
        @else
        <div class="text-center mb-8">
            <img src="{{ asset('default-img/dark_logo.png') }}" alt="Logo" class="h-12 mx-auto dark:hidden">
            <img src="{{ asset('default-img/light_logo.png') }}" alt="Logo" class="h-12 mx-auto hidden dark:block">
        </div>
        @endif
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __($authData['login_heading']) }}</h2>
            <p class="text-gray-600 dark:text-gray-300">{{ __($authData['login_description']) }}</p>
        </div>

        <form method="POST" action="{{ route('login') }}" class="space-y-6">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Email') }}</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:border-transparent auth-input-ring">
                @error('email')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('Password') }}</label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium hover:underline auth-link">{{ __('Forgot password?') }}</a>
                </div>
                <div class="relative">
                    <input type="password" id="password" name="password" required class="w-full px-3 py-2 pr-10 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:border-transparent auth-input-ring">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300">
                        <svg id="eye-open" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                            <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.147.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                        </svg>
                        <svg id="eye-closed" class="h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z" clip-rule="evenodd" />
                            <path d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z" />
                        </svg>
                    </button>
                </div>
                @error('password')
                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center">
                <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 auth-checkbox">
                <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">{{ __('Remember me') }}</label>
            </div>

            <div class="mt-8">
                <button type="submit" class="w-full text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg auth-btn">
                    {{ __('Sign In') }}
                </button>
            </div>
        </form>
        @if((Setting::where('key', 'user_registration')->value('value') ?? '1') == '1')
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Don\'t have an account?') }}
                    <a href="{{ route('register') }}" class="font-medium hover:underline auth-link">{{ __('Register Here') }}</a>
                </p>
            </div>
        @endif
    </div>
</div>
<script>
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            eyeOpen.classList.add('hidden');
            eyeClosed.classList.remove('hidden');
        } else {
            passwordField.type = 'password';
            eyeOpen.classList.remove('hidden');
            eyeClosed.classList.add('hidden');
        }
    }

</script>
@endsection
