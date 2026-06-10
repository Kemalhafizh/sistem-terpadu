{{-- Premium Empty State Component --}}
@props([
    'icon' => 'box', // box, search, task, document, calendar, lock
    'message' => 'Belum ada data',
    'submessage' => 'Coba sesuaikan filter Anda atau tambahkan item baru.',
    'actionUrl' => null,
    'actionText' => null,
    'actionType' => 'primary', // primary, secondary, outline
])

<div class="flex flex-col items-center justify-center text-center p-8 md:p-12 animate-fade-in">
    {{-- Icon Wrapper --}}
    <div class="w-16 h-16 bg-gray-100 dark:bg-slate-750 text-gray-400 dark:text-slate-400 rounded-2xl flex items-center justify-center mb-5 border border-gray-200/50 dark:border-slate-700/50 shadow-xs relative group">
        <div class="absolute inset-0 bg-primary-500/5 dark:bg-primary-500/10 rounded-2xl scale-0 group-hover:scale-110 transition-transform duration-300"></div>
        
        @if($icon === 'search')
            <svg class="w-8 h-8 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        @elseif($icon === 'task')
            <svg class="w-8 h-8 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
            </svg>
        @elseif($icon === 'document')
            <svg class="w-8 h-8 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
        @elseif($icon === 'calendar')
            <svg class="w-8 h-8 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
        @elseif($icon === 'lock')
            <svg class="w-8 h-8 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
            </svg>
        @else {{-- box --}}
            <svg class="w-8 h-8 group-hover:text-primary-500 dark:group-hover:text-primary-400 transition-colors animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
            </svg>
        @endif
    </div>

    {{-- Message & Submessage --}}
    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">
        {{ $message }}
    </h3>
    
    @if($submessage)
        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mb-6 leading-relaxed">
            {{ $submessage }}
        </p>
    @endif

    {{-- CTA Button --}}
    @if($actionUrl && $actionText)
        @if($actionType === 'primary')
            <a href="{{ $actionUrl }}" 
               class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-wider hover:bg-primary-700 active:bg-primary-900 focus:outline-none focus:border-primary-900 focus:ring ring-primary-300 transition ease-in-out duration-150 shadow-sm">
                {{ $actionText }}
            </a>
        @elseif($actionType === 'secondary')
            <a href="{{ $actionUrl }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-slate-700 border border-transparent rounded-lg font-semibold text-xs text-white dark:text-gray-100 uppercase tracking-wider hover:bg-gray-700 dark:hover:bg-slate-600 active:bg-gray-900 focus:outline-none transition ease-in-out duration-150 shadow-sm">
                {{ $actionText }}
            </a>
        @else {{-- outline --}}
            <a href="{{ $actionUrl }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800 hover:bg-gray-50 dark:hover:bg-slate-700 focus:outline-none focus:ring ring-primary-300 transition ease-in-out duration-150">
                {{ $actionText }}
            </a>
        @endif
    @endif
</div>
