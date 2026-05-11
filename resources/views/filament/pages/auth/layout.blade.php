@php
    use App\Models\Setting;
      
    try {
        $themeColor = getAuthThemeColor();

        // Get auth page image - same method as AdminPanelProvider
        $authImageUrl = getImageUrl(Setting::where('key', 'auth_page_image')->value('value'));

        // Get SEO settings
        $seoSettings = Setting::whereIn('key', ['meta_title', 'meta_keywords', 'meta_description', 'meta_image'])
            ->pluck('value', 'key')
            ->toArray();
        $metaTitle = Setting::where('key', 'site_title')->value('value') ?? config('app.name', 'Craft Laravel');
        $metaDescription = $seoSettings['meta_description'] ?? __('Secure admin portal');
        $metaImage = $seoSettings['meta_image'] ?? null;

        // Get font family
        $fontFamily = Setting::where('key', 'font_family')->first()?->value ?? 'Inter';

        // Get meta image URL
        $metaImageUrl = getImageUrl($metaImage);
    } catch (\Exception $e) {
        $themeColor = '#3b82f6';
        $metaTitle = config('app.name', 'Craft Laravel');
        $metaDescription = __('Secure admin portal');
        $metaImageUrl = null;
        $fontFamily = 'Inter';
    }
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle }}</title>
    @php
        $faviconUrl = null;
        try {
            $faviconSetting = \App\Models\Setting::where('key', 'favicon')->first();
            if ($faviconSetting && $faviconSetting->value) {
                $faviconUrl = getImageUrl($faviconSetting->value);
            }
        } catch (\Exception $e) {}
        $faviconUrl = $faviconUrl ?? asset('default-img/favicon.png');
    @endphp
    <link rel="icon" type="image/png" href="{{ $faviconUrl }}">
    <meta name="description" content="{{ $metaDescription }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ config('app.url') }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    @if ($metaImageUrl)
        <meta property="og:image" content="{{ $metaImageUrl }}?{{ time() }}">
    @endif

    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <style>
        :root {
            --theme-color: {{ $themeColor }};
            --theme-color-20: {{ $themeColor }}20;
            --theme-color-cc: {{ $themeColor }}CC;
            --theme-color-e6: {{ $themeColor }}E6;
            --auth-font-family: "{{ $fontFamily }}", system-ui, sans-serif;
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/-auththeme.css') }}">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { 'sans': ['Inter', 'system-ui', 'sans-serif'] } } }
        }
        if (!localStorage.theme) { localStorage.theme = 'light'; }
        if (localStorage.theme === 'dark') { document.documentElement.classList.add('dark'); }
    </script>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
</head>

<body class="font-sans bg-gray-50 dark:bg-gray-950 auth-dark-root">
    <!-- Language Dropdown -->
    <div class="absolute top-4 right-4 z-50">
        @php
            $currentLocale = app()->getLocale();
            $languages = [
                'en' => ['name' => 'English', 'flag' => '🇺🇸'],
                'ar' => ['name' => 'العربية', 'flag' => '🇸🇦'],
                'fr' => ['name' => 'Français', 'flag' => '🇫🇷'],
            ];
            $currentLang = $languages[$currentLocale] ?? $languages['en'];
        @endphp
        <div class="relative" x-data="{ isOpen: false }">
            <button type="button"
                    class="flex items-center gap-2 rounded-lg p-2 text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 shadow-md hover:shadow-lg transition-all duration-200"
                    @click="isOpen = ! isOpen">
                <span class="text-base">{{ $currentLang['flag'] }}</span>
                <span class="hidden sm:block">{{ $currentLang['name'] }}</span>
                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <div x-show="isOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="isOpen = false"
                 class="absolute right-0 top-full z-10 mt-2 w-40 rounded-lg bg-white dark:bg-gray-800 shadow-lg ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="p-1">
                    @foreach($languages as $locale => $lang)
                        <a href="{{ request()->fullUrlWithQuery(['locale' => $locale]) }}"
                           class="flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors {{ $currentLocale === $locale ? 'bg-gray-50 dark:bg-gray-700' : '' }}">
                            <span class="text-base">{{ $lang['flag'] }}</span>
                            <span>{{ $lang['name'] }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="min-h-screen flex items-center justify-center">
        @yield('content')
    </div>
    @include('components.cookie-banner', ['themeColor' => $themeColor])

    <script defer src="{{ asset('js/alpine.min.js') }}"></script>
</body>

</html>
