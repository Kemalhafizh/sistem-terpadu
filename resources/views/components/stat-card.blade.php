{{-- Dashboard Stat Card Component --}}
@props([
    'title',
    'value',
    'icon' => null,
    'color' => 'primary', // primary, success, danger, warning, info, secondary
    'trendValue' => null,
    'trendDirection' => 'up', // up, down
    'trendText' => null,
])

@php
    $colorMap = [
        'primary' => [
            'bg' => 'bg-primary-50 dark:bg-primary-950/30',
            'border' => 'border-primary-100 dark:border-primary-900/30',
            'text' => 'text-primary-600 dark:text-primary-400',
            'iconBg' => 'bg-primary-100 dark:bg-primary-900/50',
            'iconText' => 'text-primary-700 dark:text-primary-300'
        ],
        'success' => [
            'bg' => 'bg-emerald-50 dark:bg-emerald-950/30',
            'border' => 'border-emerald-100 dark:border-emerald-900/30',
            'text' => 'text-emerald-600 dark:text-emerald-400',
            'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/50',
            'iconText' => 'text-emerald-700 dark:text-emerald-300'
        ],
        'danger' => [
            'bg' => 'bg-red-50 dark:bg-red-950/30',
            'border' => 'border-red-100 dark:border-red-900/30',
            'text' => 'text-red-600 dark:text-red-400',
            'iconBg' => 'bg-red-100 dark:bg-red-900/50',
            'iconText' => 'text-red-700 dark:text-red-300'
        ],
        'warning' => [
            'bg' => 'bg-amber-50 dark:bg-amber-950/30',
            'border' => 'border-amber-100 dark:border-amber-900/30',
            'text' => 'text-amber-600 dark:text-amber-400',
            'iconBg' => 'bg-amber-100 dark:bg-amber-900/50',
            'iconText' => 'text-amber-700 dark:text-amber-300'
        ],
        'info' => [
            'bg' => 'bg-blue-50 dark:bg-blue-950/30',
            'border' => 'border-blue-100 dark:border-blue-900/30',
            'text' => 'text-blue-600 dark:text-blue-400',
            'iconBg' => 'bg-blue-100 dark:bg-blue-900/50',
            'iconText' => 'text-blue-700 dark:text-blue-300'
        ],
        'secondary' => [
            'bg' => 'bg-slate-50 dark:bg-slate-800/30',
            'border' => 'border-slate-100 dark:border-slate-700/30',
            'text' => 'text-slate-600 dark:text-slate-400',
            'iconBg' => 'bg-slate-100 dark:bg-slate-800/80',
            'iconText' => 'text-slate-700 dark:text-slate-300'
        ]
    ];

    $style = $colorMap[$color] ?? $colorMap['primary'];

    // Clean and parse strictly numeric/currency/percent values for Alpine.js count-up animation
    $numericValue = 0;
    $isNumericOnly = false;
    $rawValue = trim((string) $value);
    $hasPrefix = str_starts_with($rawValue, 'Rp');
    $hasPercent = str_ends_with($rawValue, '%');

    if (preg_match('/^(?:Rp\s*)?([0-9]+(?:[.,][0-9]+)*)(?:\s*%)?$/u', $rawValue, $matches)) {
        $clean = $matches[1];
        if (substr_count($clean, '.') > 1 || (substr_count($clean, '.') === 1 && strlen(substr($clean, strpos($clean, '.') + 1)) > 2)) {
            $clean = str_replace('.', '', $clean);
        }
        if (substr_count($clean, ',') > 1) {
            $clean = str_replace(',', '', $clean);
        }
        $clean = str_replace(',', '.', $clean);
        if (is_numeric($clean)) {
            $numericValue = (float) $clean;
            $isNumericOnly = true;
        }
    }
@endphp

<div class="bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700/80 rounded-2xl p-6 card-hover group">
    <div class="flex items-center justify-between">
        {{-- Icon (Left) --}}
        @if(isset($icon) || $slot->isNotEmpty())
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0 transition-transform duration-300 group-hover:scale-110 {{ $style['iconBg'] }} {{ $style['iconText'] }}">
                @if(isset($icon))
                    {!! $icon !!}
                @else
                    {{ $slot }}
                @endif
            </div>
        @endif
        
        {{-- Trend Indicator (Right) --}}
        @if($trendValue)
            <div class="flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold {{ $trendDirection === 'up' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400' : 'bg-red-50 dark:bg-red-950/50 text-red-600 dark:text-red-400' }}">
                @if($trendDirection === 'up')
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                @else
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                    </svg>
                @endif
                <span>{{ $trendValue }}</span>
            </div>
        @endif
    </div>

    {{-- Value and Label --}}
    <div class="mt-4">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
            {{ $title }}
        </h3>
        <div class="flex items-baseline mt-1 gap-2">
            <span class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white tracking-tight"
                  @if($isNumericOnly)
                  x-data="{ count: 0, target: {{ $numericValue }} }"
                  x-init="
                    let duration = 800;
                    let startTimestamp = null;
                    const step = (timestamp) => {
                        if (!startTimestamp) startTimestamp = timestamp;
                        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                        count = progress * target;
                        if (progress < 1) {
                            window.requestAnimationFrame(step);
                        } else {
                            count = target;
                        }
                    };
                    window.requestAnimationFrame(step);
                  }"
                  x-text="
                    let display = count;
                    @if($hasPrefix)
                        display = 'Rp ' + Math.floor(display).toLocaleString('id-ID');
                    @elseif($hasPercent)
                        display = (Number.isInteger(display) ? display : display.toFixed(1)) + '%';
                    @else
                        display = Math.floor(display).toLocaleString('id-ID');
                    @endif
                    return display;
                  "
                  @endif
            >
                {{ $value }}
            </span>
            @if($trendText)
                <span class="text-xs text-gray-400 dark:text-gray-500 truncate font-medium">
                    {{ $trendText }}
                </span>
            @endif
        </div>
    </div>
</div>
