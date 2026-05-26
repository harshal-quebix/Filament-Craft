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

        // Get logo
        $darkLogo = null;
        $logoSetting = Setting::where('key', 'logo_dark')->first();
        if ($logoSetting && $logoSetting->value) {
            $darkLogo = getImageUrl($logoSetting->value);
        }
    } catch (\Exception $e) {
        $themeColor = '#3b82f6';
        $darkLogo = null;
    }
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
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">{{ __('Two-Factor Authentication') }}</h2>
                <p class="text-gray-600 dark:text-gray-300">{{ __('Enter the 6-digit code from your authenticator app') }}</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('2fa.verify') }}" class="space-y-6">
                @csrf

                <div>
                    <label for="verification_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('Verification Code') }}</label>
                    <input type="text" id="verification_code" name="verification_code" required autofocus
                        maxlength="6" placeholder="{{ __('000000') }}"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white rounded-lg focus:outline-none focus:ring-2 focus:border-transparent text-center text-lg tracking-widest focus:ring-[var(--theme-color)]">
                </div>

                <div class="mt-8">
                    <button type="submit"
                        class="w-full text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] shadow-lg auth-btn"
                        class="auth-btn">
                        {{ __('Verify') }}
                    </button>
                </div>
            </form>

            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ __('Need to sign out?') }}</p>
                <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 border border-red-300 dark:border-red-700 rounded-lg text-sm font-medium text-red-700 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200 focus:ring-[var(--theme-color)]">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        {{ __('Logout') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
