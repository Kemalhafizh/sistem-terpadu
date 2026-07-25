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
                    Detail Hasil Load Test — {{ $test->scenario_name }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Laporan latensi persentil P50, P95, P99 dan stabilitas throughput.</p>
            </div>
            <a href="{{ route('load_test.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard Load Test
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Test Summary Card -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 rounded-3xl text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-700">
                <div class="space-y-2 text-center md:text-left">
                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono text-[10px] font-bold uppercase rounded-xl">
                        SIMULASI {{ strtoupper($test->status) }}
                    </span>
                    <h3 class="text-3xl font-black tracking-tight">{{ $test->scenario_name }}</h3>
                    <p class="text-xs text-gray-300">Beban {{ $test->concurrent_users }} Concurrent Users — Durasi {{ $test->duration_seconds }} Detik</p>
                </div>

                <div class="text-center bg-slate-800/80 p-6 rounded-2xl border border-slate-700/80 shrink-0 min-w-[200px]">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-wider block">Throughput Performa</span>
                    <span class="text-4xl font-black text-emerald-400 font-mono mt-1 block">{{ $test->requests_per_second }} Req/s</span>
                    <span class="text-[11px] text-gray-400 mt-1 block">Total {{ number_format($test->total_requests) }} Request</span>
                </div>
            </div>

            <!-- Percentiles Latency -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Latensi Persentil P50 (Median)</span>
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono block">{{ $test->p50_latency_ms }} ms</span>
                    <span class="text-xs text-emerald-600 font-bold">Respon Cepat</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Latensi Persentil P95</span>
                    <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400 font-mono block">{{ $test->p95_latency_ms }} ms</span>
                    <span class="text-xs text-indigo-600 font-bold">Respon Stabil</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Latensi Persentil P99 (Peak)</span>
                    <span class="text-3xl font-black text-slate-800 dark:text-white font-mono block">{{ $test->p99_latency_ms }} ms</span>
                    <span class="text-xs text-gray-400 font-mono">Batas Aman Enterprise</span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
