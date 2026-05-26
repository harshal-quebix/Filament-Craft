<div class="space-y-6 p-2">
    {{-- Role Name --}}
    <div class="bg-gradient-to-r from-primary-50 to-white rounded-xl p-4 border border-primary-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-primary-100 flex items-center justify-center">
                <x-heroicon-o-identification class="w-5 h-5 text-primary-600" />
            </div>
            <div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wider">{{ __('Role Name') }}</div>
                <div class="text-lg font-bold text-gray-900">{{ $record->name }}</div>
            </div>
        </div>
    </div>

    {{-- Permissions --}}
    <div>
        <div class="flex items-center gap-2 mb-3">
            <x-heroicon-o-shield-check class="w-5 h-5 text-primary-600" />
            <h3 class="text-base font-semibold text-gray-900">{{ __('Permissions') }}</h3>
            <span class="ml-auto text-xs bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full font-medium">
                {{ $record->permissions->count() }} {{ __('total') }}
            </span>
        </div>

        @php
            $permissions = $record->permissions->pluck('name')->toArray();
            $grouped = [];
            foreach ($permissions as $permission) {
                $parts = explode(' ', $permission);
                $action = $parts[0] ?? '';
                $module = implode(' ', array_slice($parts, 1));
                if (!isset($grouped[$module])) {
                    $grouped[$module] = [];
                }
                $grouped[$module][] = ucfirst($action);
            }
        @endphp

        @if(empty($permissions))
            <div class="text-center py-8 text-gray-400 bg-gray-50 rounded-xl border border-gray-100">
                <x-heroicon-o-shield-exclamation class="w-10 h-10 mx-auto mb-2 text-gray-300" />
                <p>{{ __('No permissions assigned') }}</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                @foreach($grouped as $module => $actions)
                    <div class="bg-white rounded-xl p-4 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-3 pb-2 border-b border-gray-100">
                            <div class="w-2 h-2 rounded-full bg-primary-500"></div>
                            <div class="font-semibold text-sm text-gray-800">
                                {{ ucwords(str_replace('_', ' ', $module)) }}
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($actions as $action)
                                @php
                                    $colorClass = match($action) {
                                        'Create' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'Edit' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'Delete' => 'bg-red-50 text-red-700 border-red-200',
                                        'View' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                                    };
                                    $iconClass = match($action) {
                                        'Create' => 'heroicon-m-plus-circle',
                                        'Edit' => 'heroicon-m-pencil-square',
                                        'Delete' => 'heroicon-m-trash',
                                        'View' => 'heroicon-m-eye',
                                        default => 'heroicon-m-check-circle',
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $colorClass }}">
                                    <x-dynamic-component :component="$iconClass" class="w-3.5 h-3.5" />
                                    {{ $action }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Footer info --}}
    <div class="flex items-center justify-between text-xs text-gray-400 pt-2 border-t border-gray-100">
        <div class="flex items-center gap-1">
            <x-heroicon-m-calendar class="w-3.5 h-3.5" />
            {{ __('Created') }}: {{ $record->created_at?->format('M d, Y H:i') ?? '-' }}
        </div>
        <div class="flex items-center gap-1">
            <x-heroicon-m-clock class="w-3.5 h-3.5" />
            {{ __('Updated') }}: {{ $record->updated_at?->format('M d, Y H:i') ?? '-' }}
        </div>
    </div>
</div>
