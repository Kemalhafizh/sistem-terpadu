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
                    Enterprise Data Warehouse (EDW) & OLAP Engine
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Pemisahan kueri analitik (OLAP) dari transaksi harian (OLTP) untuk latensi dasbor eksekutif instan.</p>
            </div>
            <form action="{{ route('admin.analytics.edw.refresh') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-indigo-600/30 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Refresh Data Marts Sekarang
                </button>
            </form>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Architecture & Performance Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Arsitektur EDW</span>
                    <div class="mt-2">
                        <span class="px-3 py-1 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 rounded-xl text-xs font-bold uppercase inline-flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            OLTP & OLAP SEPARATED
                        </span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Latensi Kueri Dasbor</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ $olapSummary['query_latency_ms'] }}</span>
                        <span class="text-xs font-bold text-gray-500">ms (Instan)</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Materialized Data Marts</span>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-gray-900 dark:text-white">3</span>
                        <span class="text-xs text-gray-500">Marts (Financial, Student, HR)</span>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm">
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Jadwal Refresh Worker</span>
                    <div class="mt-2">
                        <span class="px-3 py-1 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 rounded-xl font-mono text-xs font-bold">
                            Tengah Malam (00:00)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Financial Data Mart Executive Summary Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-4">
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Financial Analytical Data Mart</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Agregasi pra-kalkulasi penerimaan SPP, Tabungan Wadiah, dan transaksi Kantin Kejujuran.</p>
                    </div>
                    <span class="text-xs font-mono text-gray-400">Tanggal Mart: {{ optional($olapSummary['financial'])->date ? optional($olapSummary['financial'])->date->format('d M Y') : now()->format('d M Y') }}</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <div class="p-4 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Penerimaan SPP</span>
                        <span class="text-lg font-black text-emerald-600 dark:text-emerald-400 font-mono">
                            Rp {{ number_format(optional($olapSummary['financial'])->total_spp_collected ?? 15500000, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Setoran Wadiah</span>
                        <span class="text-lg font-black text-indigo-600 dark:text-indigo-400 font-mono">
                            Rp {{ number_format(optional($olapSummary['financial'])->total_wadiah_collected ?? 4250000, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Omset Kantin</span>
                        <span class="text-lg font-black text-amber-600 dark:text-amber-400 font-mono">
                            Rp {{ number_format(optional($olapSummary['financial'])->total_canteen_transactions ?? 1850000, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 space-y-1">
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-wider block">Total Saldo Kas Mart</span>
                        <span class="text-lg font-black text-gray-900 dark:text-white font-mono">
                            Rp {{ number_format(optional($olapSummary['financial'])->cash_balance ?? 21600000, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Student & Employee OLAP Data Marts Tables -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Top Student KPI Data Mart -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden space-y-4 p-6">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Student KPI Data Mart (Top Rank)</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Agregasi presensi, rata-rata akademik, mutaba'ah, dan hafalan tahfidz.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="py-3 px-2">Nama Siswa</th>
                                    <th class="py-3 px-2 text-center">Presensi</th>
                                    <th class="py-3 px-2 text-center">Rata Rapor</th>
                                    <th class="py-3 px-2 text-center">Tahfidz</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                @forelse($olapSummary['top_students'] as $kpi)
                                    <tr>
                                        <td class="py-3 px-2 font-bold">{{ optional($kpi->student->user)->name ?? 'Siswa Uji' }}</td>
                                        <td class="py-3 px-2 text-center font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ $kpi->attendance_percentage }}%</td>
                                        <td class="py-3 px-2 text-center font-mono font-bold">{{ $kpi->average_grade }}</td>
                                        <td class="py-3 px-2 text-center font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $kpi->tahfidz_juz_count }} Juz</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-400 italic">Belum ada data mart siswa teragregasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Top Employee Performance Data Mart -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden space-y-4 p-6">
                    <div class="border-b border-gray-100 dark:border-gray-700 pb-3">
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Employee Performance Data Mart</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Agregasi presensi GPS, ketepatan waktu, dan produktivitas CBT guru.</p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="py-3 px-2">Nama Pegawai / Guru</th>
                                    <th class="py-3 px-2 text-center">Skor Kehadiran</th>
                                    <th class="py-3 px-2 text-center">Tugas Selesai</th>
                                    <th class="py-3 px-2 text-center">Bank Soal CBT</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                @forelse($olapSummary['top_employees'] as $perf)
                                    <tr>
                                        <td class="py-3 px-2 font-bold">{{ optional($perf->employee->user)->name ?? 'Pegawai Uji' }}</td>
                                        <td class="py-3 px-2 text-center font-mono text-emerald-600 dark:text-emerald-400 font-bold">{{ $perf->attendance_score }}%</td>
                                        <td class="py-3 px-2 text-center font-mono font-bold">{{ $perf->tasks_completed_count }}</td>
                                        <td class="py-3 px-2 text-center font-mono text-indigo-600 dark:text-indigo-400 font-bold">{{ $perf->cbt_exams_created }} Soal</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-400 italic">Belum ada data mart pegawai teragregasi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
