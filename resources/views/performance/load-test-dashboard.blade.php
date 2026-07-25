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
                    Automated Stress Test & Load Testing Suite
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Simulasi beban puncak bersama (PPDB, Ujian CBT, Input Nilai) untuk menguji keandalan sistem.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Run Test Form Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                    <h3 class="text-base font-black text-gray-900 dark:text-white">Jalankan Simulasi Uji Beban (Load Test)</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Picu skenario stress test dengan jumlah pengguna bersama (concurrent users).</p>
                </div>

                <form action="{{ route('load_test.run') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pilih Skenario Pengujian</label>
                        <select name="scenario_name" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-bold">
                            <option value="Ujian CBT Online Massal">Ujian CBT Online Massal</option>
                            <option value="Pendaftaran PPDB Concurrent">Pendaftaran PPDB Concurrent</option>
                            <option value="Input Nilai Rapor Guru">Input Nilai Rapor Guru</option>
                            <option value="Pembayaran SPP Simultaneous">Pembayaran SPP Simultaneous</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Jumlah User Concurrent</label>
                        <input type="number" name="concurrent_users" required value="250" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Durasi Pengujian (Detik)</label>
                        <input type="number" name="duration_seconds" required value="30" class="w-full bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white rounded-xl border border-gray-300 dark:border-gray-700 text-sm px-4 py-2.5 font-mono">
                    </div>

                    <div>
                        <button type="submit" class="w-full px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-rose-600/30">
                            Mulai Simulasi Load Test
                        </button>
                    </div>
                </form>
            </div>

            <!-- Load Test Results Table -->
            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Riwayat Hasil Stress Test</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Throughput Req/Sec, latensi P50/P95/P99, dan persentase error rate.</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th class="px-6 py-4">Skenario</th>
                                <th class="px-6 py-4">Concurrent Users</th>
                                <th class="px-6 py-4">Total Request</th>
                                <th class="px-6 py-4">Throughput (Req/Sec)</th>
                                <th class="px-6 py-4">Latensi (P50/P95/P99)</th>
                                <th class="px-6 py-4">Error Rate</th>
                                <th class="px-6 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                            @forelse($tests as $t)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $t->scenario_name }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $t->concurrent_users }} Users</td>
                                    <td class="px-6 py-4 font-mono text-gray-500">{{ number_format($t->total_requests) }} Req</td>
                                    <td class="px-6 py-4 font-mono font-bold text-emerald-600 text-sm">{{ $t->requests_per_second }} Req/s</td>
                                    <td class="px-6 py-4 font-mono text-[11px] text-gray-500">
                                        {{ $t->p50_latency_ms }}ms / {{ $t->p95_latency_ms }}ms / {{ $t->p99_latency_ms }}ms
                                    </td>
                                    <td class="px-6 py-4 font-mono font-bold text-emerald-600">{{ $t->error_rate_percent }}%</td>
                                    <td class="px-6 py-4 text-right space-x-2">
                                        <a href="{{ route('load_test.results', $t->id) }}" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Detail Results
                                        </a>
                                        <a href="{{ route('load_test.compare', $t->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white rounded-lg text-xs font-bold transition-all shadow-sm">
                                            Komparasi Baseline
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-400 italic">
                                        Belum ada data load test terdaftar.
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
