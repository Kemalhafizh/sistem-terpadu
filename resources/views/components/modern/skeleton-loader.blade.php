@props([
    'width' => '100%',
    'height' => '20px',
    'rounded' => 'rounded-md',
    'class' => '',
])

<div {{ $attributes->merge(['class' => "animate-pulse bg-slate-200 dark:bg-slate-700/60 {$rounded} {$class}"]) }}
     style="width: {{ $width }}; height: {{ $height }};">
</div>
