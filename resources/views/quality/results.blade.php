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
                    Hasil Evaluasi Diri Sekolah (EDS) — TA {{ $cycle->academic_year }} {{ ucfirst($cycle->semester) }}
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Laporan analitis pencapaian mutu internal berdasarkan skala 1.00 s.d. 4.00.</p>
            </div>
            <a href="{{ route('quality.cycles') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Siklus
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            @if(isset($results))
                <!-- Summary Card -->
                <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 rounded-3xl text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-700">
                    <div class="space-y-2 text-center md:text-left">
                        <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono text-[10px] font-bold uppercase rounded-xl">
                            KATEGORI RATA-RATA EDS
                        </span>
                        <h3 class="text-3xl font-black tracking-tight">{{ $results['overall_category'] }}</h3>
                        <p class="text-xs text-gray-300">SMA Islam Sultan Agung 1 Semarang — SPMI Internal</p>
                    </div>

                    <div class="text-center bg-slate-800/80 p-6 rounded-2xl border border-slate-700/80 shrink-0 min-w-[200px]">
                        <span class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-wider block">Mean Score Indeks Mutu</span>
                        <span class="text-4xl font-black text-emerald-400 font-mono mt-1 block">{{ $results['overall_mean'] }} / 4.00</span>
                        <span class="text-[11px] text-gray-400 mt-1 block">Berdasarkan 8 SNP</span>
                    </div>
                </div>

                <!-- Results Table -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">Rincian Nilai EDS Per Standar Mutu</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Rata-rata skor evaluasi diri internal dari dewan guru dan tim SPMI.</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-4">Kode Standard</th>
                                    <th class="px-6 py-4">Nama Standar Mutu</th>
                                    <th class="px-6 py-4">Jumlah Respon</th>
                                    <th class="px-6 py-4">Rata-Rata Skor (1 - 4)</th>
                                    <th class="px-6 py-4">Kategori Capaian</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                @foreach($results['standards_breakdown'] as $res)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                        <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $res['code'] }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $res['name'] }}</td>
                                        <td class="px-6 py-4 font-mono text-gray-500">{{ $res['response_count'] }} Respon</td>
                                        <td class="px-6 py-4 font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">{{ number_format($res['mean_score'], 2) }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-mono text-[10px] font-bold rounded">
                                                {{ $res['rating'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if(isset($comparison))
                <!-- Trend Comparison Table -->
                <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm overflow-hidden">
                    <div class="p-6 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                        <div>
                            <h3 class="text-base font-black text-gray-900 dark:text-white">Analisis Tren Perbandingan Antar Periode EDS</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Periode Saat Ini ({{ $comparison['current_cycle'] }}) vs Periode Sebelumnya ({{ $comparison['previous_cycle'] }}).</p>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-6 py-4">Kode Standard</th>
                                    <th class="px-6 py-4">Nama Standar</th>
                                    <th class="px-6 py-4">Skor Saat Ini</th>
                                    <th class="px-6 py-4">Skor Sebelumnya</th>
                                    <th class="px-6 py-4">Selisih Delta</th>
                                    <th class="px-6 py-4 text-right">Tren Capaian Mutu</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                @foreach($comparison['comparison'] as $cmp)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                        <td class="px-6 py-4 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $cmp['code'] }}</td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">{{ $cmp['name'] }}</td>
                                        <td class="px-6 py-4 font-mono text-sm font-bold">{{ number_format($cmp['current_score'], 2) }}</td>
                                        <td class="px-6 py-4 font-mono text-gray-400 text-sm">{{ number_format($cmp['previous_score'], 2) }}</td>
                                        <td class="px-6 py-4 font-mono font-bold {{ $cmp['difference'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                            {{ $cmp['difference'] > 0 ? '+' : '' }}{{ number_format($cmp['difference'], 2) }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="px-2.5 py-0.5 uppercase font-mono text-[10px] font-bold rounded {{ $cmp['trend'] === 'improving' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400' : 'bg-slate-100 text-slate-700' }}">
                                                {{ strtoupper($cmp['trend']) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
