<x-app-layout>
    <x-slot name="title">Detail Penilaian Kinerja SISTA</x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                    📝 Detail Hasil Evaluasi Kinerja
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pegawai: {{ $evaluation->employee->user->name }} | Periode: {{ $evaluation->period }}
                </p>
            </div>
            <a href="{{ route('hrd.evaluations.report') }}" class="bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-white rounded-xl py-2 px-4 text-xs font-bold transition-all">
                ⬅️ Kembali
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Skor Details -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl text-center flex flex-col justify-center items-center">
            <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Skor Akhir</span>
            <div class="w-32 h-32 rounded-full border-8 border-indigo-600 flex flex-col items-center justify-center mt-6 shadow-inner">
                <span class="text-3xl font-black text-indigo-750 dark:text-indigo-400">{{ $evaluation->total_score }}</span>
                <span class="text-[10px] text-gray-400 font-bold">RATA-RATA</span>
            </div>

            <div class="mt-6">
                <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Predikat Kinerja</span>
                <h3 class="font-black text-4xl text-emerald-600 dark:text-emerald-400 mt-1">{{ $evaluation->grade }}</h3>
                <p class="text-[10px] text-gray-450 mt-1">Status: {{ strtoupper($evaluation->status) }}</p>
            </div>
        </div>

        <!-- Detail Kriteria & Kualitatif -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl md:col-span-2 space-y-6">
            <!-- Nilai Per Kriteria -->
            <div class="space-y-4">
                <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">🎯 Rincian Kriteria Penilaian</h3>
                @php
                    $scores = is_string($evaluation->criteria_scores) ? json_decode($evaluation->criteria_scores, true) : $evaluation->criteria_scores;
                @endphp
                <div class="grid grid-cols-2 gap-4">
                    @foreach($scores as $key => $val)
                        <div class="p-3 bg-gray-55/50 dark:bg-gray-900/55 rounded-2xl border border-gray-150 dark:border-gray-700/50 text-xs">
                            <span class="block text-[9px] text-gray-400 font-bold uppercase">{{ $key }}</span>
                            <span class="font-black text-lg text-gray-800 dark:text-white mt-1">{{ $val }} / 100</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Ulasan Kualitatif -->
            <div class="space-y-4 pt-4 border-t border-gray-150 dark:border-gray-700/50">
                <h3 class="text-xs font-black text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">💬 Catatan & Ulasan Evaluator</h3>
                
                <div class="space-y-3">
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Kekuatan (Strengths)</span>
                        <p class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-200/50 dark:border-gray-700/50 mt-1 italic">
                            "{{ $evaluation->strengths ?? 'Tidak ada catatan.' }}"
                        </p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Peningkatan (Improvements Needed)</span>
                        <p class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-200/50 dark:border-gray-700/50 mt-1 italic">
                            "{{ $evaluation->improvements ?? 'Tidak ada catatan.' }}"
                        </p>
                    </div>
                    <div>
                        <span class="block text-[10px] font-bold text-gray-400 uppercase tracking-wider">Sasaran / Rencana Kerja Depan (Goals)</span>
                        <p class="text-xs text-gray-700 dark:text-gray-300 bg-gray-50/50 dark:bg-gray-900/50 p-3 rounded-xl border border-gray-200/50 dark:border-gray-700/50 mt-1 italic">
                            "{{ $evaluation->goals ?? 'Tidak ada catatan.' }}"
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
