@props([
    'type' => 'default', // success, warning, danger, info, neutral, default
])

@php
    $classes = match($type) {
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200/70',
        'danger' => 'bg-rose-50 text-rose-700 border-rose-200/70',
        'info' => 'bg-blue-50 text-blue-700 border-blue-200/70',
        'neutral' => 'bg-slate-100 text-slate-700 border-slate-200',
        default => 'bg-slate-100 text-slate-600 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . $classes]) }}>
    {{ $slot }}
</span>
