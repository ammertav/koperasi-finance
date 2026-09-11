@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, success, danger, warning, rose, purple
    'size' => 'lg', // sm, md, lg
    'disabled' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'href' => null,
    'class' => '',
])

@php
    // Base styles
    $baseClasses = 'font-bold rounded-lg transition duration-200 flex items-center gap-2 whitespace-nowrap';

    // Size classes
    $sizeClasses = match($size) {
        'sm' => 'px-3 py-1.5 text-xs',
        'lg' => 'px-6 py-3 text-base',
        default => 'px-4 py-2.5 text-sm', // md
    };

    // Variant classes
    $variantClasses = match($variant) {
        'secondary' => 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 disabled:bg-gray-100 disabled:text-gray-400 disabled:border-gray-200 disabled:cursor-not-allowed',
        'success' => 'bg-green-600 text-white hover:bg-green-700 disabled:bg-green-400 disabled:cursor-not-allowed shadow-md',
        'danger' => 'bg-red-600 text-white hover:bg-red-700 disabled:bg-red-400 disabled:cursor-not-allowed shadow-md',
        'warning' => 'bg-amber-600 text-white hover:bg-amber-700 disabled:bg-amber-400 disabled:cursor-not-allowed shadow-md',
        'rose' => 'bg-rose-500 text-white hover:bg-rose-600 disabled:bg-rose-400 disabled:cursor-not-allowed shadow-md',
        'purple' => 'bg-purple-500 text-white hover:bg-purple-600 disabled:bg-purple-400 disabled:cursor-not-allowed shadow-md',
        'yellow' => 'bg-yellow-500 text-white hover:bg-yellow-600 disabled:bg-yellow-400 disabled:cursor-not-allowed shadow-md',
        default => 'bg-indigo-600 text-white hover:bg-indigo-700 disabled:bg-indigo-400 disabled:cursor-not-allowed shadow-md', // primary
    };

    // Combined classes
    $buttonClasses = "{$baseClasses} {$sizeClasses} {$variantClasses}";

    if ($disabled) {
        $buttonClasses .= ' disabled:opacity-60';
    }

    if ($class) {
        $buttonClasses .= " {$class}";
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $buttonClasses]) }}>
        @if ($icon && $iconPosition === 'left')
            <i class="fas fa-{{ $icon }}"></i>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right')
            <i class="fas fa-{{ $icon }}"></i>
        @endif
    </a>
@else
    <button
        type="{{ $type }}"
        @disabled($disabled)
        {{ $attributes->merge(['class' => $buttonClasses]) }}>
        @if ($icon && $iconPosition === 'left')
            <i class="fas fa-{{ $icon }}"></i>
        @endif
        {{ $slot }}
        @if ($icon && $iconPosition === 'right')
            <i class="fas fa-{{ $icon }}"></i>
        @endif
    </button>
@endif
