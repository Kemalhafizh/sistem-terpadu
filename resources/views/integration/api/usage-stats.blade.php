<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </span>
                    Statistik Penggunaan & Latensi API Gateway
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemantauan performa lalu lintas REST & GraphQL Gateway API secara real-time.</p>
            </div>
            <a href="{{ route('api.clients') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Client Management
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Total Permintaan API Hari Ini</span>
                    <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400 font-mono block">14,250 Req</span>
                    <span class="text-xs text-emerald-600 font-bold">+12% dari kemarin</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Rata-Rata Latensi Respon</span>
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono block">18.4 ms</span>
                    <span class="text-xs text-gray-400 font-mono">Status: OPTIMAL</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Error Rate (HTTP 5xx / 4xx)</span>
                    <span class="text-3xl font-black text-slate-800 dark:text-white font-mono block">0.02%</span>
                    <span class="text-xs text-emerald-600 font-bold">Uptime 99.99%</span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
