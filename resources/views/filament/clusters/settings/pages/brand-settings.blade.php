<x-filament-panels::page>
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <x-settings-layout>
        <div x-data="{
                colorMap: {
                    'slate': '#64748b', 'gray': '#6b7280', 'zinc': '#71717a', 'neutral': '#737373', 'stone': '#78716c',
                    'red': '#ef4444', 'orange': '#f97316', 'amber': '#f59e0b', 'yellow': '#eab308', 'lime': '#84cc16',
                    'green': '#22c55e', 'emerald': '#10b981', 'teal': '#14b8a6', 'cyan': '#06b6d4', 'sky': '#0ea5e9',
                    'blue': '#3b82f6', 'indigo': '#6366f1', 'violet': '#8b5cf6', 'purple': '#a855f7', 'fuchsia': '#d946ef',
                    'pink': '#ec4899', 'rose': '#f43f5e'
                },
                applyTheme(color) {
                    const root = document.documentElement;
                    const baseColor = this.colorMap[color];
                    root.style.setProperty('--primary-500', baseColor);
                    root.style.setProperty('--primary-600', this.darken(baseColor, 10));
                    root.style.setProperty('--primary-700', this.darken(baseColor, 20));
                },
                darken(color, percent) {
                    const num = parseInt(color.replace('#', ''), 16);
                    const amt = Math.round(2.55 * percent);
                    const R = (num >> 16) - amt;
                    const G = (num >> 8 & 0x00FF) - amt;
                    const B = (num & 0x0000FF) - amt;
                    return '#' + (0x1000000 + (R > 255 ? 255 : R < 0 ? 0 : R) * 0x10000 +
                        (G > 255 ? 255 : G < 0 ? 0 : G) * 0x100 +
                        (B > 255 ? 255 : B < 0 ? 0 : B)).toString(16).slice(1);
                }
            }" @theme-color-changed.window="applyTheme($event.detail.color)">
                <form wire:submit="save">
                    {{ $this->form }}
                </form>
            </div>
    </x-settings-layout>

    <div class="settings-save-bar">
        <x-filament::button type="button" wire:click="save" size="lg">
            <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 mr-2" />
            {{ __('Save Brand Settings') }}
        </x-filament::button>
    </div>
</x-filament-panels::page>
