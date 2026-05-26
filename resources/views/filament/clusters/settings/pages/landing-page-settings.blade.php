<x-filament-panels::page>
    <div class="settings-simple-layout landing-page-layout" x-data="landingTabs()" x-init="initTabSync()">
        {{-- Sidebar with section navigation --}}
        <div class="settings-sidebar-card">
            <h3 class="settings-sidebar-title">{{ __('Sections') }}</h3>
            <nav class="settings-sidebar-nav landing-sidebar-nav">
                <a href="#" @click.prevent="setTab(1)" :class="{ 'active': activeTab === 1 }">
                    <x-filament::icon icon="heroicon-o-paint-brush" class="w-5 h-5" />
                    <span>{{ __('Global Settings') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(2)" :class="{ 'active': activeTab === 2 }">
                    <x-filament::icon icon="heroicon-o-home" class="w-5 h-5" />
                    <span>{{ __('Hero Section') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(3)" :class="{ 'active': activeTab === 3 }">
                    <x-filament::icon icon="heroicon-o-squares-2x2" class="w-5 h-5" />
                    <span>{{ __('Features') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(4)" :class="{ 'active': activeTab === 4 }">
                    <x-filament::icon icon="heroicon-o-list-bullet" class="w-5 h-5" />
                    <span>{{ __('How It Works') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(5)" :class="{ 'active': activeTab === 5 }">
                    <x-filament::icon icon="heroicon-o-megaphone" class="w-5 h-5" />
                    <span>{{ __('CTA Section') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(6)" :class="{ 'active': activeTab === 6 }">
                    <x-filament::icon icon="heroicon-o-information-circle" class="w-5 h-5" />
                    <span>{{ __('About Page') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(7)" :class="{ 'active': activeTab === 7 }">
                    <x-filament::icon icon="heroicon-o-book-open" class="w-5 h-5" />
                    <span>{{ __('Guide Page') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(8)" :class="{ 'active': activeTab === 8 }">
                    <x-filament::icon icon="heroicon-o-envelope" class="w-5 h-5" />
                    <span>{{ __('Contact Page') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(9)" :class="{ 'active': activeTab === 9 }">
                    <x-filament::icon icon="heroicon-o-shield-check" class="w-5 h-5" />
                    <span>{{ __('Legal Pages') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(10)" :class="{ 'active': activeTab === 10 }">
                    <x-filament::icon icon="heroicon-o-lock-closed" class="w-5 h-5" />
                    <span>{{ __('Auth Section') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(11)" :class="{ 'active': activeTab === 11 }">
                    <x-filament::icon icon="heroicon-o-squares-plus" class="w-5 h-5" />
                    <span>{{ __('Footer Settings') }}</span>
                </a>
                <a href="#" @click.prevent="setTab(12)" :class="{ 'active': activeTab === 12 }">
                    <x-filament::icon icon="heroicon-o-bars-3" class="w-5 h-5" />
                    <span>{{ __('Menu Management') }}</span>
                </a>
            </nav>
        </div>

        {{-- Main form card --}}
        <div class="settings-main-card landing-page-form">
            <form wire:submit="save">
                {{ $this->form }}

                <div class="settings-save-bar">
                    <x-filament::button type="submit" size="lg">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 mr-2" />
                        {{ __('Save Settings') }}
                    </x-filament::button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function landingTabs() {
            return {
                activeTab: 1,
                setTab(index) {
                    this.activeTab = index;
                    this.$nextTick(() => {
                        const tabs = document.querySelectorAll('.landing-page-form .fi-tabs-item');
                        if (tabs[index - 1]) {
                            tabs[index - 1].click();
                        }
                    });
                },
                initTabSync() {
                    this.$nextTick(() => {
                        const tabs = document.querySelectorAll('.landing-page-form .fi-tabs-item');
                        tabs.forEach((tab, i) => {
                            tab.addEventListener('click', () => {
                                this.activeTab = i + 1;
                            });
                        });
                    });
                }
            }
        }
    </script>
</x-filament-panels::page>
