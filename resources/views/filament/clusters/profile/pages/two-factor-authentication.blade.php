<x-filament-panels::page>
    {{ $this->form }}

    <x-filament-actions::modals />

    <div class="fi-ac-header-actions">
        @foreach($this->getActions() as $action)
            {{ $action }}
        @endforeach
    </div>

    <script>
        function copyKey(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Create and show toast notification
                const toast = document.createElement('div');
                toast.style.cssText = 'position: fixed; top: 20px; right: 20px; background-color: #10b981; color: white; padding: 12px 16px; border-radius: 8px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); z-index: 9999; font-size: 14px;';
                toast.textContent = '{{ __("Key copied to clipboard") }}';
                document.body.appendChild(toast);

                // Remove toast after 3 seconds
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 3000);
            }).catch(function(err) {
                console.error('Failed to copy: ', err);
            });
        }
    </script>
</x-filament-panels::page>
