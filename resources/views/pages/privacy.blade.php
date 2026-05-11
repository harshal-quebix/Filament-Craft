@extends('layouts.page')

@php
$cmsData = legalData('privacy');
$main_bg_color = cms('main_bg_color', 'global', '#7369dd');
@endphp

@section('title', __($cmsData['title']))

@section('content')
<!-- Hero Section -->
<div class="relative py-12 sm:py-16 overflow-hidden" class="cms-bg-main">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-20 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
    </div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
        <div class="inline-flex items-center justify-center w-20 h-20 bg-white/20 backdrop-blur-sm rounded-2xl mb-6">
            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        </div>
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold text-white mb-4">{{ __($cmsData['title']) }}</h1>
        <p class="text-lg sm:text-xl text-white/90">{{ __($cmsData['subtitle']) }}</p>
    </div>
</div>

<!-- Content Section -->
<div class="py-12 sm:py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(!empty($cmsData['content']))
            <!-- Dynamic Content from CMS -->
            <div class="bg-white rounded-3xl shadow-lg p-6 sm:p-8 md:p-12">
                <div class="prose prose-lg max-w-none">
                    {!! $cmsData['content'] !!}
                </div>
            </div>
        @else
            <!-- Default Static Content -->
            <div class="bg-white rounded-3xl shadow-lg p-6 sm:p-8 md:p-12 mb-6 sm:mb-8">
                <p class="text-base sm:text-lg text-gray-700 leading-relaxed">
                    {{ __('At CRUD Generator, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our service. Please read this privacy policy carefully.') }}
                </p>
            </div>
        @endif

        <!-- Contact CTA -->
        <div class="mt-8 rounded-3xl shadow-xl p-8 md:p-12 text-center" class="cms-gradient-bottom-right">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl mb-6">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold text-white mb-4">{{ __('Questions About Privacy?') }}</h2>
            <p class="text-xl text-white/90 mb-6">{{ __("We're here to help. Contact us anytime.") }}</p>
            @php $supportEmail = getSetting('support_email'); @endphp
            @if($supportEmail)
            <a href="mailto:{{ $supportEmail }}" class="inline-flex items-center px-8 py-4 bg-white rounded-xl font-semibold hover:bg-gray-50 transition-colors" class="cms-text-main">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                {{ $supportEmail }}
            </a>
            @endif
        </div>
    </div>
</div>
@endsection
