@props([
    'label' => '',
    'color' => 'emerald',
    'icon' => null,
])

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-{$color}-100 text-{$color}-800 dark:bg-{$color}-900/40 dark:text-{$color}-300 border border-{$color}-200 dark:border-{$color}-800"]) }}>
    @if ($icon)
        <span>{{ $icon }}</span>
    @endif
    <span>{{ $label ?: $slot }}</span>
</span>
