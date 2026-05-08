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
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">{{ __($authData['forgot_heading']) }}</h1>
                <p class="text-gray-500 dark:text-gray-400">{{ __($authData['forgot_description']) }}</p>
            </div>

            @if (session('status'))
                <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">{{ __('Email Address') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus class="w-full px-4 py-3 border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition" class="auth-input-ring">
                    @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full text-white font-semibold py-3.5 px-4 rounded-xl transition-all duration-200 transform hover:scale-[1.02] shadow-lg" class="auth-btn">
                        {{ __('Send Reset Link') }}
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('Remember your password?') }}
                    <a href="{{ route('login') }}" class="font-semibold hover:underline" class="auth-link">{{ __('Back to Login') }}</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Right side: Visual -->
    <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center overflow-hidden auth-gradient-bg" style="--auth-theme-color-22: {{ $themeColor }}22; --auth-theme-color-44: {{ $themeColor }}44;">
        @if($authImageUrl)
        <div class="absolute inset-0">
            <img src="{{ $authImageUrl }}" alt="Auth" class="w-full h-full object-cover opacity-90">
            <div class="absolute inset-0 auth-gradient-overlay" style="--auth-theme-color-ee: {{ $themeColor }}ee; --auth-theme-color-bb: {{ $themeColor }}bb;"></div>
        </div>
        @else
        <div class="absolute inset-0 auth-gradient-full" style="--auth-theme-color: {{ $themeColor }}; --auth-theme-color-dd: {{ $themeColor }}dd;"></div>
        @endif

        <div class="relative z-10 text-white px-16 max-w-xl">
            <div class="mb-8">
                <svg class="w-16 h-16 text-white/90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h2 class="text-4xl font-bold mb-4">{{ __($authData['forgot_right_heading']) }}</h2>
            <p class="text-lg text-white/90 leading-relaxed">{{ __($authData['forgot_right_description']) }}</p>
        </div>

        <div class="absolute -top-20 -right-20 w-80 h-80 rounded-full bg-white/10"></div>
        <div class="absolute -bottom-20 -left-20 w-96 h-96 rounded-full bg-white/5"></div>
    </div>
</div>
@endsection
