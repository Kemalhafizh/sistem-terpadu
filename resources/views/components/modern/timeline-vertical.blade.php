@props([
    'items' => [],
])

<div class="relative pl-6 border-l-2 border-slate-200 dark:border-slate-700 space-y-6">
    @forelse ($items as $item)
        <div class="relative group">
            <!-- Bullet Icon -->
            <div class="absolute -left-[31px] top-0 flex h-6 w-6 items-center justify-center rounded-full bg-emerald-600 text-white shadow ring-4 ring-white dark:ring-slate-900 text-xs">
                {{ $item['emoji'] ?? '•' }}
            </div>
            <div>
                <span class="text-xs font-medium text-slate-400 dark:text-slate-500">{{ $item['time'] ?? '' }}</span>
                <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $item['title'] ?? '' }}</h4>
                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">{{ $item['description'] ?? '' }}</p>
            </div>
        </div>
    @empty
        {{ $slot }}
    @endforelse
</div>
