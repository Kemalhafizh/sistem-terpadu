<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </span>
                    Plugin & Extension Marketplace Catalog
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Katalog modul ekstensi resmi dan integrasi ekosistem pendidikan.</p>
            </div>
            <a href="{{ route('plugins.index') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Plugin Terpasang
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                    <span class="px-2.5 py-0.5 bg-blue-100 text-blue-800 font-mono text-[10px] font-bold rounded">VERIFIED OFFICIAL</span>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">Google Classroom Sync Engine</h4>
                    <p class="text-xs text-gray-500">Sinkronisasi otomatis tugas, jadwal, dan nilai siswa dengan Google Classroom.</p>
                    <button class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all">Pasang Plugin (Free)</button>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                    <span class="px-2.5 py-0.5 bg-amber-100 text-amber-800 font-mono text-[10px] font-bold rounded">INTEGRATION</span>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">Bank Syariah Indonesia (BSI) VA Gateway</h4>
                    <p class="text-xs text-gray-500">Modul otentikasi Virtual Account real-time untuk penerimaan SPP siswa.</p>
                    <button class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all">Pasang Plugin (Free)</button>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 font-mono text-[10px] font-bold rounded">AI EXTENSION</span>
                    <h4 class="text-base font-black text-gray-900 dark:text-white">AI Automated Question Generator</h4>
                    <p class="text-xs text-gray-500">Ekstensi pembuat soal otomatis berbasis AI untuk guru mata pelajaran.</p>
                    <button class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all">Pasang Plugin (Free)</button>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
