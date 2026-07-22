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
                    Enterprise APM, Telemetry & SLA 99.9% Dashboard
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemantauan latensi P50/P90/P99, penelusuran Correlation ID (Distributed Tracing), dan detektor Slow Query SQL.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ traceModalOpen: false, activeTrace: null, traceLoading: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Metrics Top Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Latency P99 Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Latensi P99 (< 100ms)</span>
                        <h4 class="text-2xl font-black mt-1 text-emerald-500 font-mono">
                            {{ $telemetry['latency']['p99'] }} ms
                        </h4>
                    </div>
                    <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl font-black text-xs">P99 TARGET</div>
                </div>

                <!-- SLA 99.9% Uptime Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Target Uptime SLA 99.9%</span>
                        <h4 class="text-2xl font-black text-gray-900 dark:text-white mt-1 font-mono">
                            {{ $telemetry['sla']['uptime'] }}%
                        </h4>
                    </div>
                    <div class="p-3 bg-indigo-500/10 text-indigo-500 rounded-2xl font-black text-xs">99.9% SLA</div>
                </div>

                <!-- RAM Memory Usage Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">RAM Allocated (Peak)</span>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1 font-mono">
                            {{ $telemetry['memory']['used_mb'] }} MB <span class="text-xs text-gray-400">({{ $telemetry['memory']['peak_mb'] }}MB Peak)</span>
                        </h4>
                    </div>
                    <div class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl font-black text-xs">RAM</div>
                </div>

                <!-- Disk Storage IO Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Disk Storage Used</span>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1 font-mono">
                            {{ $telemetry['storage']['used_percent'] }}% <span class="text-xs text-gray-400">({{ $telemetry['storage']['free_gb'] }}GB Free)</span>
                        </h4>
                    </div>
                    <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl font-black text-xs">DISK</div>
                </div>
            </div>

            <!-- Distributed Tracing Tester & Latency Percentiles Section -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left: Latency Percentile Breakdown -->
                <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Breakdown Respon Latensi (Percentiles)</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span class="text-gray-500">P50 (Median Response Latency):</span>
                                <span class="font-mono text-emerald-500">{{ $telemetry['latency']['p50'] }} ms</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-emerald-500 h-full rounded-full" style="width: 35%;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span class="text-gray-500">P90 (90th Percentile):</span>
                                <span class="font-mono text-indigo-500">{{ $telemetry['latency']['p90'] }} ms</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-indigo-500 h-full rounded-full" style="width: 65%;"></div>
                            </div>
                        </div>

                        <div>
                            <div class="flex justify-between text-xs font-bold mb-1">
                                <span class="text-gray-500">P99 (99th Percentile Target < 100ms):</span>
                                <span class="font-mono text-amber-500">{{ $telemetry['latency']['p99'] }} ms</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                <div class="bg-amber-500 h-full rounded-full" style="width: 98%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Distributed Tracing Simulator (X-Correlation-ID) -->
                <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Distributed Tracing (X-Correlation-ID Inspector)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Masukan atau buat Correlation ID baru untuk memeriksa jejak eksekusi distributed spans secara melintas modul.</p>

                    <div class="space-y-3">
                        <button @click="
                            traceLoading = true;
                            fetch('{{ route('admin.observability.apm.trace') }}')
                                .then(res => res.json())
                                .then(data => { activeTrace = data; traceLoading = false; traceModalOpen = true; });
                        " class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            Jalankan Simulasi Distributed Trace Request
                        </button>
                    </div>
                </div>

            </div>

            <!-- Slow Query Detector Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Detektor Slow Query SQL (> 200ms Threshold)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Pemantauan otomatis eksekusi SQL lambat yang membutuhkan optimasi indeks database.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase text-[9px] font-bold">
                            <tr>
                                <th class="p-3 rounded-l-xl">ID Query</th>
                                <th class="p-3">Kueri SQL (Statement)</th>
                                <th class="p-3">Pemanggil (Caller)</th>
                                <th class="p-3 text-right rounded-r-xl">Durasi Execution</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-750 font-medium">
                            @foreach($slowQueries as $sq)
                                <tr>
                                    <td class="p-3 font-mono font-bold text-gray-900 dark:text-white">{{ $sq['id'] }}</td>
                                    <td class="p-3 font-mono text-[10px] text-amber-600 dark:text-amber-400 truncate max-w-md" title="{{ $sq['query'] }}">
                                        {{ $sq['query'] }}
                                    </td>
                                    <td class="p-3 text-gray-500 font-mono text-[10px]">{{ $sq['caller'] }}</td>
                                    <td class="p-3 text-right font-mono font-bold text-rose-600">{{ $sq['duration_ms'] }} ms</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Trace Modal -->
        <div x-show="traceModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="traceModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="traceModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="traceModalOpen" x-transition.scale class="inline-block align-bottom bg-gray-900 text-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-800 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                        <h3 class="text-sm font-black uppercase text-indigo-400 tracking-wider">Distributed Trace Result (X-Correlation-ID)</h3>
                        <button @click="traceModalOpen = false" class="text-gray-400 hover:text-white">✕</button>
                    </div>

                    <template x-if="activeTrace">
                        <div class="space-y-4 text-xs font-mono">
                            <div class="p-3 bg-gray-950 rounded-xl border border-gray-800">
                                <span class="text-gray-400 block text-[10px]">CORRELATION ID:</span>
                                <span class="text-indigo-400 font-bold" x-text="activeTrace.correlation_id"></span>
                                <span class="text-emerald-400 float-right font-bold" x-text="'Total Latency: ' + activeTrace.total_latency_ms + ' ms'"></span>
                            </div>

                            <div class="space-y-2">
                                <template x-for="span in activeTrace.spans" :key="span.span_id">
                                    <div class="p-3 bg-gray-800/80 rounded-xl border border-gray-700 flex items-center justify-between">
                                        <div>
                                            <strong class="text-white block font-bold" x-text="span.name"></strong>
                                            <span class="text-[10px] text-gray-400 block" x-text="span.service"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-emerald-400 font-bold block" x-text="span.duration_ms + ' ms'"></span>
                                            <span class="text-[9px] text-gray-400" x-text="span.status"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <div class="flex justify-end pt-2">
                        <button @click="traceModalOpen = false" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl text-xs font-bold">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
