@if($strictlyNecessaryCookies && !request()->cookie('cookie_consent'))
<div id="cookie-banner" class="fixed bottom-20 left-4 right-4 md:left-6 md:right-6 bg-white dark:bg-gray-800 rounded-xl shadow-2xl border border-gray-200 dark:border-gray-700 z-50 p-6 max-w-md mx-auto md:max-w-lg cookie-theme-wrapper" style="--theme-color: {{ $themeColor ?? '#3b82f6' }}; --theme-color-20: {{ $themeColor ?? '#3b82f6' }}20;">
    <div class="flex items-start gap-4">
        <div class="flex-shrink-0">
            <div class="w-10 h-10 rounded-full flex items-center justify-center cookie-icon-bg">
                <svg class="w-5 h-5 cookie-icon-color" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">{{ $cookieTitle }}</h3>
            <p class="text-xs text-gray-600 dark:text-gray-300 mb-3 leading-relaxed">{{ $cookieDescription }}</p>
            @if($contactUrl !== '#')
                <a href="{{ $contactUrl }}" class="text-xs font-medium hover:underline cookie-link">{{ $contactDescription }}</a>
            @endif
        </div>
    </div>
    <div class="flex gap-2 mt-4">
        <button onclick="acceptCookies()" class="flex-1 text-white text-xs font-medium py-2.5 px-4 rounded-lg transition-colors duration-200 opacity-100 hover:opacity-90 cookie-btn-primary">
            {{ __('Accept All') }}
        </button>
        <button onclick="showStrictly()" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium py-2.5 px-4 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
            {{ __('Essential Only') }}
        </button>
        <button onclick="rejectCookies()" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-medium py-2.5 px-4 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors duration-200">
            {{ __('Decline') }}
        </button>
    </div>
</div>

<!-- Strictly Necessary Info Modal -->
<div id="strictly-modal" class="fixed inset-0 bg-black bg-opacity-50 z-60 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-3">{{ $strictlyTitle }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">{{ $strictlyDescription }}</p>
            <div class="flex gap-3">
                <button onclick="acceptStrictly()" class="flex-1 text-white text-sm font-medium py-2.5 px-4 rounded-lg cookie-btn-primary">
                    {{ __('Accept Essential') }}
                </button>
                <button onclick="hideStrictly()" class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium py-2.5 px-4 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600">
                    {{ __('Cancel') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function acceptCookies() {
    document.cookie = "cookie_consent=accepted; path=/; max-age=" + (365 * 24 * 60 * 60);
    sessionStorage.setItem('cookie_consent', 'accepted');
    @if($enableLogging)
        console.log('✅ All cookies accepted');
    @endif
    document.getElementById('cookie-banner').style.display = 'none';
}

function showStrictly() {
    document.getElementById('cookie-banner').style.display = 'none';
    document.getElementById('strictly-modal').classList.remove('hidden');
}

function hideStrictly() {
    document.getElementById('strictly-modal').classList.add('hidden');
    document.getElementById('cookie-banner').style.display = 'block';
}

function acceptStrictly() {
    document.cookie = "cookie_consent=essential; path=/; max-age=" + (365 * 24 * 60 * 60);
    sessionStorage.setItem('cookie_consent', 'essential');
    @if($enableLogging)
        console.log('🍪 Essential cookies only');
    @endif
    hideStrictly();
    document.getElementById('cookie-banner').style.display = 'none';
}

function rejectCookies() {
    document.cookie = "cookie_consent=rejected; path=/; max-age=" + (2 * 60 * 60);
    sessionStorage.setItem('cookie_consent', 'rejected');
    @if($enableLogging)
        console.log('❌ Cookies rejected');
    @endif
    document.getElementById('cookie-banner').style.display = 'none';
}

if (sessionStorage.getItem('cookie_consent')) {
    const banner = document.getElementById('cookie-banner');
    if (banner) banner.style.display = 'none';
}
</script>
@endif
