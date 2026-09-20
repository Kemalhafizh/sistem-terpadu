@props([
    'blur' => '16px',
    'bgOpacity' => '0.08',
    'borderColor' => 'rgba(255, 255, 255, 0.15)',
    'class' => '',
])

<div {{ $attributes->merge(['class' => 'backdrop-blur-md rounded-2xl border shadow-lg transition-all duration-300 hover:shadow-xl ' . $class]) }}
     style="background-color: rgba(255, 255, 255, {{ $bgOpacity }}); border-color: {{ $borderColor }};">
    {{ $slot }}
</div>
