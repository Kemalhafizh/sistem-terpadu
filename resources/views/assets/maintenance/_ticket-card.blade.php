<div class="bg-white dark:bg-gray-800 p-4 rounded-3xl shadow-sm border border-gray-200 dark:border-gray-700/80 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between space-y-4">
    <div class="flex items-center justify-between">
        <!-- Priority Badge -->
        <span class="inline-flex px-2.5 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider {{ $ticket->priority_color }}">
            {{ $ticket->priority }}
        </span>
        <!-- Asset Code -->
        <span class="text-[9px] font-bold text-gray-400 dark:text-gray-500 font-mono tracking-tight">{{ $ticket->asset->asset_code }}</span>
    </div>

    <!-- Asset Name & Location -->
    <div>
        <h4 class="font-black text-sm text-gray-900 dark:text-white leading-snug line-clamp-2">{{ $ticket->asset->name }}</h4>
        <div class="flex items-center text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 mb-2">
            <svg class="w-3.5 h-3.5 mr-1 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="truncate">{{ $ticket->asset->room->name ?? 'N/A' }}</span>
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1 italic font-medium">
            "{{ $ticket->issue_description }}"
        </p>
    </div>

    <!-- Divider -->
    <div class="border-t border-gray-100 dark:border-gray-700"></div>

    <!-- Reporter & Timestamp info -->
    <div class="flex items-center justify-between text-[10px] text-gray-400 dark:text-gray-500 font-medium">
        <div class="flex items-center space-x-1.5 truncate max-w-[65%]">
            <div class="w-5 h-5 rounded-full bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 font-black flex items-center justify-center text-[9px] uppercase border border-teal-100 dark:border-teal-900/50 shrink-0">
                {{ substr($ticket->reporter->name ?? 'A', 0, 1) }}
            </div>
            <span class="font-bold text-gray-600 dark:text-gray-400 truncate">{{ $ticket->reporter->name ?? 'Anonim' }}</span>
        </div>
        <span class="shrink-0 text-gray-400 dark:text-gray-600 font-semibold">{{ $ticket->created_at->diffForHumans() }}</span>
    </div>

    <!-- Action Link -->
    <div>
        <a href="{{ route('admin.assets.maintenance.show', $ticket->id) }}" class="w-full text-center px-4 py-2 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 hover:bg-teal-600 hover:text-white dark:hover:bg-teal-500 dark:hover:text-white rounded-2xl font-black text-[10px] uppercase tracking-wider transition-all duration-200 block border border-teal-100/30 dark:border-teal-900/10">
            Tinjau Tiket
        </a>
    </div>
</div>
