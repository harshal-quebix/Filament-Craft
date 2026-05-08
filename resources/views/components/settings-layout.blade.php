@props(['title'])

<div class="settings-simple-layout">
    <div class="settings-sidebar-card">
        <h3 class="settings-sidebar-title">{{ __('Settings') }}</h3>
        <nav class="settings-sidebar-nav">
            <a href="{{ url('settings/system-settings') }}" class="{{ request()->is('settings/system-settings') ? 'active' : '' }}">
                {{ __('System Settings') }}
            </a>
            <a href="{{ url('settings/brand-settings') }}" class="{{ request()->is('settings/brand-settings') ? 'active' : '' }}">
                {{ __('Brand Settings') }}
            </a>
            <a href="{{ url('settings/email-settings') }}" class="{{ request()->is('settings/email-settings') ? 'active' : '' }}">
                {{ __('Email Settings') }}
            </a>
            <a href="{{ url('settings/cookie-settings') }}" class="{{ request()->is('settings/cookie-settings') ? 'active' : '' }}">
                {{ __('Cookie Settings') }}
            </a>
            <a href="{{ url('settings/seo-settings') }}" class="{{ request()->is('settings/seo-settings') ? 'active' : '' }}">
                {{ __('SEO Settings') }}
            </a>

        </nav>
    </div>

    <div class="settings-main-card">
        {{ $slot }}
    </div>
</div>
