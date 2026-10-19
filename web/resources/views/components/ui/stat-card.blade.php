@props([
    'label' => '',
    'value' => 'Rp0',
    'subtext' => null,
    'id' => null,
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/90 p-5 shadow-xs flex flex-col justify-between ' . $class]) }}>
    <span class="text-xs font-medium text-slate-500 uppercase tracking-wider mb-2">{{ $label }}</span>
    <div @if($id) id="{{ $id }}" @endif class="text-2xl font-bold text-slate-900 tracking-tight">
        {{ $value }}
    </div>
    @if ($subtext)
        <span @if($id) id="{{ $id }}-subtext" @endif class="text-xs text-slate-400 mt-2 font-normal">{{ $subtext }}</span>
    @endif
</div>
