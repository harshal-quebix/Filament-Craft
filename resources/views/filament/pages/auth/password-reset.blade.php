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

// Get auth page image - same method as login page
$authImageUrl = null;
$authSetting = Setting::where('key', 'auth_page_image')->first();
if ($authSetting && $authSetting->value) {
$authImageUrl = getImageUrl($authSetting->value);
}

// Get logo - same method as AdminPanelProvider
$darkLogo = null;
$logoSetting = Setting::where('key', 'logo_dark')->first();
if ($logoSetting && $logoSetting->value) {
$darkLogo = getImageUrl($logoSetting->value);
}
} catch (\Exception $e) {
$themeColor = '#3b82f6';
$darkLogo = null;
}

$authData = authData();
@endphp

@extends('filament.pages.auth.layout')

@section('content')
    <div class="w-full max-w-lg px-8 py-16">
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
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __($authData['forgot_heading']) }}</h2>
                <p class="text-gray-600 dark:text-gray-300">{{ __($authData['forgot_description']) }}</p>
            </div>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Email Address') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:border-transparent focus:ring-[var(--theme-color)]">
                    @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-8">
                    <button type="submit"
                        class="auth-btn w-full text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg">
                        {{ __('Send Reset Link') }}
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Remember your password?') }}
                    <a href="{{ route('login') }}" class="auth-link font-medium hover:underline">{{ __('Back to Login') }}</a>
                </p>
            </div>
        </div>
    </div>
@endsection
