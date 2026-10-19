@props([
    'variant' => 'primary', // primary, secondary, outline, danger
    'type' => 'button',
    'id' => null,
    'class' => '',
])

@php
    $variantClasses = match($variant) {
        'primary' => 'bg-blue-600 hover:bg-blue-700 text-white shadow-xs focus:ring-blue-500',
        'secondary' => 'bg-slate-100 hover:bg-slate-200 text-slate-700 focus:ring-slate-400',
        'outline' => 'bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-xs focus:ring-blue-500',
        'danger' => 'bg-rose-600 hover:bg-rose-700 text-white shadow-xs focus:ring-rose-500',
        default => 'bg-blue-600 hover:bg-blue-700 text-white focus:ring-blue-500',
    };
@endphp

<button
    type="{{ $type }}"
    @if($id) id="{{ $id }}" @endif
    {{ $attributes->merge(['class' => 'inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-150 focus:outline-hidden focus:ring-2 focus:ring-offset-1 disabled:opacity-50 disabled:cursor-not-allowed ' . $variantClasses . ' ' . $class]) }}
>
    {{ $slot }}
</button>
