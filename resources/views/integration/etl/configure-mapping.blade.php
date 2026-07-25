<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </span>
                    Visual Column Mapper ETL — {{ $job->name }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemetaan kolom sumber data berkas eksternal ke skema kolom tabel {{ $job->target_table }}.</p>
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

            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-6">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Pemetaan Kolom Visual (Mapping Config)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Pilih pencocokan kolom secara presisi sebelum pipeline dijalankan.</p>
                    </div>
                </div>

                <div class="space-y-4 font-mono text-xs">
                    <div class="grid grid-cols-2 p-3 bg-gray-50 dark:bg-gray-900 rounded-xl font-bold uppercase text-gray-500">
                        <div>Kolom Berkas Sumber ({{ strtoupper($job->source_type) }})</div>
                        <div>Kolom Skema Database Target ({{ $job->target_table }})</div>
                    </div>

                    <div class="grid grid-cols-2 p-3 border border-gray-100 dark:border-gray-700 rounded-xl items-center">
                        <span class="font-bold text-indigo-600">Column A (NISN Siswa)</span>
                        <span class="font-bold text-emerald-600">students.nisn</span>
                    </div>

                    <div class="grid grid-cols-2 p-3 border border-gray-100 dark:border-gray-700 rounded-xl items-center">
                        <span class="font-bold text-indigo-600">Column B (Nama Lengkap)</span>
                        <span class="font-bold text-emerald-600">students.name</span>
                    </div>

                    <div class="grid grid-cols-2 p-3 border border-gray-100 dark:border-gray-700 rounded-xl items-center">
                        <span class="font-bold text-indigo-600">Column C (Kelas & Rombel)</span>
                        <span class="font-bold text-emerald-600">students.classroom</span>
                    </div>
                </div>

                <form action="{{ route('etl.execute', $job->id) }}" method="POST" class="pt-4 flex justify-end">
                    @csrf
                    <button type="submit" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-emerald-600/30">
                        Simpan Pemetaan & Eksekusi ETL Pipeline
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-app-layout>
