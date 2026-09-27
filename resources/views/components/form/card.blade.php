@props(['title' => null])
<div {{ $attributes->class(['bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm p-6']) }}>
    @if ($title)
        <h2 class="text-sm font-black uppercase tracking-wider text-gray-900 dark:text-white mb-4">{{ $title }}</h2>
    @endif
    {{ $slot }}
</div>
