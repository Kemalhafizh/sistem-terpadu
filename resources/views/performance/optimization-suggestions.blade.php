<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L5.596 15.12a2 2 0 00-1.022.547l-1.42 1.42a2 2 0 00.586 3.414l2.828.943a18 18 0 0010.876 0l2.828-.943a2 2 0 00.586-3.414l-1.42-1.42z"></path>
                        </svg>
                    </span>
                    Rekomendasi Indeks DDL & Eager Loading
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Saran penambahan indeks database otomatis untuk mengeliminasi pemindaian tabel (*full table scan*).</p>
            </div>
            <a href="{{ route('query_performance.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard Query
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="space-y-4">
                @foreach($suggestions as $s)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-mono text-[10px] font-bold rounded uppercase">
                                TABEL: {{ $s['table'] }}
                            </span>
                            <span class="text-xs font-mono text-gray-400">Kolom Target: {{ implode(', ', $s['columns']) }}</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-300 font-medium">{{ $s['reason'] }}</p>
                        <div class="p-3 bg-slate-900 text-emerald-400 font-mono text-xs rounded-xl overflow-x-auto">
                            {{ $s['ddl'] }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
