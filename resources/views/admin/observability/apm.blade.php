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
                    Enterprise APM: Latensi, Ketersediaan & Query Lambat
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Diukur dari setiap request web &amp; API yang benar-benar tercatat — tidak ada angka contoh.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6" x-data="{ traceModalOpen: false, activeTrace: null, traceLoading: false }">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @php
                // Every figure below was measured (RequestMetricsRecorder /
                // PerformanceLogger); this page used to print fixed numbers.
                $lat = $telemetry['latency'];
                $sla = $telemetry['sla'];
                $fmt = fn ($v, $suffix = '') => $v === null ? '–' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.').$suffix;
                $scale = max((float) ($lat['p99'] ?? 0), (float) $lat['target_p99'], 1);
                $bar = fn ($v) => $v === null ? 0 : min(100, round((float) $v / $scale * 100, 1));
                $p99Color = $lat['is_p99_healthy'] === null ? 'text-gray-400' : ($lat['is_p99_healthy'] ? 'text-emerald-500' : 'text-rose-500');
                $slaColor = match ($sla['status']) { 'MEETS TARGET' => 'text-emerald-500', 'BELOW TARGET' => 'text-rose-500', default => 'text-gray-400' };
                $percentileRows = [
                    ['P50 (median)', 'p50', 'bg-emerald-500', 'text-emerald-500'],
                    ['P90', 'p90', 'bg-indigo-500', 'text-indigo-500'],
                    ['P99', 'p99', 'bg-amber-500', 'text-amber-500'],
                ];
            @endphp

            <!-- Metrics Top Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Latency P99 Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Latensi P99 (target &lt; {{ $fmt($lat['target_p99']) }} ms)</span>
                        <h4 class="text-2xl font-black mt-1 {{ $p99Color }} font-mono">
                            {{ $fmt($lat['p99'], ' ms') }}
                        </h4>
                        <p class="text-[10px] text-gray-400">{{ number_format($lat['sample_count'], 0, ',', '.') }} request, {{ $lat['window_hours'] }} jam terakhir</p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 text-emerald-500 rounded-2xl font-black text-xs">P99</div>
                </div>

                <!-- Request availability Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Ketersediaan Request (target {{ $fmt($sla['target']) }}%)</span>
                        <h4 class="text-2xl font-black mt-1 font-mono {{ $slaColor }}">
                            {{ $fmt($sla['availability'], '%') }}
                        </h4>
                        <p class="text-[10px] text-gray-400">
                            @if($sla['availability'] === null)
                                Belum ada request tercatat.
                            @else
                                {{ $sla['error_count'] }} respons 5xx • sisa error budget {{ $fmt($sla['error_budget_remaining'], '%') }}
                            @endif
                        </p>
                        <p class="text-[10px] text-gray-400">Uptime server belum diukur (butuh pemantau eksternal).</p>
                    </div>
                    <div class="p-3 bg-indigo-500/10 text-indigo-500 rounded-2xl font-black text-xs">SLA</div>
                </div>

                <!-- PHP process memory Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-amber-500 uppercase tracking-wider">Memori Proses PHP</span>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1 font-mono">
                            {{ $telemetry['memory']['used_mb'] }} MB <span class="text-xs text-gray-400">({{ $telemetry['memory']['peak_mb'] }}MB puncak)</span>
                        </h4>
                        <p class="text-[10px] text-gray-400">Proses yang menampilkan halaman ini (batas {{ $telemetry['memory']['limit_mb'] }}), bukan RAM server.</p>
                    </div>
                    <div class="p-3 bg-amber-500/10 text-amber-500 rounded-2xl font-black text-xs">RAM</div>
                </div>

                <!-- Disk Storage Card -->
                <div class="bg-white dark:bg-gray-800 p-5 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold text-rose-500 uppercase tracking-wider">Disk Terpakai</span>
                        <h4 class="text-xl font-black text-gray-900 dark:text-white mt-1 font-mono">
                            {{ $fmt($telemetry['storage']['used_percent'], '%') }} <span class="text-xs text-gray-400">({{ $fmt($telemetry['storage']['free_gb'], ' GB') }} kosong)</span>
                        </h4>
                    </div>
                    <div class="p-3 bg-rose-500/10 text-rose-500 rounded-2xl font-black text-xs">DISK</div>
                </div>
            </div>

            <!-- Latency Percentiles & Request Lookup -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Left: Latency Percentile Breakdown -->
                <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Sebaran Latensi Respons (Persentil)</h3>
                    @if($lat['sample_count'] === 0)
                        <p class="text-xs text-gray-500">Belum ada request tercatat dalam {{ $lat['window_hours'] }} jam terakhir.</p>
                    @else
                        <p class="text-xs text-gray-500">
                            Dari {{ number_format($lat['sample_count'], 0, ',', '.') }} request web &amp; API
                            ({{ \Carbon\Carbon::parse($lat['window_start'])->translatedFormat('d M H:i') }} – {{ \Carbon\Carbon::parse($lat['window_end'])->translatedFormat('d M H:i') }}).
                        </p>
                        <div class="space-y-4">
                            @foreach($percentileRows as [$label, $key, $barColor, $textColor])
                                <div>
                                    <div class="flex justify-between text-xs font-bold mb-1">
                                        <span class="text-gray-500">{{ $label }}:</span>
                                        <span class="font-mono {{ $textColor }}">{{ $fmt($lat[$key], ' ms') }}</span>
                                    </div>
                                    <div class="w-full bg-gray-100 dark:bg-gray-700 h-2.5 rounded-full overflow-hidden">
                                        <div class="{{ $barColor }} h-full rounded-full" style="width: {{ $bar($lat[$key]) }}%;"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Right: Correlation ID lookup -->
                <div class="lg:col-span-6 bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Cari Request (X-Correlation-ID)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Setiap respons web &amp; API membawa header <code>X-Correlation-ID</code>. Tempel nilainya untuk melihat request yang tercatat beserta query lambatnya.</p>

                    <form class="space-y-3" x-data="{ correlationId: '', traceError: null }" @submit.prevent="
                        traceLoading = true; traceError = null;
                        fetch('{{ route('admin.observability.apm.trace') }}?correlation_id=' + encodeURIComponent(correlationId.trim()), { headers: { 'Accept': 'application/json' } })
                            .then(async res => ({ ok: res.ok, data: await res.json() }))
                            .then(({ ok, data }) => {
                                traceLoading = false;
                                if (ok) { activeTrace = data; traceModalOpen = true; }
                                else { traceError = data.message || 'Pencarian gagal.'; }
                            })
                            .catch(() => { traceLoading = false; traceError = 'Pencarian gagal (koneksi).'; });
                    ">
                        <input type="text" x-model="correlationId" required maxlength="64" placeholder="mis. 9b2f0c1e-…"
                               class="w-full rounded-xl border-gray-200 dark:border-gray-700 dark:bg-gray-900 text-xs font-mono">
                        <p x-show="traceError" x-text="traceError" class="text-xs text-rose-500"></p>
                        <button type="submit" :disabled="traceLoading || !correlationId.trim()" class="w-full py-3 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white rounded-xl text-xs font-bold shadow-md transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <span x-text="traceLoading ? 'Mencari…' : 'Cari Request'"></span>
                        </button>
                    </form>
                </div>

            </div>

            <!-- Slow Query Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-6 space-y-4">
                <div>
                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-wider">Query SQL Lambat (&ge; {{ $fmt(config('apm.slow_query_ms', 500)) }} ms)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Query yang benar-benar tercatat oleh PerformanceLogger, diurutkan dari yang paling lambat.</p>
                </div>

                @if(empty($slowQueries))
                    <p class="text-xs text-gray-500">Belum ada query lambat yang tercatat.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase text-[9px] font-bold">
                                <tr>
                                    <th class="p-3 rounded-l-xl">#</th>
                                    <th class="p-3">Kueri SQL</th>
                                    <th class="p-3">Request</th>
                                    <th class="p-3">Tercatat</th>
                                    <th class="p-3 text-right rounded-r-xl">Durasi</th>
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
                                        <td class="p-3 text-gray-500 font-mono text-[10px]">{{ $sq['logged_at'] ? \Carbon\Carbon::parse($sq['logged_at'])->translatedFormat('d M H:i') : '–' }}</td>
                                        <td class="p-3 text-right font-mono font-bold text-rose-600">{{ $sq['duration_ms'] }} ms</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>

        <!-- Trace Modal -->
        <div x-show="traceModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="traceModalOpen" x-transition.opacity class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="traceModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="traceModalOpen" x-transition.scale class="inline-block align-bottom bg-gray-900 text-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full border border-gray-800 p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                        <h3 class="text-sm font-black uppercase text-indigo-400 tracking-wider">Request Tercatat (X-Correlation-ID)</h3>
                        <button @click="traceModalOpen = false" class="text-gray-400 hover:text-white">&times;</button>
                    </div>

                    <template x-if="activeTrace">
                        <div class="space-y-4 text-xs font-mono">
                            <div class="p-3 bg-gray-950 rounded-xl border border-gray-800">
                                <span class="text-gray-400 block text-[10px]">CORRELATION ID:</span>
                                <span class="text-indigo-400 font-bold" x-text="activeTrace.correlation_id"></span>
                                <span class="text-emerald-400 float-right font-bold" x-text="'Total: ' + activeTrace.total_latency_ms + ' ms'"></span>
                            </div>

                            <div class="space-y-2">
                                <template x-for="(req, i) in activeTrace.requests" :key="i">
                                    <div class="p-3 bg-gray-800/80 rounded-xl border border-gray-700 flex items-center justify-between">
                                        <div>
                                            <strong class="text-white block font-bold" x-text="req.method + ' ' + req.path"></strong>
                                            <span class="text-[10px] text-gray-400 block" x-text="req.recorded_at"></span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-emerald-400 font-bold block" x-text="req.duration_ms + ' ms'"></span>
                                            <span class="text-[9px]" :class="req.status >= 500 ? 'text-rose-400' : 'text-gray-400'" x-text="'HTTP ' + req.status"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <div class="space-y-2">
                                <span class="text-gray-400 block text-[10px]">QUERY LAMBAT SELAMA REQUEST INI:</span>
                                <p x-show="activeTrace.slow_queries.length === 0" class="text-gray-500">Tidak ada.</p>
                                <template x-for="(q, i) in activeTrace.slow_queries" :key="'q' + i">
                                    <div class="p-3 bg-gray-800/80 rounded-xl border border-gray-700 flex items-center justify-between gap-3">
                                        <span class="text-amber-400 truncate" x-text="q.sql"></span>
                                        <span class="text-rose-400 font-bold whitespace-nowrap" x-text="q.time_ms + ' ms'"></span>
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
