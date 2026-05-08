<div class="fi-dropdown fi-user-menu mr-4" x-data="{ isOpen: false }">
    <button type="button"
            class="fi-dropdown-trigger flex items-center gap-3 rounded-lg p-2 text-sm font-medium text-gray-700 outline-none transition duration-75 hover:bg-gray-50 focus:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5 dark:focus:bg-white/5"
            @click="isOpen = ! isOpen">
        <span class="text-base">&nbsp;&nbsp;{{ $currentLang['flag'] }}</span>
        <span class="hidden sm:block ml-2">&nbsp;{{ $currentLang['name'] }}&nbsp;&nbsp;</span>
        <svg class="fi-dropdown-trigger-icon h-5 w-5 text-gray-400 dark:text-gray-500 ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
        </svg>
    </button>
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.away="isOpen = false"
         class="fi-dropdown-panel absolute right-0 top-full z-10 mt-2 w-48 rounded-lg bg-white shadow-lg ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="fi-dropdown-list p-1">
            @foreach($languages as $locale => $lang)
                <a href="{{ request()->fullUrlWithQuery(['locale' => $locale]) }}"
                   class="fi-dropdown-list-item flex items-center gap-3 rounded-md px-3 py-2 text-sm font-medium text-gray-700 outline-none transition duration-75 hover:bg-gray-50 focus:bg-gray-50 dark:text-gray-200 dark:hover:bg-white/5 dark:focus:bg-white/5 {{ $currentLocale === $locale ? 'bg-gray-50 dark:bg-white/5' : '' }}">
                    <span class="text-base">{{ $lang['flag'] }}</span>
                    <span>{{ $lang['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
</div>
