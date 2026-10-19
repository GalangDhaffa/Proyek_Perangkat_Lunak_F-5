@props([
    'title' => null,
    'badge' => null,
    'badgeType' => 'default',
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/90 shadow-xs p-6 ' . $class]) }}>
    @if ($title || $badge || isset($headerRight))
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
            <div class="flex items-center gap-3">
                @if ($title)
                    <h3 class="text-base font-semibold text-slate-800 tracking-tight">{{ $title }}</h3>
                @endif
                @if ($badge)
                    <x-ui.badge :type="$badgeType">{{ $badge }}</x-ui.badge>
                @endif
            </div>
            @if (isset($headerRight))
                <div>{{ $headerRight }}</div>
            @endif
        </div>
    @endif
    {{ $slot }}
</div>
