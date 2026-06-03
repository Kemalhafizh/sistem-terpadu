<x-app-layout>
    <x-slot name="title">Checklist Instrumen Akreditasi</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span>📋</span> Checklist Instrumen Akreditasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Evaluasi keselarasan pemenuhan 8 Standar Nasional Pendidikan (SNP) berdasar data operasional sekolah.
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('compliance.export') }}" class="inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl py-2.5 px-5 text-sm font-black uppercase tracking-wider shadow-lg shadow-emerald-500/20 hover:shadow-emerald-500/35 transition-all duration-300 transform hover:-translate-y-0.5">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Unduh Laporan PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Submenu Navigation -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex flex-wrap gap-1">
            <a href="{{ route('compliance.index') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                📊 Dashboard Kepatuhan
            </a>
            <a href="{{ route('compliance.checklist') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all bg-primary-600 text-white shadow-md shadow-primary-500/20">
                📋 Checklist Instrumen
            </a>
            <a href="{{ route('compliance.documents') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                📁 Repositori Bukti Fisik
            </a>
            <a href="{{ route('compliance.timeline') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                📅 Timeline Visitasi
            </a>
        </div>

        <!-- Checklist standard cards -->
        <div class="space-y-6">
            @foreach($standards as $standard)
            @php
                // Map the 8 standard codes to specific metric keys from the report
                $metricKey = null;
                switch ($standard->code) {
                    case 'SNP-1': // Kompetensi Lulusan
                        $metricKey = 'student_attendance'; // mapping as proxy
                        break;
                    case 'SNP-2': // Standar Isi
                        $metricKey = 'syllabi_rate';
                        break;
                    case 'SNP-3': // Standar Proses
                        $metricKey = 'lesson_plan_rate';
                        break;
                    case 'SNP-4': // Standar Penilaian
                        $metricKey = 'teaching_journal_rate';
                        break;
                    case 'SNP-5': // Standar PTK
                        $metricKey = 'teacher_student_ratio';
                        break;
                    case 'SNP-6': // Standar Sarpras
                        $metricKey = 'book_student_ratio';
                        break;
                    case 'SNP-7': // Standar Pengelolaan
                        $metricKey = 'library_rate';
                        break;
                    case 'SNP-8': // Standar Pembiayaan
                        $metricKey = 'finance_score';
                        break;
                }

                $metric = $report['metrics'][$metricKey] ?? null;

                $badgeClass = 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400 border border-red-200 dark:border-red-900/50';
                $badgeText = 'BELUM TERPENUHI';
                $icon = '❌';

                if ($metric) {
                    if ($metric['status'] === 'terpenuhi') {
                        $badgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50';
                        $badgeText = 'TERPENUHI';
                        $icon = '✅';
                    } elseif ($metric['status'] === 'perlu_perbaikan') {
                        $badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50';
                        $badgeText = 'PERLU PERBAIKAN';
                        $icon = '⚠️';
                    }
                }
            @endphp
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg overflow-hidden transition-all duration-300">
                <div class="p-6 border-b border-gray-150/50 dark:border-gray-700/50 bg-gray-50/50 dark:bg-gray-900/30 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div class="flex items-center space-x-3">
                        <span class="text-xl">{{ $icon }}</span>
                        <div>
                            <span class="text-[10px] font-black text-primary-500 uppercase tracking-widest">{{ $standard->code }}</span>
                            <h3 class="text-base font-black text-gray-800 dark:text-white mt-0.5">{{ $standard->name }}</h3>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex px-3 py-1 text-xs font-black rounded-full uppercase tracking-wider {{ $badgeClass }}">
                            {{ $badgeText }}
                        </span>
                    </div>
                </div>

                <div class="p-6 space-y-4">
                    <p class="text-xs text-gray-500 dark:text-gray-400 leading-relaxed font-normal">
                        {{ $standard->description ?: 'Evaluasi parameter instrumen pemenuhan akreditasi untuk ' . strtolower($standard->name) . '.' }}
                    </p>

                    @if($metric)
                    <div class="bg-gray-50 dark:bg-gray-950/40 border border-gray-200/30 dark:border-gray-800/50 p-4 rounded-2xl space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Metrik Penilaian Sistem</span>
                            <span class="text-xs font-black text-gray-800 dark:text-white">{{ $metric['value'] }}</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-900 h-2 rounded-full overflow-hidden">
                            <div class="h-full bg-primary-500 rounded-full" style="width: {{ $metric['score'] }}%"></div>
                        </div>
                        <p class="text-[11px] text-gray-400 font-normal">
                            <strong>Keterangan:</strong> {{ $metric['desc'] }}
                        </p>
                    </div>
                    @endif

                    <!-- Detailed indicators checklist list -->
                    <div class="space-y-2 pt-2">
                        <h4 class="text-xs font-black text-gray-400 uppercase tracking-wider">Checklist Indikator:</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @forelse($standard->indicators as $indicator)
                            <div class="flex items-start space-x-2.5 p-3 bg-white dark:bg-gray-850 rounded-xl border border-gray-100 dark:border-gray-750/30">
                                <span class="text-emerald-500 dark:text-emerald-400 mt-0.5">✓</span>
                                <div>
                                    <div class="text-xs font-black text-gray-800 dark:text-gray-200 leading-snug">Indikator {{ $indicator->code }}</div>
                                    <div class="text-[11px] text-gray-400 mt-1 leading-snug font-normal">{{ $indicator->name }}</div>
                                </div>
                            </div>
                            @empty
                            <div class="col-span-full text-center text-xs text-gray-450 dark:text-gray-500 py-3">
                                Tidak ada indikator terdaftar.
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
