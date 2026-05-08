<x-filament-panels::page>
    <x-settings-layout>
        <div x-data="fontHandler()" @font-family-changed.window="applyFont($event.detail.font)">
            <form wire:submit="save">
                {{ $this->form }}
            </form>
        </div>
    </x-settings-layout>

    <div class="settings-save-bar">
        <x-filament::button type="button" wire:click="save" size="lg">
            <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 mr-2" />
            {{ __('Save Settings') }}
        </x-filament::button>
    </div>

    <script>
        function fontHandler() {
            return {
                applyFont(font) {
                    let style = document.getElementById('dynamic-font-style');
                    if (!style) {
                        style = document.createElement('style');
                        style.id = 'dynamic-font-style';
                        document.head.appendChild(style);
                    }
                    style.innerHTML = `
                        html, body, *, .fi-body, .fi-main, .fi-sidebar, .fi-header,
                        .fi-navigation, .fi-section, .fi-form, .fi-table, .fi-btn,
                        .fi-input, .fi-select, .fi-textarea,
                        input, textarea, select, button, label, span, div, p,
                        h1, h2, h3, h4, h5, h6, a, li, td, th {
                            font-family: "${font}", system-ui, sans-serif !important;
                        }
                    `;
                }
            }
        }
    </script>
</x-filament-panels::page>
