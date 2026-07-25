<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                        </svg>
                    </span>
                    BAN-SM Accreditation Readiness Dashboard
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Sistem pemantauan otomatis kesiapan akreditasi sekolah berdasarkan 8 Standar Nasional Pendidikan (SNP).</p>
            </div>
            <a href="{{ route('accreditation.report') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-all shadow-md flex items-center gap-2">
                <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Cetak Laporan Kesiapan BAN-SM
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Overall Readiness Summary Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-8 rounded-3xl text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6 border border-slate-700">
                <div class="space-y-2 text-center md:text-left">
                    <span class="px-3 py-1 bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono text-[10px] font-bold uppercase rounded-xl">
                        PROYEKSI AKREDITASI BAN-SM
                    </span>
                    <h3 class="text-3xl font-black tracking-tight">{{ $overall['grade'] }}</h3>
                    <p class="text-xs text-gray-300">SMA Islam Sultan Agung 1 Semarang — Yayasan Badan Wakaf Sultan Agung (YBWSA)</p>
                </div>

                <div class="text-center bg-slate-800/80 p-6 rounded-2xl border border-slate-700/80 shrink-0 min-w-[200px]">
                    <span class="text-[10px] font-mono font-bold text-gray-400 uppercase tracking-wider block">Skor Kesiapan Akumulasi</span>
                    <span class="text-4xl font-black text-emerald-400 font-mono mt-1 block">{{ $overall['overall_score'] }} / 100</span>
                    <span class="text-[11px] text-gray-400 mt-1 block">Berdasarkan 8 Standar Nasional (SNP)</span>
                </div>
            </div>

            <!-- 8 SNP Standards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($overall['standards_breakdown'] as $std)
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm space-y-4 hover:border-indigo-500/50 transition-all">
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3">
                            <span class="px-2.5 py-0.5 bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300 font-mono text-[10px] font-bold rounded">
                                {{ $std['code'] }}
                            </span>
                            <span class="font-mono text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ $std['score_percent'] }}%
                            </span>
                        </div>

                        <div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white leading-tight line-clamp-2 min-h-[2.5rem]">{{ $std['name'] }}</h4>
                            <p class="text-xs font-mono text-gray-400 mt-1">Terverifikasi: {{ $std['verified_count'] }} / {{ $std['total_indicators'] }} Indikator</p>
                        </div>

                        <!-- Progress Bar -->
                        <div class="w-full bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                            <div class="bg-indigo-600 h-full rounded-full transition-all" style="width: {{ $std['score_percent'] }}%"></div>
                        </div>

                        <div class="pt-2">
                            <a href="{{ route('accreditation.standard_detail', $std['standard_id']) }}" class="w-full py-2 bg-gray-50 dark:bg-gray-900 hover:bg-indigo-50 dark:hover:bg-indigo-950/40 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center justify-center gap-1 border border-gray-200 dark:border-gray-700">
                                <span>Buka Indikator & Bukti Fisik</span>
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-app-layout>
