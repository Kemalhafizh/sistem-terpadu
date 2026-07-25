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
                    Database Query Optimizer & N+1 Sentinel
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Deteksi otomatis N+1 Query, pemantauan Slow Queries (> 200ms), dan rekomendasi indeks.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('query_performance.slow') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    Slow Queries Log
                </a>
                <a href="{{ route('query_performance.suggestions') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                    Saran Indeks DDL
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Health Score Database</span>
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono block">{{ $report['health_score'] }} / 100</span>
                    <span class="text-xs text-emerald-600 font-bold">Status: OPTIMAL</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Total Query Dianalisis</span>
                    <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400 font-mono block">{{ $report['total_queries_analyzed'] }}</span>
                    <span class="text-xs text-gray-400 font-mono">Real-time Logging</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Jumlah Slow Queries (> 200ms)</span>
                    <span class="text-3xl font-black text-rose-600 dark:text-rose-400 font-mono block">{{ $report['slow_queries_count'] }}</span>
                    <span class="text-xs text-gray-400">Butuh Optimasi</span>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm text-center space-y-2">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase">Rata-Rata Latensi Query</span>
                    <span class="text-3xl font-black text-emerald-600 dark:text-emerald-400 font-mono block">{{ $report['average_latency_ms'] }} ms</span>
                    <span class="text-xs text-emerald-600 font-bold">Sangat Cepat</span>
                </div>
            </div>

            <!-- Recent Query Logs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Log Eksekusi Query Terbaru</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Monitoring latensi eksekusi SQL per route.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Route Name</th>
                                <th class="px-6 py-4">Query SQL</th>
                                <th class="px-6 py-4">Waktu Eksekusi</th>
                                <th class="px-6 py-4">Status Sentinel</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($logs as $l)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $l->route }}</td>
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-600 dark:text-gray-300 truncate max-w-md">{{ $l->query_sql }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $l->execution_time_ms }} ms</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 {{ $l->is_slow ? 'bg-rose-100 text-rose-800' : 'bg-emerald-100 text-emerald-800' }} font-mono text-[10px] font-bold rounded uppercase">
                                            {{ $l->is_slow ? 'SLOW QUERY' : 'NORMAL' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada log query tercatat.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
