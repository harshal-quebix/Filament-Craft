<x-filament-panels::page>
    <x-settings-layout>
        <form wire:submit="save">
            {{ $this->form }}
        </form>
    </x-settings-layout>

    <div class="settings-save-bar">
        <x-filament::button type="button" wire:click="save" size="lg">
            <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 mr-2" />
            {{ __('Save Cookie Settings') }}
        </x-filament::button>
    </div>
</x-filament-panels::page>
