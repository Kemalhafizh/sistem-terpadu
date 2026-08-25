<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-bold text-2xl text-slate-800 dark:text-white leading-tight">
                    {{ __('Dasbor Kinerja Guru 360 Derajat') }}
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                    Evaluasi komprehensif pedagogik, kepribadian, sosial, profesional, dan kepuasan siswa.
                </p>
            </div>
        </div>
    </x-slot>

    <div class="py-6 max-w-7xl mx-auto px-4 sm:px-6 space-y-6">
        @if($metric)
            <!-- Overview Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Kepuasan Siswa</span>
                    <div class="text-3xl font-black text-slate-900 dark:text-white my-1">{{ $metric->student_satisfaction_score }}<span class="text-base font-normal">/100</span></div>
                    <p class="text-xs text-emerald-600 font-semibold">Sangat Memuaskan</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Peer Review Sejawat</span>
                    <div class="text-3xl font-black text-slate-900 dark:text-white my-1">{{ $metric->peer_review_score }}<span class="text-base font-normal">/100</span></div>
                    <p class="text-xs text-cyan-600 font-semibold">Kolaboratif & Teladan</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Rata-rata Nilai Kelas</span>
                    <div class="text-3xl font-black text-slate-900 dark:text-white my-1">{{ $metric->avg_class_grade }}</div>
                    <p class="text-xs text-slate-500">Di atas KKM (75.0)</p>
                </div>
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm">
                    <span class="text-xs font-bold text-slate-500 uppercase">Kehadiran Mengajar</span>
                    <div class="text-3xl font-black text-slate-900 dark:text-white my-1">{{ $metric->attendance_percentage }}%</div>
                    <p class="text-xs text-emerald-600 font-semibold">Tepat Waktu & Disiplin</p>
                </div>
            </div>

            <!-- Radar Competency Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Breakdown 5 Pilar Kompetensi Guru</h3>
                    <div class="space-y-3 pt-2">
                        @foreach($metric->skill_radar_data ?? [] as $pillar => $score)
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-bold">
                                    <span class="text-slate-700 dark:text-slate-300 uppercase">{{ ucfirst(str_replace('_', ' ', $pillar)) }}</span>
                                    <span class="text-emerald-600 dark:text-emerald-400">{{ $score }}/100</span>
                                </div>
                                <div class="w-full h-3 rounded-full bg-slate-100 dark:bg-slate-800 overflow-hidden">
                                    <div class="h-full bg-gradient-to-r from-emerald-500 to-teal-400 rounded-full" style="width: {{ $score }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-sm space-y-4">
                    <h3 class="font-bold text-lg text-slate-800 dark:text-white">Pilih Guru untuk Evaluasi</h3>
                    <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                        @foreach($teachers as $t)
                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-800/60 border border-slate-100 dark:border-slate-700 flex items-center justify-between">
                                <div>
                                    <h4 class="font-bold text-xs text-slate-900 dark:text-white">{{ $t->name }}</h4>
                                    <span class="text-[10px] text-slate-500">{{ $t->email }}</span>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl bg-emerald-100 text-emerald-700 text-xs font-bold">Terpilih</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-12 text-center border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-slate-500">Belum ada data metrik guru yang tersedia.</p>
            </div>
        @endif
    </div>
</x-app-layout>
