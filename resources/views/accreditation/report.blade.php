<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="p-2 bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 rounded-2xl border border-indigo-500/20">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </span>
                    Laporan Resmi Kesiapan Akreditasi BAN-SM
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Laporan asesmen kesiapan akreditasi 8 Standar Nasional Pendidikan (SNP).</p>
            </div>
            <a href="{{ route('accreditation.dashboard') }}" class="px-4 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-xl text-xs font-bold transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 max-w-4xl mx-auto space-y-6">

            <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-150 dark:border-gray-700/80 shadow-sm p-8 space-y-6">
                <!-- Header Report Seal -->
                <div class="text-center border-b border-gray-100 dark:border-gray-700 pb-6 space-y-2">
                    <span class="px-3 py-1 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-mono text-[10px] font-bold rounded-xl uppercase">
                        BADAN AKREDITASI NASIONAL SEKOLAH / MADRASAH (BAN-SM)
                    </span>
                    <h1 class="text-2xl font-black text-gray-900 dark:text-white tracking-tight">{{ $reportData['institution'] }}</h1>
                    <p class="text-xs text-gray-500 font-mono">NPSN: {{ $reportData['npsn'] }} | Tanggal Asesmen: {{ $reportData['generated_at'] }}</p>
                </div>

                <!-- Score Summary Box -->
                <div class="p-6 bg-indigo-50 dark:bg-indigo-950/40 rounded-2xl border border-indigo-200 dark:border-indigo-800 text-center space-y-2">
                    <span class="text-xs font-mono font-bold uppercase text-indigo-700 dark:text-indigo-300">Hasil Prediksi Nilai Akreditasi</span>
                    <h3 class="text-4xl font-black text-indigo-900 dark:text-white">{{ $reportData['accreditation_grade'] }}</h3>
                    <p class="text-sm font-mono font-bold text-emerald-600 dark:text-emerald-400">Skor Total Kesiapan: {{ $reportData['overall_readiness_score'] }} / 100</p>
                </div>

                <!-- Breakdown SNP Table -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold uppercase font-mono text-gray-400">Rincian Perolehan 8 Standar Nasional Pendidikan (SNP)</h4>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-gray-50 dark:bg-gray-900/50 text-gray-400 uppercase font-mono text-[10px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                                <tr>
                                    <th class="px-4 py-3">Kode Standard</th>
                                    <th class="px-4 py-3">Nama Standar Nasional</th>
                                    <th class="px-4 py-3">Indikator Terverifikasi</th>
                                    <th class="px-4 py-3 text-right">Persentase Kesiapan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 font-medium text-gray-700 dark:text-gray-200">
                                @foreach($reportData['snp_standards'] as $snp)
                                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-colors">
                                        <td class="px-4 py-3 font-mono font-bold text-indigo-600 dark:text-indigo-400">{{ $snp['code'] }}</td>
                                        <td class="px-4 py-3 font-bold">{{ $snp['name'] }}</td>
                                        <td class="px-4 py-3 font-mono text-gray-500">{{ $snp['verified_count'] }} / {{ $snp['total_indicators'] }} Indikator</td>
                                        <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">{{ $snp['score_percent'] }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="pt-6 border-t border-gray-100 dark:border-gray-700 flex justify-between items-center text-xs text-gray-500 font-mono">
                    <p>Yayasan Badan Wakaf Sultan Agung (YBWSA)</p>
                    <p>SISTA Enterprise GRC Subsystem &copy; 2026</p>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
