<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Direktori Podcast & Audio Edukasi Islam') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Kajian agama Islam, motivasi belajar, dan materi audio interaktif {{ school_name() }}.
                </p>
            </div>
            <button onclick="document.getElementById('newPodcastModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                </svg>
                <span>Buat Kanal Podcast</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Podcast Channels Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($podcasts as $pod)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4 hover:border-emerald-500 transition-all">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 flex items-center justify-center text-white font-bold text-xl shadow-md">
                                <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">{{ $pod->category }}</span>
                                <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $pod->title }}</h3>
                                <p class="text-xs text-slate-500">Host: {{ $pod->host->name ?? 'Ustadz / Guru' }}</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-xs font-bold text-slate-600 dark:text-slate-300">
                            {{ $pod->episodes->count() }} Episode
                        </span>
                    </div>

                    <p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">{{ $pod->description }}</p>

                    <!-- Latest Episode Player Bar -->
                    @if($pod->episodes->isNotEmpty())
                        @php $latestEp = $pod->episodes->first(); @endphp
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                            <div class="truncate pr-2">
                                <span class="text-[10px] font-bold text-slate-400 block">Episode Terbaru:</span>
                                <h5 class="text-xs font-bold text-slate-800 dark:text-white truncate">{{ $latestEp->title }}</h5>
                            </div>
                            <button class="w-9 h-9 rounded-full bg-emerald-600 hover:bg-emerald-700 text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                                <svg class="w-4 h-4 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </button>
                        </div>
                    @endif
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 100-6 3 3 0 000 6z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Belum Ada Kanal Podcast</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">Buat episode kajian audio Islami dan materi bimbingan belajar untuk didengarkan siswa kapan saja.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
