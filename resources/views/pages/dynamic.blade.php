@extends('layouts.page')

@php
$main_bg_color = cms('main_bg_color', 'global', '#7369dd');
@endphp

@section('title', __($menu->page_name))

@section('content')
<!-- Hero Section -->
<div class="cms-bg-main text-white py-12 sm:py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl sm:text-5xl md:text-6xl font-bold mb-4 sm:mb-6">{{ __($menu->page_name) }}</h1>
    </div>
</div>

<!-- Content Section - Direct display with proper spacing -->
<div class="py-12 sm:py-16 bg-white">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="content-editor-output">
            {!! $menu->content !!}
        </div>
    </div>
</div>
@endsection
