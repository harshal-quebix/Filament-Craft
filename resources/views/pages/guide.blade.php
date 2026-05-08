@extends('layouts.page')

@php
$cmsData = guideData();
$main_bg_color = cms('main_bg_color', 'global', '#7369dd');
@endphp

@section('title', __($cmsData['title']))

@section('content')
<!-- Hero Section -->
<div class="cms-bg-main" class="text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center justify-center w-16 h-16 sm:w-20 sm:h-20 bg-white/20 rounded-3xl mb-4 sm:mb-6">
            <svg class="w-8 h-8 sm:w-10 sm:h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                {!! $cmsData['hero_icon'] !!}
            </svg>
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-4 sm:mb-6">{{ __($cmsData['title']) }}</h1>
        <p class="text-lg sm:text-xl text-white/90 max-w-2xl mx-auto">{{ __($cmsData['subtitle']) }}</p>
    </div>
</div>

<div class="py-12 sm:py-16 bg-gray-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="space-y-8 sm:space-y-12">
            <section>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6 sm:mb-8 text-center">{{ __($cmsData['steps_title']) }}</h2>
                <div class="grid md:grid-cols-2 gap-4 sm:gap-6">
                    @foreach($cmsData['steps'] as $step)
                    <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition">
                        <div class="w-14 h-14 sm:w-16 sm:h-16 text-white rounded-2xl flex items-center justify-center font-bold text-xl sm:text-2xl mb-4 sm:mb-6" class="cms-bg-main">{{ $step['number'] }}</div>
                        <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-3">{{ __($step['title']) }}</h3>
                        <p class="text-sm sm:text-base text-gray-600 leading-relaxed">{{ __($step['description']) }}</p>
                    </div>
                    @endforeach
                </div>
            </section>

            <section>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6 sm:mb-8 text-center">{{ __('Available Field Types') }}</h2>
                <div class="grid md:grid-cols-3 gap-4 sm:gap-6">
                    @foreach($cmsData['field_types'] as $fieldType)
                    <div class="bg-white p-6 rounded-2xl shadow-lg border border-gray-100 hover:shadow-xl transition">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4" class="cms-bg-main-20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="cms-text-main">
                                {!! $fieldType['icon'] !!}
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 mb-2 text-lg">{{ __($fieldType['title']) }}</h3>
                        <p class="text-gray-600 text-sm">{{ __($fieldType['description']) }}</p>
                    </div>
                    @endforeach
                </div>
            </section>

            <section>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6 sm:mb-8 text-center">{{ __('Relationships') }}</h2>
                <div class="bg-white p-8 sm:p-10 rounded-2xl shadow-lg border border-gray-100">
                    <ul class="space-y-4">
                        @foreach($cmsData['relations'] as $relation)
                        <li class="flex items-start">
                            <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20" class="cms-text-main">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <strong class="text-gray-900">{{ __($relation['title']) }}:</strong>
                                <span class="text-gray-600">{{ __($relation['description']) }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section>
                <h2 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-6 sm:mb-8 text-center">{{ __('Best Practices') }}</h2>
                <div class="p-8 sm:p-10 rounded-2xl shadow-lg" class="cms-gradient-bottom-right-fade">
                    <ul class="space-y-3 text-gray-700">
                        @foreach($cmsData['best_practices'] as $practice)
                        <li class="flex items-start">
                            <span class="mr-2" class="cms-text-main">✓</span>
                            <span>{{ __($practice['text']) }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </section>

            <section class="cms-bg-main" class="text-white p-8 sm:p-12 rounded-3xl shadow-2xl text-center">
                <h2 class="text-3xl font-bold mb-4">{{ __($cmsData['help_title']) }}</h2>
                <p class="text-white/90 mb-8 text-lg">{{ __($cmsData['help_content']) }}</p>
                <a href="{{ route('contact') }}" class="inline-flex items-center bg-white px-8 py-4 rounded-xl hover:shadow-2xl transition font-bold text-lg" class="cms-text-main">
                    {{ __('Contact Support') }}
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                    </svg>
                </a>
            </section>
        </div>
    </div>
</div>
@endsection
