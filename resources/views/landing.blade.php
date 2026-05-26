<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('CRUD Generator - Accelerate your development') }}</title>
    <link rel="icon" type="image/png" href="{{ getFavicon() }}">
    <script src="{{ asset('js/tailwind.min.js') }}" data-navigate-once></script>

    @php
    $cmsData = landingData();
    $main_bg_color = $cmsData['main_bg_color'] ?? '#7369dd';
    $light_bg_color = $cmsData['light_bg_color'] ?? '#f9fafb';
    $text_color = $cmsData['text_color'] ?? '#111827';
    $heading_color = $cmsData['heading_color'] ?? '#111827';
    $font_family = $cmsData['font_family'] ?? 'DM Sans';
    $hero = $cmsData['hero'] ?? [];
    $features = $cmsData['features'] ?? [];
    $steps = $cmsData['steps'] ?? [];
    $cta = $cmsData['cta'] ?? [];
    $footerData = footerData();
    $footerMenus = \App\Models\Menu::getFooterMenus();
    $headerMenus = \App\Models\Menu::getHeaderMenus();
    $authData = authData();
    @endphp

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $main_bg_color }}',
                        'primary-light': '{{ $light_bg_color }}',
                        'primary-dark': '{{ $main_bg_color }}',
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v=2">
</head>
<body class="bg-white antialiased landing-theme-wrapper landing-body" style="--cms-main-bg: {{ $main_bg_color }}; --cms-light-bg: {{ $light_bg_color }}; --cms-text-color: {{ $text_color }}; --cms-heading-color: {{ $heading_color }}; --cms-main-bg-20: {{ $main_bg_color }}20; --cms-main-bg-30: {{ $main_bg_color }}30; --cms-main-bg-aa: {{ $main_bg_color }}aa; --cms-main-bg-dd: {{ $main_bg_color }}dd; --landing-font-family: '{{ $font_family }}';">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20">
                <div class="flex items-center">
                    <a href="{{ route('landing') }}" class="flex items-center">
                        <img src="{{ getLogo('dark') }}" alt="CRUD Generator" class="h-8 sm:h-10 w-auto object-contain">
                    </a>
                </div>
                <div class="flex items-center">
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')" class="md:hidden p-2 text-gray-600 hover:text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="{{ route('landing') }}" class="text-gray-600 hover:text-primary transition font-medium">{{ __('Home') }}</a>
                        @foreach($headerMenus as $menu)
                            <a href="{{ $menu->getRouteUrl() }}" class="text-gray-600 hover:text-primary transition font-medium">
                                {{ __($menu->page_name) }}
                            </a>
                        @endforeach
                        <a href="{{ auth()->check() ? route('filament.admin.pages.dashboard') : route('filament.admin.auth.login') }}" class="text-white px-6 py-2.5 rounded-xl hover:opacity-90 transition font-semibold cms-bg-main">
                            {{ __($authData['header_button_text']) }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('landing') }}" class="block text-gray-600 hover:text-primary hover:bg-gray-50 transition font-medium py-2 px-3 rounded-lg">{{ __('Home') }}</a>
                @foreach($headerMenus as $menu)
                    <a href="{{ $menu->getRouteUrl() }}" class="block text-gray-600 hover:text-primary hover:bg-gray-50 transition font-medium py-2 px-3 rounded-lg">
                        {{ __($menu->page_name) }}
                    </a>
                @endforeach
                <a href="{{ auth()->check() ? route('filament.admin.pages.dashboard') : route('filament.admin.auth.login') }}" class="block text-white px-4 py-2.5 rounded-xl hover:opacity-90 transition font-semibold text-center mt-2 cms-bg-main">{{ __($authData['header_button_text']) }}</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative overflow-hidden bg-gradient-to-br from-[{{ $main_bg_color }}] via-[{{ $main_bg_color }}dd] to-[{{ $main_bg_color }}aa]">
        @php
        $heroImage = $hero['image'] ?? null;
        @endphp

        <!-- Hero Background Image (if uploaded) -->
        @if($heroImage)
        <div class="absolute inset-0 z-0">
            <img src="{{ getImageUrl($heroImage) }}" alt="Hero Background" class="w-full h-full object-cover opacity-30">
            <div class="absolute inset-0 bg-gradient-to-br from-[{{ $main_bg_color }}] via-[{{ $main_bg_color }}dd] to-[{{ $main_bg_color }}aa] opacity-90"></div>
        </div>
        @endif

        <!-- Decorative Vector Background -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-10 right-10 w-96 h-96 bg-white rounded-full blur-3xl opacity-10"></div>
            <div class="absolute bottom-10 left-10 w-80 h-80 bg-white rounded-full blur-3xl opacity-10"></div>
            <svg class="absolute top-20 right-10 w-64 h-64 opacity-10" viewBox="0 0 200 200" fill="none">
                <rect x="20" y="20" width="160" height="160" rx="30" fill="white"/>
            </svg>
            <svg class="absolute top-40 left-20 w-48 h-48 opacity-10" viewBox="0 0 200 200" fill="none">
                <polygon points="100,20 180,180 20,180" fill="white"/>
            </svg>
            <svg class="absolute bottom-32 right-32 w-56 h-56 opacity-10" viewBox="0 0 200 200" fill="none">
                <circle cx="100" cy="100" r="80" fill="white"/>
            </svg>
            <svg class="absolute bottom-20 left-1/3 w-40 h-40 opacity-10" viewBox="0 0 200 200" fill="none">
                <path d="M100 20 L180 100 L100 180 L20 100 Z" fill="white"/>
            </svg>
            <div class="absolute top-1/4 right-1/4 w-3 h-3 bg-white/30 rounded-full"></div>
            <div class="absolute top-1/3 left-1/4 w-2 h-2 bg-white/25 rounded-full"></div>
            <div class="absolute bottom-1/3 right-1/3 w-3 h-3 bg-white/30 rounded-full"></div>
            <div class="absolute bottom-1/4 left-1/3 w-2 h-2 bg-white/20 rounded-full"></div>
            <div class="absolute top-2/3 right-1/2 w-2 h-2 bg-white/25 rounded-full"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid md:grid-cols-2 gap-8 sm:gap-12 md:gap-16 items-center py-8 sm:py-12 md:py-16">
                <!-- Left Content -->
                <div class="text-white">
                    @if(!empty($hero['badge_text']))
                    <div class="inline-flex items-center gap-2 mb-6 px-4 py-2 bg-white/20 backdrop-blur-md rounded-full text-sm font-medium border border-white/30">
                        <span>{{ $hero['badge_text'] }}</span>
                    </div>
                    @endif

                    <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold mb-6 leading-tight">
                        {{ $hero['title'] ?? 'Accelerate your development workflow' }}
                    </h1>
                    <p class="text-lg sm:text-xl md:text-2xl mb-8 text-white/90 leading-relaxed max-w-xl">
                        {{ $hero['description'] ?? '' }}
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-8">
                        <a href="{{ $hero['primary_button_url'] ?? route('filament.admin.auth.login') }}" class="inline-flex items-center justify-center bg-white px-6 sm:px-8 py-3 sm:py-4 rounded-xl text-base sm:text-lg font-bold hover:shadow-2xl hover:scale-105 transition-all duration-200 cms-text-main">
                            {{ $hero['primary_button_text'] ?? 'Start Building' }}
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                            </svg>
                        </a>
                        <a href="{{ $hero['secondary_button_url'] ?? route('guide') }}" class="inline-flex items-center justify-center bg-white/10 backdrop-blur-md text-white px-6 sm:px-8 py-3 sm:py-4 rounded-xl text-base sm:text-lg font-semibold hover:bg-white/20 transition-all duration-200 border border-white/30">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ $hero['secondary_button_text'] ?? 'Watch Demo' }}
                        </a>
                    </div>

                    <!-- Stats -->
                    @if(!empty($hero['stats']))
                    <div class="grid grid-cols-3 gap-3 sm:gap-6">
                        @foreach($hero['stats'] as $stat)
                        <div>
                            <div class="text-2xl sm:text-3xl font-bold mb-1">{{ $stat['label'] ?? '' }}</div>
                            <div class="text-xs sm:text-sm text-white/80">{{ $stat['text'] ?? '' }}</div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Right Illustration -->
                <div class="hidden md:block relative">
                    <div class="relative transform hover:scale-105 transition-transform duration-500">
                        <div class="absolute inset-0 bg-white/20 rounded-3xl blur-2xl"></div>
                        <div class="relative bg-white rounded-2xl shadow-2xl overflow-hidden backdrop-blur-sm">
                            @if(!empty($hero['image']))
                                <!-- Show Hero Image if uploaded -->
                                <img src="{{ getImageUrl($hero['image']) }}" alt="Hero Image" class="w-full h-full object-cover min-h-[500px]">
                            @else
                                <!-- Default Mockup -->
                                <div class="p-8">
                                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                            <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                            <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-600">{{ __('CRUD Builder') }}</div>
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <div class="text-xs font-semibold text-gray-500 mb-2">{{ __('Model Name') }}</div>
                                            <div class="h-10 bg-gradient-to-r from-[{{ $main_bg_color }}20] to-[#f3f4f6] rounded-lg flex items-center px-4">
                                                <div class="w-20 h-3 rounded cms-bg-main-30"></div>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="text-xs font-semibold text-gray-500 mb-2">{{ __('Table Name') }}</div>
                                            <div class="h-10 bg-gradient-to-r from-[{{ $main_bg_color }}20] to-[#f3f4f6] rounded-lg flex items-center px-4">
                                                <div class="w-24 h-3 rounded cms-bg-main-30"></div>
                                            </div>
                                        </div>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <div class="text-xs font-semibold text-gray-500 mb-2">{{ __('Field Type') }}</div>
                                                <div class="h-10 bg-gradient-to-r from-[{{ $main_bg_color }}20] to-[#f3f4f6] rounded-lg flex items-center px-4">
                                                    <div class="w-16 h-3 rounded cms-bg-main-30"></div>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="text-xs font-semibold text-gray-500 mb-2">{{ __('Validation') }}</div>
                                                <div class="h-10 bg-gradient-to-r from-[{{ $main_bg_color }}20] to-[#f3f4f6] rounded-lg flex items-center px-4">
                                                    <div class="w-14 h-3 rounded cms-bg-main-30"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <button class="w-full h-12 text-white rounded-lg font-bold text-sm shadow-lg hover:shadow-xl transition-all duration-200 flex items-center justify-center gap-2 cms-gradient-hero">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            {{ __('Generate CRUD') }}
                                        </button>
                                    </div>
                                    <div class="mt-6 p-4 bg-gray-900 rounded-lg">
                                        <div class="flex items-center gap-2 mb-3">
                                            <div class="w-2 h-2 rounded-full bg-green-400"></div>
                                            <div class="text-xs text-gray-400 font-mono">{{ __('Generated Code') }}</div>
                                        </div>
                                        <div class="space-y-2 font-mono text-xs">
                                            <div class="text-purple-400">&lt;?php</div>
                                            <div class="text-blue-400">class <span class="text-yellow-400">Product</span></div>
                                            <div class="text-gray-500">// Auto-generated...</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="py-8 sm:py-12 md:py-16 cms-bg-light">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12 sm:mb-16">
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-4 cms-text-heading">{{ __('Everything you need') }}</h2>
                <p class="text-lg sm:text-xl text-gray-600">{{ __('Powerful features to build production-ready applications') }}</p>
            </div>
            <div class="grid md:grid-cols-3 gap-6 sm:gap-8">
                @foreach($features as $feature)
                <div class="bg-white p-6 sm:p-8 rounded-2xl border border-gray-100 card-hover">
                    <div class="w-14 h-14 rounded-xl flex items-center justify-center mb-6 cms-bg-main-20">
                        <svg class="w-7 h-7" fill="none" stroke="{{ $main_bg_color }}" viewBox="0 0 24 24">
                            {!! $feature['icon'] ?? '' !!}
                        </svg>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold mb-3 cms-text-heading">{{ $feature['title'] ?? '' }}</h3>
                    <p class="text-gray-600 leading-relaxed">{{ $feature['description'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="pt-0 pb-8 sm:py-12 md:py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-8 sm:mb-12">
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4 cms-text-heading">{{ __('How it works') }}</h2>
                <p class="text-base sm:text-lg text-gray-600">{{ __('Four simple steps to generate your CRUD') }}</p>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
                @foreach($steps as $step)
                <div class="text-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 text-white rounded-2xl flex items-center justify-center text-2xl sm:text-3xl font-bold mx-auto mb-4 sm:mb-5 shadow-lg cms-gradient-step">
                        {{ $step['number'] ?? '' }}
                    </div>
                    <h3 class="text-base sm:text-lg font-bold mb-2 cms-text-heading">{{ $step['title'] ?? '' }}</h3>
                    <p class="text-xs sm:text-sm text-gray-600">{{ $step['description'] ?? '' }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-8 sm:py-12 md:py-16 cms-gradient-hero text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-3 sm:mb-4">{{ $cta['title'] ?? 'Ready to accelerate your development?' }}</h2>
            <p class="text-base sm:text-lg mb-6 sm:mb-8 text-white/80">{{ $cta['subtitle'] ?? '' }}</p>
            <a href="{{ $cta['button_url'] ?? route('filament.admin.auth.login') }}" class="inline-flex items-center justify-center bg-white px-6 sm:px-8 py-3 sm:py-4 rounded-xl text-sm sm:text-base font-semibold hover:shadow-2xl transition landing-glow cms-text-main">
                {{ $cta['button_text'] ?? 'Get Started Now' }}
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-300">
        <div class="absolute top-0 left-0 right-0 h-1 cms-gradient-top-border"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 md:py-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 md:gap-10 mb-4 sm:mb-6">
                <div class="col-span-2 md:col-span-1">
                    <a href="{{ route('landing') }}" class="flex items-center space-x-2 mb-4 sm:mb-6">
                        <img src="{{ getLogo('light') }}" alt="CRUD Generator" class="h-8 sm:h-10 w-auto object-contain">
                    </a>
                    <p class="text-sm sm:text-base text-gray-400 leading-relaxed mb-4 sm:mb-6">{{ __($footerData['description']) }}</p>

                    @if(!empty($footerData['social_icons']))
                    <div class="flex space-x-3">
                        @foreach($footerData['social_icons'] as $icon)
                        <a href="{{ $icon['url'] }}" target="_blank" rel="noopener"
                            class="w-10 h-10 bg-gray-800 rounded-lg flex items-center justify-center transition-all duration-300 hover:scale-110"
                            onmouseover="this.style.backgroundColor='{{ $main_bg_color }}'"
                            onmouseout="this.style.backgroundColor='#1f2937'"
                            title="{{ $icon['platform'] }}">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                {!! $icon['icon_svg'] !!}
                            </svg>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                <div>
                    <h4 class="text-white font-bold mb-4 sm:mb-6 text-base sm:text-lg">{{ __('Product') }}</h4>
                    <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base">
                        <li><a href="{{ route('landing') }}" class="text-gray-400 hover:text-white transition">{{ __('Home') }}</a></li>
                        <li><a href="{{ route('guide') }}" class="text-gray-400 hover:text-white transition">{{ __('Documentation') }}</a></li>
                        <li><a href="{{ route('filament.admin.auth.login') }}" class="text-gray-400 hover:text-white transition">{{ __('Get Started') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-4 sm:mb-6 text-base sm:text-lg">{{ __('Company') }}</h4>
                    <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base">
                        <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-white transition">{{ __('About Us') }}</a></li>
                        <li><a href="{{ route('contact') }}" class="text-gray-400 hover:text-white transition">{{ __('Contact') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-4 sm:mb-6 text-base sm:text-lg">{{ __('Legal') }}</h4>
                    <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base">
                        <li><a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white transition">{{ __('Privacy Policy') }}</a></li>
                        <li><a href="{{ route('terms') }}" class="text-gray-400 hover:text-white transition">{{ __('Terms & Conditions') }}</a></li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 pt-4 sm:pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
                    <p class="text-sm sm:text-base text-gray-400">
                        {{ $footerData['copyright_text'] }}
                    </p>
                    @if(!empty($footerData['copyright_pages']))
                    <div class="flex items-center space-x-4 sm:space-x-6 text-sm sm:text-base">
                        @foreach($footerData['copyright_pages'] as $page)
                            <a href="{{ $page->getRouteUrl() }}" class="text-gray-400 hover:text-white transition">{{ __($page->page_name) }}</a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
