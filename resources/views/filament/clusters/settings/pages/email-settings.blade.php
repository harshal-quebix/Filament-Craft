<x-filament-panels::page>
    <x-settings-layout>
        <form wire:submit="save">
            {{ $this->form }}

            <div class="settings-save-bar">
                <div class="flex items-center gap-3">
                    <x-filament::button type="button" color="info" wire:click="sendTestEmail" size="lg">
                        <x-filament::icon icon="heroicon-o-paper-airplane" class="w-5 h-5 mr-2" />
                        {{ __('Send Test Email') }}
                    </x-filament::button>
                    <x-filament::button type="submit" size="lg">
                        <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 mr-2" />
                        {{ __('Save Email Settings') }}
                    </x-filament::button>
                </div>
            </div>
        </form>
    </x-settings-layout>
</x-filament-panels::page>
