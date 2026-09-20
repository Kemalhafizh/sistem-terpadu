@props([
    'title' => '',
    'value' => 0,
    'icon' => null,
    'trend' => null,
    'trendUp' => true,
    'badgeColor' => 'emerald',
])

<div class="relative overflow-hidden rounded-2xl bg-white p-5 shadow-sm border border-slate-100 dark:bg-slate-800 dark:border-slate-700 transition hover:shadow-md">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $title }}</p>
            <h3 class="mt-1 text-2xl font-bold text-slate-800 dark:text-white">{{ $value }}</h3>
            @if ($trend)
                <p class="mt-1 flex items-center text-xs font-semibold {{ $trendUp ? 'text-emerald-600' : 'text-rose-600' }}">
                    <span>{{ $trendUp ? '↑' : '↓' }} {{ $trend }}</span>
                    <span class="ml-1 font-normal text-slate-400">vs bulan lalu</span>
                </p>
            @endif
        </div>
        @if ($icon)
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-{{ $badgeColor }}-50 text-{{ $badgeColor }}-600 dark:bg-{{ $badgeColor }}-900/30 dark:text-{{ $badgeColor }}-400 text-xl">
                {{ $icon }}
            </div>
        @endif
    </div>
</div>
