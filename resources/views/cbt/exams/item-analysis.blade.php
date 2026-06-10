<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">Analisis Butir Soal: {{ $exam->title }}</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $exam->subject?->name }} — {{ $exam->classroom?->name ?? 'Semua Kelas' }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('cbt.exams.analysis.pdf', $exam) }}" class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Cetak PDF (Landscape)
                </a>
                <a href="{{ route('cbt.exams.show', $exam) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5 border border-slate-200 dark:border-slate-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 space-y-6">

        {{-- Top Summary Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Reliabilitas Ujian (KR-20)</p>
                <div class="flex items-baseline gap-2 mt-1">
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white">{{ $analysis['stats']['reliability'] }}</h3>
                    @php
                        $relColor = match($analysis['stats']['reliability_text']) {
                            'Tinggi' => 'text-emerald-600 bg-emerald-500/10 dark:text-emerald-400',
                            'Sedang' => 'text-amber-600 bg-amber-500/10 dark:text-amber-400',
                            default => 'text-rose-600 bg-rose-500/10 dark:text-rose-400'
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded text-[9px] font-black uppercase tracking-wider {{ $relColor }}">{{ $analysis['stats']['reliability_text'] }}</span>
                </div>
                <p class="text-[9px] text-slate-400 mt-1">Mengukur kekonsistenan soal ujian</p>
            </div>

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rata-rata / Median Nilai</p>
                <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-1">
                    {{ $analysis['stats']['average'] }} <span class="text-xs font-semibold text-slate-400">/ {{ $analysis['stats']['median'] }}</span>
                </h3>
                <p class="text-[9px] text-slate-400 mt-1">Standar Deviasi: {{ $analysis['stats']['std_dev'] }}</p>
            </div>

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tingkat Kesukaran Soal</p>
                <div class="flex gap-3 mt-2">
                    <div class="text-center flex-1 bg-emerald-500/5 rounded-xl py-1">
                        <span class="text-xs text-emerald-600 font-black">{{ $analysis['difficulty_counts']['Mudah'] }}</span>
                        <span class="block text-[8px] font-bold text-slate-400 uppercase">Mudah</span>
                    </div>
                    <div class="text-center flex-1 bg-amber-500/5 rounded-xl py-1">
                        <span class="text-xs text-amber-600 font-black">{{ $analysis['difficulty_counts']['Sedang'] }}</span>
                        <span class="block text-[8px] font-bold text-slate-400 uppercase">Sedang</span>
                    </div>
                    <div class="text-center flex-1 bg-rose-500/5 rounded-xl py-1">
                        <span class="text-xs text-rose-600 font-black">{{ $analysis['difficulty_counts']['Sulit'] }}</span>
                        <span class="block text-[8px] font-bold text-slate-400 uppercase">Sulit</span>
                    </div>
                </div>
            </div>

            <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800/80 rounded-2xl p-5 shadow-sm">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Rekomendasi Soal</p>
                <div class="flex gap-3 mt-2">
                    <div class="text-center flex-1 bg-emerald-500/10 rounded-xl py-1">
                        <span class="text-xs text-emerald-600 font-black">{{ $analysis['recommendation_counts']['Pertahankan'] }}</span>
                        <span class="block text-[8px] font-bold text-slate-400 uppercase">Simpan</span>
                    </div>
                    <div class="text-center flex-1 bg-amber-500/10 rounded-xl py-1">
                        <span class="text-xs text-amber-600 font-black">{{ $analysis['recommendation_counts']['Revisi'] }}</span>
                        <span class="block text-[8px] font-bold text-slate-400 uppercase">Revisi</span>
                    </div>
                    <div class="text-center flex-1 bg-rose-500/10 rounded-xl py-1">
                        <span class="text-xs text-rose-600 font-black">{{ $analysis['recommendation_counts']['Buang'] }}</span>
                        <span class="block text-[8px] font-bold text-slate-400 uppercase">Buang</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Analysis Grid Table --}}
        <div class="bg-white/70 dark:bg-slate-900/70 backdrop-blur-md border border-gray-100 dark:border-slate-800 rounded-3xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-gray-100 dark:border-slate-800">
                <h4 class="text-sm font-black text-slate-800 dark:text-white">Daftar Analisis Per Butir Soal</h4>
                <p class="text-[11px] text-slate-400 mt-0.5">Klik pada salah satu baris soal untuk melihat analisis distractor (pengecoh) terperinci.</p>
            </div>
            
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[900px]" x-data="{ activeRow: null }">
                    <thead>
                        <tr class="bg-slate-50/60 dark:bg-slate-950/40 border-b border-gray-100 dark:border-slate-800/80 text-[10px] font-black uppercase tracking-widest text-slate-400">
                            <th class="px-6 py-4 w-12 text-center">No</th>
                            <th class="px-6 py-4">Butir Soal & Pertanyaan</th>
                            <th class="px-6 py-4 text-center">Indeks Kesukaran (P)</th>
                            <th class="px-6 py-4 text-center">Daya Pembeda (D)</th>
                            <th class="px-6 py-4 text-center">Rekomendasi</th>
                            <th class="px-6 py-4">Catatan Perbaikan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60 text-xs font-semibold text-gray-700 dark:text-slate-300">
                        @foreach($analysis['questions'] as $i => $item)
                            @php
                                $q = $item['question'];
                                $diff = $item['difficulty'];
                                $disc = $item['discrimination'];
                                $rec = $item['recommendation'];
                                $distractors = $item['distractors'] ?? [];

                                $rowColor = match($rec['recommendation']) {
                                    'Pertahankan' => 'hover:bg-emerald-500/5 dark:hover:bg-emerald-500/5',
                                    'Revisi' => 'hover:bg-amber-500/5 dark:hover:bg-amber-500/5',
                                    default => 'hover:bg-rose-500/5 dark:hover:bg-rose-500/5'
                                };

                                $badgeColor = match($rec['recommendation']) {
                                    'Pertahankan' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                    'Revisi' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                    default => 'bg-rose-500/10 text-rose-600 border-rose-500/20'
                                };

                                $diffColor = match($diff['difficulty_text']) {
                                    'Mudah' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                    'Sedang' => 'bg-blue-500/10 text-blue-600 border-blue-500/20',
                                    default => 'bg-red-500/10 text-red-600 border-red-500/20'
                                };

                                $discColor = match($disc['discrimination_text']) {
                                    'Baik' => 'bg-emerald-500/10 text-emerald-600 border-emerald-500/20',
                                    'Cukup' => 'bg-amber-500/10 text-amber-600 border-amber-500/20',
                                    default => 'bg-rose-500/10 text-rose-600 border-rose-500/20'
                                };
                            @endphp
                            <!-- Question Row -->
                            <tr class="cursor-pointer transition-colors {{ $rowColor }}" @click="activeRow = (activeRow === {{ $q->id }} ? null : {{ $q->id }})">
                                <td class="px-6 py-4 text-center text-slate-400 font-black">{{ $q->pivot->order ?? ($i + 1) }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-slate-900 dark:text-white font-bold line-clamp-1 pr-6 flex items-center gap-1.5">
                                        {{ $q->question_text }}
                                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform shrink-0" :class="activeRow === {{ $q->id }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </div>
                                    <div class="text-[10px] text-slate-400 mt-0.5">Tipe: {{ $q->type === 'multiple_choice' ? 'Pilihan Ganda' : ($q->type === 'true_false' ? 'Benar/Salah' : 'Esai') }}</div>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="font-black text-sm">{{ $diff['difficulty_index'] }}</div>
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase border {{ $diffColor }}">{{ $diff['difficulty_text'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div class="font-black text-sm">{{ $disc['discrimination_index'] }}</div>
                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase border {{ $discColor }}">{{ $disc['discrimination_text'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-2 py-1 rounded-lg text-[10px] font-black uppercase border {{ $badgeColor }}">{{ $rec['recommendation'] }}</span>
                                </td>
                                <td class="px-6 py-4 text-slate-500 dark:text-slate-400 text-[11px] leading-relaxed">{{ $rec['notes'] }}</td>
                            </tr>

                            <!-- Distractor Analysis Row -->
                            <tr x-show="activeRow === {{ $q->id }}" x-transition style="display: none;" class="bg-slate-50/40 dark:bg-slate-950/20">
                                <td colspan="6" class="px-8 py-5 border-b border-gray-100 dark:border-slate-800/80">
                                    <div class="space-y-4">
                                        <div class="p-3 bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-xl">
                                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Pertanyaan Soal</p>
                                            <p class="text-xs font-semibold text-slate-800 dark:text-white mt-1">{{ $q->question_text }}</p>
                                            @if($q->explanation)
                                                <p class="text-[10px] text-slate-500 mt-2"><span class="font-bold text-emerald-600">Penjelasan:</span> {{ $q->explanation }}</p>
                                            @endif
                                        </div>

                                        @if($q->type === 'multiple_choice')
                                            <div>
                                                <h5 class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Analisis Efektifitas Pengecoh (Distractor Analysis)</h5>
                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                    @foreach($distractors as $key => $dist)
                                                        @php
                                                            $bgColor = $dist['is_correct'] 
                                                                ? 'bg-emerald-500/10 border-emerald-500/20 dark:bg-emerald-950/20' 
                                                                : ($dist['is_effective'] ? 'bg-blue-500/5 border-blue-500/10' : 'bg-rose-500/5 border-rose-500/10');
                                                            $borderColor = $dist['is_correct'] 
                                                                ? 'border-emerald-500/30' 
                                                                : ($dist['is_effective'] ? 'border-blue-500/20' : 'border-rose-500/20');
                                                            $badgeColor = $dist['is_correct'] 
                                                                ? 'bg-emerald-500/20 text-emerald-600' 
                                                                : ($dist['is_effective'] ? 'bg-blue-500/20 text-blue-600' : 'bg-red-500/20 text-red-600');
                                                            $badgeText = $dist['is_correct'] 
                                                                ? 'Kunci Jawaban' 
                                                                : ($dist['is_effective'] ? 'Efektif' : 'Tidak Efektif');
                                                        @endphp
                                                        <div class="p-3 border rounded-xl flex items-start gap-3 {{ $bgColor }} {{ $borderColor }}">
                                                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-black shrink-0 {{ $dist['is_correct'] ? 'bg-emerald-500 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">{{ $dist['option'] }}</span>
                                                            <div class="flex-1 min-w-0 space-y-1">
                                                                <p class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $dist['text'] }}</p>
                                                                <div class="flex items-center gap-2">
                                                                    <div class="flex-1 h-2 bg-slate-200 dark:bg-slate-800 rounded-full overflow-hidden">
                                                                        <div class="h-full rounded-full {{ $dist['is_correct'] ? 'bg-emerald-500' : 'bg-slate-400' }}" style="width: {{ $dist['percentage'] }}%"></div>
                                                                    </div>
                                                                    <span class="text-[10px] font-black text-slate-500 dark:text-slate-400 shrink-0">{{ $dist['count'] }} murid ({{ $dist['percentage'] }}%)</span>
                                                                </div>
                                                                <div class="flex items-center gap-1.5 mt-1">
                                                                    <span class="px-1.5 py-0.5 rounded text-[8px] font-black uppercase tracking-wider {{ $badgeColor }}">{{ $badgeText }}</span>
                                                                    @if(!$dist['is_correct'])
                                                                        <span class="text-[9px] text-slate-400">
                                                                            {{ $dist['is_effective'] ? 'Bagus, dipilih oleh >= 5% siswa' : 'Jelek, kurang memikat pengecoh' }}
                                                                        </span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @else
                                            <div class="p-3 bg-slate-100/50 dark:bg-slate-950/20 border border-slate-200/50 dark:border-slate-800 rounded-xl">
                                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Kunci Jawaban Resmi</p>
                                                <p class="text-xs font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $q->correct_answer }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
