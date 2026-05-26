@props(['title'])

<div class="settings-simple-layout">
    <div class="settings-sidebar-card">
        <h3 class="settings-sidebar-title">{{ __('Profile') }}</h3>
        <nav class="settings-sidebar-nav">
            <a href="{{ url('profile/personal-info') }}" class="{{ request()->is('profile/personal-info') ? 'active' : '' }}">
                {{ __('Personal Information') }}
            </a>
            <a href="{{ url('profile/change-password') }}" class="{{ request()->is('profile/change-password') ? 'active' : '' }}">
                {{ __('Change Password') }}
            </a>
        </nav>
    </div>

    <div class="settings-main-card">
        {{ $slot }}
    </div>
</div>
