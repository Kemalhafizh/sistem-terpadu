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
                    Enterprise Vendor SLA & Service Level Tracker
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemantauan otomatis kualitas ketersediaan (Uptime %), latensi P95, dan kepatuhan kontrak SLA penyedia pihak ketiga.</p>
            </div>
            <form action="{{ route('admin.vendor-sla.probe') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Jalankan Pengujian SLA Sekarang
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Summary Overview Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Status Kesehatan Vendor</span>
                    <div class="mt-2">
                        @if($overallCompliant)
                            <span class="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                ALL VENDORS COMPLIANT
                            </span>
                        @else
                            <span class="px-3 py-1 bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                SLA BREACH DETECTED
                            </span>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Total Pengujian Probe</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $totalProbes }}</span>
                        <span class="text-xs text-gray-500">Pengujian Sampel</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Vendor Terpantau</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-gray-900 dark:text-white">4</span>
                        <span class="text-xs text-gray-500">Penyedia (WA, Payment, Dapodik, EMIS)</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Standar Kepatuhan Kontrak</span>
                    <div class="mt-2">
                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 rounded-xl font-mono text-xs font-bold">
                            99.0% - 99.9% Uptime Target
                        </span>
                    </div>
                </div>
            </div>

            <!-- Vendor Scorecards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($slaSummary as $key => $vendor)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                            <div>
                                <h3 class="text-base font-black text-gray-900 dark:text-white">{{ $vendor['name'] }}</h3>
                                <span class="text-xs font-mono text-gray-400">{{ $vendor['endpoint'] }}</span>
                            </div>
                            <span class="px-3 py-1 rounded-xl text-xs font-bold uppercase font-mono {{ $vendor['latest_status'] === 'UP' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : ($vendor['latest_status'] === 'DEGRADED' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400') }}">
                                {{ $vendor['latest_status'] }}
                            </span>
                        </div>

                        <div class="grid grid-cols-3 gap-4 font-mono">
                            <div class="p-3 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 text-center">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Uptime %</span>
                                <span class="text-lg font-black {{ $vendor['is_sla_compliant'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $vendor['uptime_percentage'] }}%
                                </span>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 text-center">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Latensi P95</span>
                                <span class="text-lg font-black text-indigo-600 dark:text-indigo-400">
                                    {{ $vendor['p95_latency_ms'] }} ms
                                </span>
                            </div>

                            <div class="p-3 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 text-center">
                                <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Target SLA</span>
                                <span class="text-lg font-black text-gray-800 dark:text-gray-200">
                                    {{ $vendor['target_sla'] }}%
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Recent Probe Logs Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden space-y-4">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Log Pengujian Probe SLA Terbaru</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Riwayat latensi dan respon HTTP dari seluruh endpoint vendor penyedia.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Nama Vendor</th>
                                <th class="px-6 py-4">Status Service</th>
                                <th class="px-6 py-4">Latensi (ms)</th>
                                <th class="px-6 py-4">HTTP Status Code</th>
                                <th class="px-6 py-4">Waktu Pemeriksaan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($recentLogs as $log)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold">{{ $log->vendor_name }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2.5 py-0.5 rounded font-mono text-[10px] font-bold uppercase {{ $log->status === 'UP' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : ($log->status === 'DEGRADED' ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-400') }}">
                                            {{ $log->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $log->latency_ms }} ms</td>
                                    <td class="px-6 py-4 font-mono">{{ $log->http_status }}</td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ $log->checked_at->format('d M Y H:i:s') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-400 italic">Belum ada riwayat pengujian vendor. Klik "Jalankan Pengujian SLA Sekarang" di atas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
