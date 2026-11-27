<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="font-semibold text-xl text-slate-900 leading-tight dark:text-white">
                    {{ __('Analitik & Dashboard Eksekutif Lanjutan') }}
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ __('Ringkasan capaian kehadiran, akademik, keuangan, dan kedisiplinan sekolah') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('analytics.index', ['clear_cache' => 1]) }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-lg text-xs font-medium transition-colors shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.2"></path></svg>
                    <span>{{ __('Segarkan Data') }}</span>
                </a>
                <a href="{{ route('analytics.export.all') }}" class="inline-flex items-center gap-2 px-3.5 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-lg text-xs font-medium transition-colors shadow-sm">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>{{ __('Ekspor Semua (Excel)') }}</span>
                </a>
            </div>
        </div>
    </x-slot>

    <x-vendor.script lib="apexcharts" />

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            {{-- Pintasan Analitik Rinci --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
                <a href="{{ route('analytics.student-risk') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-rose-600 dark:text-rose-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('Pemantauan Risiko') }}</span>
                </a>

                <a href="{{ route('analytics.attendance') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('Analitik Kehadiran') }}</span>
                </a>

                <a href="{{ route('analytics.academic') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('Analitik Akademik') }}</span>
                </a>

                <a href="{{ route('analytics.financial') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('Analitik Keuangan') }}</span>
                </a>

                <a href="{{ route('analytics.islamic') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 21v-4m18 4v-4M12 3a9 9 0 00-9 9h18a9 9 0 00-9-9z"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('BPI & Keislaman') }}</span>
                </a>

                <a href="{{ route('analytics.heatmap') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('Peta Kehadiran') }}</span>
                </a>

                <a href="{{ route('analytics.comparison') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('Komparasi Tahunan') }}</span>
                </a>

                <a href="{{ route('analytics.custom-report') }}" class="flex flex-col items-start justify-between p-3.5 bg-white dark:bg-slate-800 rounded-xl hover:border-primary-500/60 dark:hover:border-primary-500/60 transition-colors border border-slate-200 dark:border-slate-700">
                    <svg class="w-5 h-5 text-slate-600 dark:text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                    <span class="text-xs font-medium text-slate-800 dark:text-slate-200">{{ __('Laporan Kustom') }}</span>
                </a>
            </div>

            {{-- Filter Rombel --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-medium text-sm text-slate-900 dark:text-white">{{ __('Filter Analitik Akademik') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Pilih kelas untuk menyaring distribusi nilai secara dinamis') }}</p>
                    </div>
                </div>
                <form action="{{ route('analytics.index') }}" method="GET" class="w-full md:w-auto flex items-center gap-2">
                    <select name="classroom_id" onchange="this.form.submit()" class="w-full md:w-64 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm font-medium text-slate-700 dark:text-slate-200 focus:border-primary-500 focus:ring-primary-500">
                        <option value="">{{ __('Seluruh Kelas') }}</option>
                        @foreach($classrooms as $class)
                            <option value="{{ $class->id }}" @selected($classroomId == $class->id)>
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>

            {{-- Metrik Utama --}}
            @php
                $latestAttendance = $attendanceTrend->last();
                $presencePct = $latestAttendance ? $latestAttendance['percentage'] : 0.0;
            @endphp
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Rata-rata Kehadiran') }}</p>
                    <h3 class="text-3xl font-semibold text-slate-900 dark:text-white mt-2 tabular-nums">{{ $presencePct }}%</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Periode bulan terakhir') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Rasio Pelunasan SPP') }}</p>
                    <h3 class="text-3xl font-semibold text-emerald-600 dark:text-emerald-400 mt-2 tabular-nums">{{ $finance['global']['paid_percentage'] }}%</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Dari total tagihan terbit') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Poin Kedisiplinan') }}</p>
                    <h3 class="text-3xl font-semibold text-rose-600 dark:text-rose-400 mt-2 tabular-nums">{{ $bk['global']['penalty_points'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Akumulasi poin pelanggaran') }}</p>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-5 shadow-sm border border-slate-200 dark:border-slate-700">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ __('Rata-rata E-Learning') }}</p>
                    <h3 class="text-3xl font-semibold text-slate-900 dark:text-white mt-2 tabular-nums">{{ $elearning['average_score'] }}</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ __('Nilai tugas dan evaluasi daring') }}</p>
                </div>
            </div>

            {{-- Grafik Kehadiran & Akademik --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Tren Kehadiran Bulanan') }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Perbandingan hadir, sakit, izin, dan alpa (6 bulan)') }}</p>
                        </div>
                        <a href="{{ route('analytics.export.attendance') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 flex items-center justify-center transition-colors" title="{{ __('Ekspor PDF') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </a>
                    </div>
                    <div id="attendanceTrendChart" class="w-full h-80"></div>
                </div>

                <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Rata-rata Nilai Mata Pelajaran') }}</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('10 mata pelajaran dengan capaian rata-rata tertinggi') }}</p>
                        </div>
                        <a href="{{ route('analytics.export.grades') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 flex items-center justify-center transition-colors" title="{{ __('Ekspor PDF') }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </a>
                    </div>
                    <div id="subjectGradesChart" class="w-full h-80"></div>
                </div>
            </div>

            {{-- Seksi Keuangan --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white">{{ __('Rasio Tunggakan & Keuangan Sekolah') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Rasio pelunasan global dan rincian per kelas') }}</p>
                    </div>
                    <a href="{{ route('analytics.export.finance') }}" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 flex items-center justify-center transition-colors" title="{{ __('Ekspor PDF') }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </a>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                    <div class="flex flex-col items-center justify-center p-4 border-b xl:border-b-0 xl:border-e border-slate-200 dark:border-slate-700">
                        <h5 class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-3 text-center">{{ __('Status Pelunasan Global') }}</h5>
                        <div id="financialDonutChart" class="w-full flex justify-center"></div>
                        <div class="mt-4 text-center space-y-1 tabular-nums">
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Total Tagihan') }}: <span class="font-semibold text-slate-900 dark:text-white">Rp {{ number_format($finance['global']['total'], 0, ',', '.') }}</span></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Lunas') }}: <span class="font-semibold text-emerald-600 dark:text-emerald-400">Rp {{ number_format($finance['global']['paid'], 0, ',', '.') }}</span></p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Tunggakan') }}: <span class="font-semibold text-rose-600 dark:text-rose-400">Rp {{ number_format($finance['global']['unpaid'], 0, ',', '.') }}</span></p>
                        </div>
                    </div>

                    <div class="xl:col-span-2 p-2">
                        <h5 class="text-xs font-medium text-slate-600 dark:text-slate-300 mb-3">{{ __('Tunggakan vs Pelunasan per Kelas') }}</h5>
                        <div id="financeClassroomsChart" class="w-full h-72"></div>
                    </div>
                </div>
            </div>

            {{-- Seksi BK & Kinerja Guru --}}
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-1 bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700 flex flex-col">
                    <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('Penalti vs Penghargaan BK') }}</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ __('Perbandingan catatan pelanggaran dan prestasi siswa') }}</p>
                    <div id="bkRadarChart" class="w-full flex justify-center my-auto"></div>
                    <div class="mt-5 grid grid-cols-2 gap-3 text-center tabular-nums">
                        <div class="p-3 bg-rose-50 dark:bg-rose-950/30 border border-rose-200/60 dark:border-rose-800/50 rounded-lg">
                            <p class="text-xs font-medium text-rose-700 dark:text-rose-300">{{ __('Pelanggaran') }}</p>
                            <h4 class="text-base font-semibold text-rose-700 dark:text-rose-300 mt-1">{{ $bk['global']['penalty_count'] }} {{ __('kasus') }}</h4>
                        </div>
                        <div class="p-3 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200/60 dark:border-emerald-800/50 rounded-lg">
                            <p class="text-xs font-medium text-emerald-700 dark:text-emerald-300">{{ __('Penghargaan') }}</p>
                            <h4 class="text-base font-semibold text-emerald-700 dark:text-emerald-300 mt-1">{{ $bk['global']['reward_count'] }} {{ __('kasus') }}</h4>
                        </div>
                    </div>
                </div>

                <div class="xl:col-span-2 space-y-6">
                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('Kinerja Guru & Jurnal Harian') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ __('10 guru paling konsisten mengisi jurnal mengajar harian') }}</p>
                        <div id="teacherPerformanceChart" class="w-full h-64"></div>
                    </div>

                    <div class="bg-white dark:bg-slate-800 rounded-xl p-6 shadow-sm border border-slate-200 dark:border-slate-700">
                        <h4 class="text-base font-semibold text-slate-900 dark:text-white mb-1">{{ __('Pertumbuhan Pendaftar SPMB') }}</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ __('Tren penerimaan calon siswa baru antar tahun ajaran') }}</p>
                        <div id="spmbGrowthChart" class="w-full h-64"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accentColor = '#0d9488';
            const secondarySeriesColor = '#64748b';
            const successColor = '#10b981';
            const warningColor = '#f59e0b';
            const dangerColor = '#e11d48';

            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#94a3b8' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            const attendanceTrendData = @json($attendanceTrend);
            new ApexCharts(document.querySelector("#attendanceTrendChart"), {
                chart: { type: 'area', height: 320, toolbar: { show: false } },
                series: [
                    { name: @js(__('Hadir')), data: attendanceTrendData.map(d => d.H) },
                    { name: @js(__('Sakit')), data: attendanceTrendData.map(d => d.S) },
                    { name: @js(__('Izin')), data: attendanceTrendData.map(d => d.I) },
                    { name: @js(__('Alpa')), data: attendanceTrendData.map(d => d.A) }
                ],
                xaxis: { categories: attendanceTrendData.map(d => d.month), labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [successColor, warningColor, secondarySeriesColor, dangerColor],
                stroke: { curve: 'smooth', width: 2.5 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0.02 } },
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();

            const gradesData = @json($grades['top_subjects']);
            new ApexCharts(document.querySelector("#subjectGradesChart"), {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                plotOptions: {
                    bar: { horizontal: true, barHeight: '58%', borderRadius: 6, distributed: false }
                },
                series: [{ name: @js(__('Rata-rata Nilai')), data: gradesData.map(d => d.average) }],
                xaxis: { categories: gradesData.map(d => d.name), labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [accentColor],
                grid: { borderColor: gridColor },
                legend: { show: false },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();

            const financeData = @json($finance['global']);
            new ApexCharts(document.querySelector("#financialDonutChart"), {
                chart: { type: 'donut', height: 250 },
                series: [parseFloat(financeData.paid), parseFloat(financeData.unpaid)],
                labels: [@js(__('Sudah Dibayar')), @js(__('Tunggakan'))],
                colors: [successColor, dangerColor],
                legend: { position: 'bottom', labels: { colors: textColor } },
                dataLabels: { enabled: true, formatter: (val) => val.toFixed(1) + "%" },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();

            const financeClasses = @json($finance['by_classroom']);
            new ApexCharts(document.querySelector("#financeClassroomsChart"), {
                chart: { type: 'bar', height: 280, stacked: true, toolbar: { show: false } },
                series: [
                    { name: @js(__('Sudah Dibayar (Rp)')), data: financeClasses.map(c => c.paid) },
                    { name: @js(__('Tunggakan (Rp)')), data: financeClasses.map(c => c.unpaid) }
                ],
                xaxis: { categories: financeClasses.map(c => c.classroom), labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [successColor, dangerColor],
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();

            const bkClasses = @json($bk['by_classroom']);
            new ApexCharts(document.querySelector("#bkRadarChart"), {
                chart: { type: 'radar', height: 250, toolbar: { show: false } },
                series: [
                    { name: @js(__('Poin Pelanggaran')), data: bkClasses.map(b => b.penalty_points) },
                    { name: @js(__('Poin Penghargaan')), data: bkClasses.map(b => b.reward_points) }
                ],
                labels: bkClasses.map(b => b.classroom),
                colors: [dangerColor, successColor],
                xaxis: { labels: { style: { colors: textColor } } },
                legend: { position: 'bottom', labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();

            const teachersData = @json($teachers['performance']);
            new ApexCharts(document.querySelector("#teacherPerformanceChart"), {
                chart: { type: 'bar', height: 250, toolbar: { show: false } },
                series: [
                    { name: @js(__('Jurnal Ditulis')), data: teachersData.map(t => t.total_journals) },
                    { name: @js(__('Sudah Direview')), data: teachersData.map(t => t.reviewed_journals) }
                ],
                xaxis: { categories: teachersData.map(t => t.name), labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [secondarySeriesColor, accentColor],
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();

            const spmbGrowthData = @json($spmb['growth']);
            new ApexCharts(document.querySelector("#spmbGrowthChart"), {
                chart: { type: 'area', height: 250, toolbar: { show: false } },
                series: [{ name: @js(__('Pendaftar Baru')), data: spmbGrowthData.map(s => s.total) }],
                xaxis: { categories: spmbGrowthData.map(s => s.year || '2026'), labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: [accentColor],
                stroke: { curve: 'smooth', width: 2.5 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0.02 } },
                grid: { borderColor: gridColor },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            }).render();
        });
    </script>
</x-app-layout>
