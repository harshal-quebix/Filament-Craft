<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $metaTitle)</title>
    <meta name="description" content="{{ $metaDescription }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ env('APP_URL') }}">
    <meta property="og:title" content="@yield('title', $metaTitle)">
    <meta property="og:description" content="{{ $metaDescription }}">
    @if($metaImageUrl)
    <meta property="og:image" content="{{ $metaImageUrl }}?{{ time() }}">
    @endif

    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <link rel="stylesheet" href="{{ route('theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
</head>
<body>
    <div class="min-h-screen flex">
        <!-- Page Header -->
        <div class="absolute top-0 left-0 right-0 z-20 bg-white/10 backdrop-blur-sm border-b border-white/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <h1 class="text-xl font-semibold text-white">@yield('header-title', \App\Models\Setting::where('key', 'site_title')->value('value') ?? 'Craft Laravel')</h1>
                    <div class="text-sm text-white/80">
                        @yield('header-subtitle', __('Secure Access Dashboard'))
                    </div>
                </div>
            </div>
        </div>

        <!-- LEFT SIDE - SVG ILLUSTRATION -->
        <div class="hidden lg:flex w-1/2 items-center justify-center text-white relative overflow-hidden pt-20 theme-bg-gradient">
            <div class="h-full flex items-center justify-center p-8 relative z-10">
                <div class="max-w-[700px] mx-auto w-full">
                    <!-- Animated SVG Illustration -->
                    <svg width="714" height="704" viewBox="0 0 714 704" fill="none" xmlns="http://www.w3.org/2000/svg" class="size-full">
                        <rect x="22" y="10" width="670" height="670" rx="60" class="theme-fill-20" />
                        <path class="svg-animated-border theme-fill" d="M54 598V108.41V102C54 74.3858 76.3858 52 104 52H610C637.614 52 660 74.3858 660 102V598C660 625.614 637.614 648 610 648H104C76.3858 648 54 625.614 54 598Z" stroke="white" stroke-width="3" stroke-dasharray="8 8" />
                        <g><circle cx="357" cy="648" r="45" class="theme-fill-e6 svg-pulse-delayed" /><circle cx="357" cy="648" r="44.8" stroke="white" stroke-width="0.3" /></g>
                        <g><circle cx="660" cy="648" r="45" class="theme-fill-e6 svg-pulse" /><circle cx="660" cy="648" r="44.8" stroke="white" stroke-width="0.3" /></g>
                        <g><circle cx="54" cy="648" r="45" class="theme-fill-e6 svg-pulse" /><circle cx="54" cy="648" r="44.8" stroke="white" stroke-width="0.3" /></g>
                        <rect x="117" y="118" width="480" height="463" rx="24" class="theme-fill-cc" stroke="white" stroke-width="0.9" />
                        <rect x="137" y="138" width="440" height="424" rx="24" class="theme-fill" stroke="white" stroke-width="0.9" />
                        <path d="M250 197H488C495 197 502 203 502 210V540C502 547 495 554 488 554H250C242 554 236 547 236 540V210C236 203 242 197 250 197Z" class="theme-fill-cc" stroke="white" stroke-width="0.9" />
                        <path d="M490 210V540H249V210H490Z" class="theme-fill" stroke="white" stroke-width="0.9" />
                        <circle cx="301" cy="265" r="25" fill="white" class="svg-pulse" />
                        <rect x="340" y="250" width="120" height="8" rx="4" fill="white" opacity="0.8" />
                        <rect x="340" y="265" width="80" height="6" rx="3" fill="white" opacity="0.6" />
                        <rect x="270" y="320" width="60" height="40" rx="8" fill="white" opacity="0.9" class="svg-pulse-delayed" />
                        <rect x="350" y="320" width="60" height="40" rx="8" fill="white" opacity="0.9" class="svg-pulse" />
                        <rect x="430" y="320" width="60" height="40" rx="8" fill="white" opacity="0.9" class="svg-pulse-delayed" />
                        <rect x="270" y="380" width="220" height="100" rx="8" fill="white" opacity="0.1" />
                        <path d="M290 450L320 430L350 440L380 420L410 435L440 415" stroke="white" stroke-width="3" fill="none" opacity="0.8" class="svg-pulse" />
                        <g class="svg-rotate"><circle cx="520" cy="500" r="20" fill="white" opacity="0.9" /><path d="M520 485L525 495L515 495Z" class="theme-fill" /><path d="M520 515L515 505L525 505Z" class="theme-fill" /><path d="M505 500L515 495L515 505Z" class="theme-fill" /><path d="M535 500L525 505L525 495Z" class="theme-fill" /></g>
                        <g class="svg-pulse-delayed"><circle cx="180" cy="400" r="15" fill="white" opacity="0.9" /><path d="M180 390v20M175 395h10c2 0 3-1 3-3s-1-3-3-3h-4c-2 0-3-1-3-3s1-3 3-3h10" class="theme-stroke" stroke-width="2" fill="none" /></g>
                        <g class="svg-pulse"><circle cx="550" cy="300" r="12" fill="white" opacity="0.9" /><circle cx="570" cy="300" r="12" fill="white" opacity="0.7" /><circle cx="590" cy="300" r="12" fill="white" opacity="0.5" /></g>
                    </svg>
                </div>
            </div>
        </div>

        <!-- RIGHT SIDE - FORM CONTENT -->
        <div class="w-full lg:w-1/2 flex items-center justify-center bg-gray-50">
            <div class="w-full max-w-md p-8">
                <div class="bg-white rounded-2xl shadow-xl p-8">
                    @yield('form-content')
                </div>
            </div>
        </div>
    </div>
</body>
</html>
