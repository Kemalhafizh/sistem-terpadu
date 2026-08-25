<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Papan Tulis Kolaboratif (Real-Time Whiteboard)') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Media pembelajaran visual interaktif dengan anotasi real-time untuk kelas dan rapat.
                </p>
            </div>
            <button onclick="document.getElementById('newSessionModal').classList.remove('hidden')" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl text-sm font-semibold shadow-sm transition-all flex items-center space-x-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Buka Sesi Baru</span>
            </button>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        <!-- Sessions Directory Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($sessions as $sess)
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4 hover:border-emerald-500 transition-all">
                    <div class="flex items-center justify-between">
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $sess->status === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-slate-100 text-slate-700' }}">
                            {{ ucfirst($sess->status) }}
                        </span>
                        <span class="text-xs text-slate-400">{{ $sess->created_at->diffForHumans() }}</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white">{{ $sess->title }}</h3>
                        <p class="text-xs text-slate-500 mt-1">Host: {{ $sess->host->name ?? 'Guru' }}</p>
                    </div>

                    <div class="pt-2 flex items-center justify-between border-t border-slate-100 dark:border-slate-800">
                        <span class="text-xs text-slate-400 font-mono">ID: {{ substr($sess->session_id, 0, 8) }}...</span>
                        <a href="#" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors">
                            Masuk Canvas
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 flex items-center justify-center mx-auto">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Belum Ada Sesi Papan Tulis</h3>
                    <p class="text-sm text-slate-500 max-w-md mx-auto">Buka sesi papan tulis interaktif baru untuk memulai pembelajaran visual kolaboratif.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
