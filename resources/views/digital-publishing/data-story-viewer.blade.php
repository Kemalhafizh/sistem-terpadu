<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Pembangun Cerita Data (Data Story & Scrollytelling)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Visualisasi naratif capaian sekolah interaktif untuk laporan tahunan dan akreditasi institusi.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        @forelse($stories as $story)
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 border border-slate-100 dark:border-slate-800 shadow-sm space-y-6">
                <div class="space-y-2 border-b border-slate-100 dark:border-slate-800 pb-4">
                    <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Laporan Naratif Data</span>
                    <h3 class="text-2xl font-black text-slate-900 dark:text-white">{{ $story->title }}</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $story->summary }}</p>
                </div>

                <!-- Scrollytelling Visual Steps Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($story->story_configuration['steps'] ?? [] as $idx => $step)
                        <div class="p-6 rounded-3xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 space-y-3">
                            <span class="w-8 h-8 rounded-full bg-emerald-500 text-white flex items-center justify-center text-xs font-bold shadow-md">
                                {{ $idx + 1 }}
                            </span>
                            <h4 class="text-lg font-bold text-slate-900 dark:text-white">{{ $step['title'] }}</h4>
                            <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400">{{ $step['stat'] }}</div>
                            <p class="text-xs text-slate-500 leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                <div class="w-16 h-16 rounded-3xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                    </svg>
                </div>
                <h3 class="font-bold text-lg text-slate-800 dark:text-white">Belum Ada Cerita Data Interaktif</h3>
                <p class="text-sm text-slate-500 max-w-md mx-auto">Susun infografis dan cerita data berbasis scrollytelling untuk publikasi transparansi sekolah.</p>
            </div>
        @endforelse
    </div>
</x-app-layout>
