@php
    $permissions = $getRecord()->permissions->pluck('name')->toArray();
    $limit = 5;
    $total = count($permissions);
    $visible = array_slice($permissions, 0, $limit);
    $hidden = array_slice($permissions, $limit);
@endphp

<div x-data="{ expanded: false }" class="flex flex-wrap gap-1">
    @foreach($visible as $permission)
        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
            {{ $permission }}
        </span>
    @endforeach

    @foreach($hidden as $permission)
        <span x-show="expanded" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
            {{ $permission }}
        </span>
    @endforeach

    @if(count($hidden) > 0)
        <button
            x-on:click="expanded = !expanded"
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 cursor-pointer hover:bg-gray-200"
        >
            <span x-show="!expanded">+{{ count($hidden) }} {{ __('more') }}</span>
            <span x-show="expanded">{{ __('show less') }}</span>
        </button>
    @endif
</div>
