<x-filament-panels::page>
    <x-profile-layout>
        <form wire:submit="save">
            {{ $this->form }}

            <div class="settings-save-bar">
                <x-filament::button type="submit" size="lg">
                    <x-filament::icon icon="heroicon-o-check-circle" class="w-5 h-5 mr-2" />
                    {{ __('Save Changes') }}
                </x-filament::button>
            </div>
        </form>
    </x-profile-layout>
</x-filament-panels::page>
