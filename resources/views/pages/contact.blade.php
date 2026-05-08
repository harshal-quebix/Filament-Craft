@extends('layouts.page')

@php
$cmsData = contactData();
$main_bg_color = cms('main_bg_color', 'global', '#7369dd');

$iconSvgs = [
    'mail' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>',
    'location' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>',
    'clock' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>',
];
@endphp

@section('title', __($cmsData['title']))

@section('content')
<!-- Hero Section -->
<div class="cms-bg-main" class="text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-4 sm:mb-6">{{ __($cmsData['title']) }}</h1>
        <p class="text-lg sm:text-xl text-white/90 max-w-2xl mx-auto">{{ __($cmsData['subtitle']) }}</p>
    </div>
</div>

<!-- Contact Section -->
<div class="py-12 sm:py-16 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-8 sm:gap-12">
            <!-- Contact Info -->
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-6 sm:mb-8">{{ __($cmsData['heading']) }}</h2>
                <p class="text-base sm:text-lg text-gray-600 mb-8 sm:mb-10">{{ __($cmsData['intro']) }}</p>

                <div class="space-y-6">
                    @foreach($cmsData['info_items'] as $item)
                    <div class="flex items-start group">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center mr-5 group-hover:scale-110 transition-transform flex-shrink-0" class="cms-bg-main">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $iconSvgs[$item['icon']] ?? $item['icon'] !!}
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 mb-2 text-lg">{{ __($item['title']) }}</h3>
                            <p class="text-gray-600">{{ __($item['line1']) }}</p>
                            @if(!empty($item['line2']))
                            <p class="text-sm text-gray-500 mt-1">{{ __($item['line2']) }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Social Links -->
                @php
                $socialIcons = cms('social_icons', 'footer', []);
                $socialIcons = array_values(array_filter($socialIcons, fn ($icon) => $icon['is_active'] ?? true));
                @endphp
                @if(!empty($socialIcons))
                <div class="mt-12">
                    <h3 class="font-bold text-gray-900 mb-4 text-lg">{{ __('Follow Us') }}</h3>
                    <div class="flex space-x-4">
                        @foreach($socialIcons as $icon)
                        <a href="{{ $icon['url'] }}" target="_blank" rel="noopener"
                            class="w-12 h-12 bg-white border-2 border-gray-200 rounded-xl flex items-center justify-center transition"
                            title="{{ $icon['platform'] }}"
                            onmouseover="this.style.borderColor='{{ $main_bg_color }}'; this.style.color='{{ $main_bg_color }}'"
                            onmouseout="this.style.borderColor='#e5e7eb'; this.style.color='inherit'">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                {!! $icon['icon_svg'] !!}
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Contact Form -->
            <div class="bg-white p-6 sm:p-10 rounded-3xl shadow-xl border border-gray-100">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">{{ __($cmsData['form_title']) }}</h2>
                <p class="text-sm sm:text-base text-gray-600 mb-6 sm:mb-8">{{ __($cmsData['form_subtitle']) }}</p>

                @if(session('success'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST" class="space-y-6" id="contactForm">
                    @csrf
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Full Name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary hover:border-primary transition outline-none" placeholder="{{ __('John Doe') }}" required>
                        @error('name')<span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>@enderror
                        <span class="text-red-500 text-sm mt-1 hidden" data-error="name"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Email Address') }}</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary hover:border-primary transition outline-none" placeholder="{{ __('john@example.com') }}" required>
                        @error('email')<span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>@enderror
                        <span class="text-red-500 text-sm mt-1 hidden" data-error="email"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Subject') }}</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary hover:border-primary transition outline-none" placeholder="{{ __('How can we help?') }}" required>
                        @error('subject')<span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>@enderror
                        <span class="text-red-500 text-sm mt-1 hidden" data-error="subject"></span>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('Message') }}</label>
                        <textarea name="message" rows="5" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-primary hover:border-primary transition outline-none resize-none" placeholder="{{ __('Tell us more about your inquiry...') }}" required>{{ old('message') }}</textarea>
                        @error('message')<span class="text-red-500 text-sm mt-1 block">{{ $message }}</span>@enderror
                        <span class="text-red-500 text-sm mt-1 hidden" data-error="message"></span>
                    </div>
                    <button type="submit" class="w-full text-white px-6 py-4 rounded-xl hover:shadow-xl transition font-bold text-lg" class="cms-bg-main">
                        {{ __('Send Message') }}
                        <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </form>
                <script>
                document.getElementById('contactForm').addEventListener('submit', function(e) {
                    let valid = true;
                    document.querySelectorAll('[data-error]').forEach(el => el.classList.add('hidden'));

                    const name = this.name.value.trim();
                    const email = this.email.value.trim();
                    const subject = this.subject.value.trim();
                    const message = this.message.value.trim();

                    if (!name) {
                        document.querySelector('[data-error="name"]').textContent = '{{ __("The name field is required.") }}';
                        document.querySelector('[data-error="name"]').classList.remove('hidden');
                        valid = false;
                    }
                    if (!email) {
                        document.querySelector('[data-error="email"]').textContent = '{{ __("The email field is required.") }}';
                        document.querySelector('[data-error="email"]').classList.remove('hidden');
                        valid = false;
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                        document.querySelector('[data-error="email"]').textContent = '{{ __("The email must be a valid email address.") }}';
                        document.querySelector('[data-error="email"]').classList.remove('hidden');
                        valid = false;
                    }
                    if (!subject) {
                        document.querySelector('[data-error="subject"]').textContent = '{{ __("The subject field is required.") }}';
                        document.querySelector('[data-error="subject"]').classList.remove('hidden');
                        valid = false;
                    }
                    if (!message) {
                        document.querySelector('[data-error="message"]').textContent = '{{ __("The message field is required.") }}';
                        document.querySelector('[data-error="message"]').classList.remove('hidden');
                        valid = false;
                    }

                    if (!valid) e.preventDefault();
                });
                </script>
            </div>
        </div>
    </div>
</div>
@endsection
