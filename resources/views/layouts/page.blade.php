@php
$main_bg_color = cms('main_bg_color', 'global', '#7369dd');
$font_family = cms('font_family', 'global', 'DM Sans');
$headerMenus = \App\Models\Menu::getHeaderMenus();
$footerMenus = \App\Models\Menu::getFooterMenus();
$footerData = footerData();
$authData = authData();
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ __('CRUD Generator') }}</title>
    <link rel="icon" type="image/png" href="{{ getFavicon() }}">
    <script src="{{ asset('js/tailwind.min.js') }}"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '{{ $main_bg_color }}',
                        'primary-light': '{{ $main_bg_color }}20',
                        'primary-dark': '{{ $main_bg_color }}',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('css/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}?v=2">
</head>

<body class="bg-gray-50 page-theme-wrapper landing-body" style="--cms-main-bg: {{ $main_bg_color }}; --cms-light-bg: {{ $main_bg_color }}08; --cms-text-color: #111827; --cms-heading-color: #111827; --cms-main-bg-20: {{ $main_bg_color }}20; --cms-main-bg-30: {{ $main_bg_color }}30; --cms-main-bg-aa: {{ $main_bg_color }}aa; --cms-main-bg-dd: {{ $main_bg_color }}dd; --landing-font-family: '{{ $font_family }}';">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16 sm:h-20">
                <div class="flex items-center">
                    <a href="{{ route('landing') }}" class="flex items-center">
                        <img src="{{ getLogo('dark') }}" alt="CRUD Generator"
                            class="h-8 sm:h-10 w-auto object-contain">
                    </a>
                </div>
                <div class="flex items-center">
                    <button onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
                        class="md:hidden p-2 text-gray-600 hover:text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <div class="hidden md:flex items-center space-x-6">
                        <a href="{{ route('landing') }}" class="text-gray-600 hover:text-primary transition">{{ __('Home') }}</a>

                        {{-- Dynamic Header Menus --}}
                        @foreach($headerMenus as $menu)
                            <a href="{{ $menu->getRouteUrl() }}" class="text-gray-600 hover:text-primary transition">
                                {{ __($menu->page_name) }}
                            </a>
                        @endforeach

                        <a href="{{ route('filament.admin.auth.login') }}"
                            class="text-white px-6 py-2.5 rounded-xl hover:opacity-90 transition font-semibold cms-bg-main">
                            {{ __($authData['header_button_text']) }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden bg-white border-t border-gray-100">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('landing') }}"
                    class="block text-gray-600 hover:text-primary hover:bg-gray-50 transition font-medium py-2 px-3 rounded-lg">{{ __('Home') }}</a>

                {{-- Dynamic Header Menus Mobile --}}
                @foreach($headerMenus as $menu)
                    <a href="{{ $menu->getRouteUrl() }}"
                        class="block text-gray-600 hover:text-primary hover:bg-gray-50 transition font-medium py-2 px-3 rounded-lg">
                        {{ __($menu->page_name) }}
                    </a>
                @endforeach

                <a href="{{ route('filament.admin.auth.login') }}"
                    class="block text-white px-4 py-2.5 rounded-xl hover:opacity-90 transition font-semibold text-center mt-2 cms-bg-main">{{ __($authData['header_button_text']) }}</a>
            </div>
        </div>
    </nav>

    @yield('content')

    @if (session('success'))
        <div class="page-toast" id="toast">
            <div class="flex items-center space-x-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-semibold">{{ session('success') }}</span>
            </div>
        </div>
        <script>
            setTimeout(() => {
                document.getElementById('toast').classList.add('hide');
                setTimeout(() => document.getElementById('toast').remove(), 300);
            }, 3000);
        </script>
    @endif

    <!-- Footer -->
    <footer class="relative bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 text-gray-300">
        <!-- Decorative top border -->
        <div class="absolute top-0 left-0 right-0 h-1 cms-gradient-top-border"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 md:py-10">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 md:gap-10 mb-4 sm:mb-6">
                <!-- Brand Section -->
                <div class="col-span-2 md:col-span-1">
                    <a href="{{ route('landing') }}" class="flex items-center space-x-2 mb-4 sm:mb-6">
                        <img src="{{ getLogo('light') }}" alt="CRUD Generator"
                            class="h-8 sm:h-10 w-auto object-contain">
                    </a>
                    <p class="text-sm sm:text-base text-gray-400 leading-relaxed mb-4 sm:mb-6">
                        {{ __($footerData['description']) }}
                    </p>

                    {{-- Dynamic Social Links --}}
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

                {{-- Dynamic Footer Menus Grouped by Categories --}}
                @php
                    $footerMenuChunks = $footerMenus->chunk(ceil($footerMenus->count() / 2));
                @endphp

                <!-- Dynamic Footer Column 1 -->
                @if($footerMenuChunks->count() > 0)
                <div>
                    <h4 class="text-white font-bold mb-4 sm:mb-6 text-base sm:text-lg">{{ __('Pages') }}</h4>
                    <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base">
                        @foreach($footerMenuChunks->get(0, collect()) as $menu)
                            <li>
                                <a href="{{ $menu->getRouteUrl() }}" class="text-gray-400 hover:text-white transition">
                                    {{ __($menu->page_name) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Dynamic Footer Column 2 -->
                @if($footerMenuChunks->count() > 1)
                <div>
                    <h4 class="text-white font-bold mb-4 sm:mb-6 text-base sm:text-lg">{{ __('More') }}</h4>
                    <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base">
                        @foreach($footerMenuChunks->get(1, collect()) as $menu)
                            <li>
                                <a href="{{ $menu->getRouteUrl() }}" class="text-gray-400 hover:text-white transition">
                                    {{ __($menu->page_name) }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Legal Links (Fixed) -->
                <div>
                    <h4 class="text-white font-bold mb-4 sm:mb-6 text-base sm:text-lg">{{ __('Legal') }}</h4>
                    <ul class="space-y-2 sm:space-y-3 text-sm sm:text-base">
                        <li>
                            <a href="{{ route('privacy') }}" class="text-gray-400 hover:text-white transition">
                                {{ __('Privacy Policy') }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('terms') }}" class="text-gray-400 hover:text-white transition">
                                {{ __('Terms & Conditions') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="border-t border-gray-800 pt-4 sm:pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center space-y-3 md:space-y-0">
                    <p class="text-sm sm:text-base text-gray-400">
                        {{ $footerData['copyright_text'] }}
                    </p>
                    @if(!empty($footerData['copyright_pages']))
                    <div class="flex items-center space-x-4 sm:space-x-6 text-sm sm:text-base">
                        @foreach($footerData['copyright_pages'] as $page)
                            <a href="{{ $page->getRouteUrl() }}"
                                class="text-gray-400 hover:text-white transition">{{ __($page->page_name) }}</a>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </footer>
</body>

</html>
