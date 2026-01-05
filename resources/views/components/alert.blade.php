@props(['type' => 'success', 'dismissible' => true])

@php
    $classes = [
        'success' => 'bg-green-100 border-green-400 text-green-700',
        'error' => 'bg-red-100 border-red-400 text-red-700',
        'warning' => 'bg-yellow-100 border-yellow-400 text-yellow-700',
        'info' => 'bg-blue-100 border-blue-400 text-blue-700',
    ];
    
    $iconColors = [
        'success' => 'text-green-600',
        'error' => 'text-red-600',
        'warning' => 'text-yellow-600',
        'info' => 'text-blue-600',
    ];
    
    $closeButtonColors = [
        'success' => 'text-green-500 hover:text-green-700',
        'error' => 'text-red-500 hover:text-red-700',
        'warning' => 'text-yellow-500 hover:text-yellow-700',
        'info' => 'text-blue-500 hover:text-blue-700',
    ];
    
    $alertClass = $classes[$type] ?? $classes['success'];
    $iconColor = $iconColors[$type] ?? $iconColors['success'];
    $closeColor = $closeButtonColors[$type] ?? $closeButtonColors['success'];
    
    // Get custom classes from attributes, default to mb-4 if not provided
    $customClass = $attributes->get('class', 'mb-4');
@endphp

<div 
    x-data="{ show: true }"
    x-show="show"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="{{ $alertClass }} border px-4 py-3 rounded relative {{ $customClass }}"
    role="alert"
    x-init="if (typeof lucide !== 'undefined') { lucide.createIcons(); }">
    <div class="flex items-start justify-between">
        <div class="flex items-start flex-1">
            @if($type === 'success')
                <i data-lucide="check-circle" class="w-5 h-5 mr-2 mt-0.5 {{ $iconColor }}"></i>
            @elseif($type === 'error')
                <i data-lucide="alert-circle" class="w-5 h-5 mr-2 mt-0.5 {{ $iconColor }}"></i>
            @elseif($type === 'warning')
                <i data-lucide="alert-triangle" class="w-5 h-5 mr-2 mt-0.5 {{ $iconColor }}"></i>
            @else
                <i data-lucide="info" class="w-5 h-5 mr-2 mt-0.5 {{ $iconColor }}"></i>
            @endif
            <span class="block sm:inline flex-1">{{ $slot }}</span>
        </div>
        @if($dismissible)
            <button 
                type="button"
                @click="show = false"
                class="ml-4 {{ $closeColor }} focus:outline-none focus:ring-2 focus:ring-offset-2 rounded-md p-1 transition-colors"
                aria-label="Close">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        @endif
    </div>
</div>

