<x-app-layout>
    <x-slot name="title">Gap Analysis: {{ $period->name }}</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Raport Mutu (EDS)', 'url' => route('eds.index')],
            ['label' => 'Dashboard Periode', 'url' => route('eds.show', $period->id)],
            ['label' => 'Gap Analysis', 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('eds.show', $period->id) }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-650 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                        Analisis Kesenjangan Mutu (Gap Analysis)
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Daftar indikator di bawah target standar (&lt; 3.00) yang memerlukan tindakan perbaikan segera.</p>
                </div>
            </div>
            
            <div class="shrink-0">
                <a href="{{ route('eds.export-pdf', $period->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-rose-600/15 border border-rose-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Unduh Rekomendasi PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @php
            $totalGaps = count($gaps);
            $unassessed = collect($gaps)->where('is_assessed', false)->count();
            $score1 = collect($gaps)->where('score', 1)->where('is_assessed', true)->count();
            $score2 = collect($gaps)->where('score', 2)->where('is_assessed', true)->count();
        @endphp

        <!-- Panel Ringkasan KPI (Glassmorphic Cards) -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-red-100 dark:bg-red-900/50 rounded-xl text-red-650 dark:text-red-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Total Kesenjangan</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $totalGaps }} Indikator</h3>
                </div>
            </div>
            
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-gray-100 dark:bg-gray-700/50 rounded-xl text-gray-600 dark:text-gray-400">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Belum Dinilai</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $unassessed }} Indikator</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-red-100 dark:bg-red-950/40 rounded-xl text-red-650 dark:text-red-400">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-black text-xs bg-red-200 text-red-800 dark:bg-red-900/50 dark:text-red-300">1</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mutu Kurang (Skor 1)</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $score1 }} Indikator</h3>
                </div>
            </div>

            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex items-center space-x-4">
                <div class="p-3 bg-amber-100 dark:bg-amber-950/40 rounded-xl text-amber-600 dark:text-amber-400">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center font-black text-xs bg-amber-200 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">2</span>
                </div>
                <div>
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Mutu Cukup (Skor 2)</p>
                    <h3 class="text-xl font-extrabold text-gray-800 dark:text-white mt-0.5">{{ $score2 }} Indikator</h3>
                </div>
            </div>
        </div>

        <!-- Tabel Gap Analysis -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-[12%]">Standar</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-[23%]">Kode / Indikator</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center w-[8%]">Skor</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-[22%]">Bukti Fisik / Masalah</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-[25%]">Rencana Aksi Perbaikan</th>
                            <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center w-[10%]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-150/40 dark:divide-gray-700/40">
                        @forelse($gaps as $gap)
                        <tr class="hover:bg-red-50/10 dark:hover:bg-red-950/5 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-gray-100 dark:bg-gray-900 text-gray-650 dark:text-gray-350 border border-gray-200/50 dark:border-gray-850 uppercase">{{ $gap['standard']->code }}</span>
                                <div class="text-[9px] text-gray-400 font-bold mt-1 line-clamp-2">{{ $gap['standard']->name }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-800 dark:text-gray-250">
                                <strong>{{ $gap['indicator']->code }}</strong> — {{ $gap['indicator']->name }}
                                <div class="text-[9px] text-gray-400 mt-1">{{ $gap['indicator']->description }}</div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($gap['is_assessed'])
                                    @if($gap['score'] == 1)
                                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-400 border border-red-200/50 dark:border-red-900/50">1</span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black bg-amber-100 text-amber-800 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/50">2</span>
                                    @endif
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[9px] font-black bg-gray-100 text-gray-450 dark:bg-gray-900 dark:text-gray-550 border border-gray-200 dark:border-gray-750">BELUM</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600 dark:text-gray-400 leading-normal font-semibold">
                                {{ Str::limit($gap['evidence'], 80) }}
                            </td>
                            <td class="px-6 py-4 text-xs text-indigo-750 dark:text-indigo-400 leading-normal font-bold">
                                {{ Str::limit($gap['recommendation'], 90) }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm">
                                @if($period->status !== 'completed')
                                    @can('assess', $period)
                                    <a href="{{ route('eds.assess', [$period->id, $gap['standard']->id]) }}" class="inline-flex items-center px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-md shadow-primary-500/10 transition-all">
                                        Perbaiki
                                    </a>
                                    @else
                                    <a href="{{ route('eds.assess', [$period->id, $gap['standard']->id]) }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 font-bold transition-all text-xs">
                                        Lihat
                                    </a>
                                    @endcan
                                @else
                                <a href="{{ route('eds.assess', [$period->id, $gap['standard']->id]) }}" class="text-gray-500 dark:text-gray-400 hover:text-gray-700 font-bold transition-all text-xs">
                                    Lihat
                                </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-emerald-600 dark:text-emerald-400 font-extrabold italic bg-emerald-50/10 dark:bg-emerald-950/5">
                                <div>🎉 Luar Biasa!</div>
                                <div class="text-xs text-gray-400 mt-1 font-normal uppercase tracking-wider">Seluruh aspek mutu sekolah terdata dalam pencapaian Baik atau Unggul (Skor &ge; 3.00)</div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
