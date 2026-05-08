@extends('layouts.page')

@php
$cmsData = aboutData();
$main_bg_color = cms('main_bg_color', 'global', '#7369dd');
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

<div class="py-12 sm:py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-2 gap-8 sm:gap-12 mb-16 sm:mb-20">
            <div class="bg-white p-8 sm:p-10 rounded-3xl shadow-lg">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center mb-6" class="cms-bg-main">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">{{ __($cmsData['story_title']) }}</h2>
                <p class="text-base sm:text-lg text-gray-600 leading-relaxed">{{ __($cmsData['story_content']) }}</p>
            </div>

            <div class="bg-white p-8 sm:p-10 rounded-3xl shadow-lg">
                <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-2xl flex items-center justify-center mb-6" class="cms-bg-main">
                    <svg class="w-7 h-7 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">{{ __($cmsData['mission_title']) }}</h2>
                <p class="text-base sm:text-lg text-gray-600 leading-relaxed">{{ __($cmsData['mission_content']) }}</p>
            </div>
        </div>

        <div class="bg-white p-8 sm:p-12 rounded-3xl shadow-lg">
            <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6 sm:mb-8 text-center">{{ __($cmsData['why_title']) }}</h2>
            <div class="grid md:grid-cols-3 gap-6 sm:gap-8">
                @foreach($cmsData['why_items'] as $item)
                <div class="text-center">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl flex items-center justify-center mx-auto mb-4 sm:mb-6" class="cms-bg-main-20">
                        <svg class="w-8 h-8 sm:w-10 sm:h-10" fill="currentColor" viewBox="0 0 20 20" class="cms-text-main">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-2 sm:mb-3">{{ __($item['title']) }}</h3>
                    <p class="text-sm sm:text-base text-gray-600">{{ __($item['description']) }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
