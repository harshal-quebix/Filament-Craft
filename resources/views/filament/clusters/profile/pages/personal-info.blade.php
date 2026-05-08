<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-8 pt-2">
            <x-filament::button type="submit">
                {{ __('Save Changes') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
