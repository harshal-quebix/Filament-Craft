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

// Get auth page image
$authImageUrl = null;
$authSetting = Setting::where('key', 'auth_page_image')->first();
if ($authSetting && $authSetting->value) {
$authImageUrl = getImageUrl($authSetting->value);
}

// Get logo
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
<div class="min-h-screen w-full flex">
    <!-- Left side: Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12 sm:px-12 lg:px-20 bg-white dark:bg-gray-900">
        <div class="w-full max-w-md">
            @if ($darkLogo)
            <div class="mb-10">
                <img src="{{ $darkLogo }}" alt="Logo" class="h-10">
            </div>
            @else
            <div class="mb-10">
                <img src="{{ asset('default-img/dark_logo.png') }}" alt="Logo" class="h-10 dark:hidden">
                <img src="{{ asset('default-img/light_logo.png') }}" alt="Logo" class="h-10 hidden dark:block">
            </div>
            @endif

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">{{ __($authData['reset_heading']) }}</h1>
                <p class="text-gray-500 dark:text-gray-400">{{ __($authData['reset_description']) }}</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl mb-6">
                    @foreach ($errors->all() as $error)
                        <p class="text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $email }}">

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Email') }}</label>
                    <input type="email" id="email" name="email" value="{{ $email }}" readonly class="w-full px-4 py-3 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('New Password') }}</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required autofocus class="w-full px-4 py-3 pr-10 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition focus:ring-[var(--theme-color)]">
                        <button type="button" onclick="togglePassword('password', 'eye-open-1', 'eye-closed-1')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300">
                            <svg id="eye-open-1" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.147.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                            </svg>
                            <svg id="eye-closed-1" class="h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z" clip-rule="evenodd" />
                                <path d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Confirm Password') }}</label>
                    <div class="relative">
                        <input type="password" id="password_confirmation" name="password_confirmation" required class="w-full px-4 py-3 pr-10 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition focus:ring-[var(--theme-color)]">
                        <button type="button" onclick="togglePassword('password_confirmation', 'eye-open-2', 'eye-closed-2')" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-700 dark:text-gray-500 dark:hover:text-gray-300">
                            <svg id="eye-open-2" class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12.5a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 0 1 0-1.186A10.004 10.004 0 0 1 10 3c4.257 0 7.893 2.66 9.336 6.41.147.381.147.804 0 1.186A10.004 10.004 0 0 1 10 17c-4.257 0-7.893-2.66-9.336-6.41ZM14 10a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" clip-rule="evenodd" />
                            </svg>
                            <svg id="eye-closed-2" class="h-5 w-5 hidden" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3.28 2.22a.75.75 0 0 0-1.06 1.06l14.5 14.5a.75.75 0 1 0 1.06-1.06l-1.745-1.745a10.029 10.029 0 0 0 3.3-4.38 1.651 1.651 0 0 0 0-1.185A10.004 10.004 0 0 0 9.999 3a9.956 9.956 0 0 0-4.744 1.194L3.28 2.22ZM7.752 6.69l1.092 1.092a2.5 2.5 0 0 1 3.374 3.373l1.091 1.092a4 4 0 0 0-5.557-5.557Z" clip-rule="evenodd" />
                                <path d="m10.748 13.93 2.523 2.523a9.987 9.987 0 0 1-3.27.547c-4.258 0-7.894-2.66-9.337-6.41a1.651 1.651 0 0 1 0-1.186A10.007 10.007 0 0 1 2.839 6.02L6.07 9.252a4 4 0 0 0 4.678 4.678Z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] shadow-lg auth-btn">
                        {{ __($authData['reset_heading']) }}
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Remember your password?') }}
                    <a href="{{ route('login') }}" class="font-semibold hover:underline auth-link">{{ __('Sign In') }}</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Right side: Visual -->
    <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center overflow-hidden auth-gradient-bg" style="--theme-color-22: {{ $themeColor }}22; --theme-color-44: {{ $themeColor }}44;">
        @if($authImageUrl)
        <div class="absolute inset-0">
            <img src="{{ $authImageUrl }}" alt="Auth" class="w-full h-full object-cover opacity-90">
            <div class="absolute inset-0 auth-gradient-overlay" style="--theme-color-ee: {{ $themeColor }}ee; --theme-color-bb: {{ $themeColor }}bb;"></div>
        </div>
        @else
        <div class="absolute inset-0 auth-gradient-full" style="--theme-color: {{ $themeColor }}; --theme-color-dd: {{ $themeColor }}dd;"></div>
        @endif

        <div class="relative z-10 text-white px-16 max-w-xl">
            <div class="mb-8">
                <svg class="w-16 h-16 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h2 class="text-4xl font-bold mb-4">{{ __($authData['reset_right_heading']) }}</h2>
            <p class="text-lg text-white/90 leading-relaxed">{{ __($authData['reset_right_description']) }}</p>
        </div>

        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-20 -left-20 w-96 h-96 rounded-full bg-white/5"></div>
    </div>
</div>

<script>
    function togglePassword(fieldId, eyeOpenId, eyeClosedId) {
        const passwordField = document.getElementById(fieldId);
        const eyeOpen = document.getElementById(eyeOpenId);
        const eyeClosed = document.getElementById(eyeClosedId);

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
