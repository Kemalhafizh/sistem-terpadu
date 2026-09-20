@props([
    'percentage' => 75,
    'size' => 64,
    'strokeWidth' => 6,
    'strokeColor' => '#10B981',
    'trackColor' => '#E2E8F0',
    'class' => '',
])

@php
    $radius = ($size - $strokeWidth) / 2;
    $circumference = 2 * pi() * $radius;
    $offset = $circumference - ($percentage / 100) * $circumference;
@endphp

<div {{ $attributes->merge(['class' => 'relative inline-flex items-center justify-center ' . $class]) }}
     style="width: {{ $size }}px; height: {{ $size }}px;">
    <svg width="{{ $size }}" height="{{ $size }}" class="transform -rotate-90">
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}"
                stroke="{{ $trackColor }}" stroke-width="{{ $strokeWidth }}" fill="transparent" />
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $radius }}"
                stroke="{{ $strokeColor }}" stroke-width="{{ $strokeWidth }}" fill="transparent"
                stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $offset }}"
                stroke-linecap="round" class="transition-all duration-1000 ease-out" />
    </svg>
    <div class="absolute inset-0 flex items-center justify-center text-xs font-bold">
        {{ $slot->isEmpty() ? $percentage . '%' : $slot }}
    </div>
</div>
