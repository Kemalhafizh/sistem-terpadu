<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </span>
                    Monitoring Progres Eksekusi ETL — {{ $job->name }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Status pemrosesan dan indikator kesehatan data migrasi.</p>
            </div>
            <a href="{{ route('etl.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar ETL
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-8 space-y-6 text-center">
                <span class="px-3 py-1 bg-emerald-100 text-emerald-800 font-mono text-xs font-bold rounded-xl uppercase">
                    STATUS PIPELINE: {{ strtoupper($job->status) }}
                </span>

                <div>
                    <h3 class="text-3xl font-black text-gray-900 dark:text-white font-mono">{{ $job->records_processed }} / {{ $job->records_total }} Record</h3>
                    <p class="text-xs text-gray-500 mt-1">Persentase Ketercapaian Loading Data: {{ $job->progress_percentage }}%</p>
                </div>

                <div class="w-full max-w-xl mx-auto bg-gray-100 dark:bg-gray-700 h-4 rounded-full overflow-hidden">
                    <div class="bg-emerald-600 h-full rounded-full transition-all" style="width: {{ $job->progress_percentage }}%"></div>
                </div>

                @if($job->status === 'completed')
                    <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 rounded-2xl max-w-md mx-auto text-xs text-emerald-800 dark:text-emerald-300 font-bold">
                        Seluruh record berhasil diekstrak, ditransformasikan, dan diunggah ke tabel target!
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
