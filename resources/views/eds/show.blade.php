<x-app-layout>
    <x-slot name="title">Dashboard EDS: {{ $period->name }}</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Raport Mutu (EDS)', 'url' => route('eds.index')],
            ['label' => 'Dashboard Periode', 'url' => '#']
        ]" />
    </x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div class="flex items-center space-x-3">
                <a href="{{ route('eds.index') }}" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-slate-700/50 text-slate-650 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-750 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"></path></svg>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight">
                        Dashboard Analitik EDS
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $period->name }} — Tahun Akademik {{ $period->academicYear->year }}</p>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <!-- Gap Analysis Link -->
                <a href="{{ route('eds.gap-analysis', $period->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-amber-500/10 border border-amber-400/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Prioritas Gap Analysis
                </a>

                <!-- PDF Export Link -->
                <a href="{{ route('eds.export-pdf', $period->id) }}" class="inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-rose-600/15 border border-rose-500/20">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Cetak Laporan PDF
                </a>

                <!-- Complete Period Button -->
                @can('manage', $period)
                    @if($period->status !== 'completed')
                    <form action="{{ route('eds.complete', $period->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan periode evaluasi ini secara formal? Setelah diselesaikan, penilaian tidak dapat diubah lagi.')">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-emerald-600/15 border border-emerald-500/20">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Selesaikan EDS
                        </button>
                    </form>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Toast Alerts -->
        @if(session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50" role="alert">
            <span class="font-bold">Berhasil!</span> {{ session('success') }}
        </div>
        @endif

        <!-- Grid 1: Spider Chart & overall KPIs -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Radar Chart Card -->
            <div class="lg:col-span-2 bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border border-gray-250/50 dark:border-gray-700/50 rounded-3xl p-6 shadow-xl flex flex-col justify-between">
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white leading-tight">Radar Capaian Mutu</h3>
                    <p class="text-xs text-gray-400 mt-1">Visualisasi grafik laba-laba kekuatan dan kelemahan mutu berdasarkan 8 Standar Nasional Pendidikan.</p>
                </div>
                <div class="flex justify-center items-center mt-4">
                    <div id="eds-radar-chart" class="w-full max-w-lg"></div>
                </div>
            </div>

            <!-- Aggregated Score KPI Card -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border border-gray-250/50 dark:border-gray-700/50 rounded-3xl p-6 shadow-xl flex flex-col justify-between items-center text-center relative overflow-hidden">
                <div class="w-full">
                    <div class="flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status Evaluasi</span>
                        @if($period->status === 'draft')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-gray-150 text-gray-700 dark:bg-gray-900 dark:text-gray-400 border border-gray-200 dark:border-gray-750">DRAFT</span>
                        @elseif($period->status === 'in_progress')
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-indigo-50 text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-900/50 animate-pulse">SEDANG BERJALAN</span>
                        @else
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50">SELESAI FORMAL</span>
                        @endif
                    </div>
                </div>

                <!-- Circular Score Indicator -->
                <div class="my-6 relative flex items-center justify-center">
                    <!-- Glowing Back Effect -->
                    <div class="absolute w-36 h-36 rounded-full bg-primary-500/10 dark:bg-primary-500/5 blur-xl"></div>
                    
                    <!-- Circle layout -->
                    <div class="w-40 h-40 rounded-full border-8 border-gray-100 dark:border-gray-700/50 flex flex-col items-center justify-center relative bg-white/50 dark:bg-gray-850/50 backdrop-blur-md">
                        <span class="text-xs text-gray-400 font-extrabold uppercase tracking-widest">Skor Mutu</span>
                        <h4 class="text-4xl font-extrabold text-gray-900 dark:text-white mt-1 leading-none">{{ number_format($scoresData['overall_average'], 2) }}</h4>
                        <span class="text-xs text-gray-400 mt-1.5">Skala 1 - 4</span>
                    </div>
                </div>

                <!-- Predicate / Label -->
                <div class="w-full space-y-3">
                    @php
                        $roundedScore = round($scoresData['overall_average']);
                        $pillClass = '';
                        $predikat = 'Belum Dievaluasi';
                        $keterangan = 'Mulailah mengisi penilaian standar indikator di bawah.';
                        
                        if ($scoresData['assessed_indicators'] > 0) {
                            if ($scoresData['overall_average'] >= 3.5) {
                                $pillClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400 border-emerald-200 dark:border-emerald-900/50';
                                $predikat = 'UNGGUL';
                                $keterangan = 'Sekolah secara konsisten melampaui seluruh kriteria standar nasional secara terukur.';
                            } elseif ($scoresData['overall_average'] >= 3.0) {
                                $pillClass = 'bg-blue-100 text-blue-800 dark:bg-blue-950/50 dark:text-blue-400 border-blue-200 dark:border-blue-900/50';
                                $predikat = 'BAIK';
                                $keterangan = 'Sekolah telah memenuhi seluruh kriteria standar nasional pendidikan dengan sangat memadai.';
                            } elseif ($scoresData['overall_average'] >= 2.0) {
                                $pillClass = 'bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400 border-amber-200 dark:border-amber-900/50';
                                $predikat = 'CUKUP';
                                $keterangan = 'Sekolah memenuhi standar minimum dengan catatan prioritas perbaikan pada beberapa aspek.';
                            } else {
                                $pillClass = 'bg-red-100 text-red-800 dark:bg-red-950/50 dark:text-red-400 border-red-200 dark:border-red-900/50';
                                $predikat = 'KURANG';
                                $keterangan = 'Sekolah belum memenuhi kriteria standar minimum. Membutuhkan tindakan peningkatan mutu segera.';
                            }
                        }
                    @endphp
                    <span class="inline-flex px-4 py-1.5 rounded-full text-sm font-extrabold uppercase border {{ $pillClass ?: 'bg-gray-100 text-gray-500 border-gray-200' }}">
                        {{ $predikat }}
                    </span>
                    <p class="text-xs text-gray-400 leading-relaxed px-4">{{ $keterangan }}</p>
                </div>

                <!-- Aggregated Indicator Progress -->
                <div class="w-full pt-4 border-t border-gray-150 dark:border-gray-700/60 mt-4 text-xs flex justify-between font-bold text-gray-450 dark:text-gray-400">
                    <span>Progres Dinilai: {{ $scoresData['assessed_indicators'] }} / {{ $scoresData['total_indicators'] }}</span>
                    <span>{{ $scoresData['overall_progress'] }}%</span>
                </div>
            </div>
        </div>

        <!-- Section:Capian 8 SNP -->
        <div class="space-y-4">
            <div>
                <h3 class="text-lg font-black text-gray-800 dark:text-white leading-tight">Pencapaian 8 Standar Nasional Pendidikan (SNP)</h3>
                <p class="text-xs text-gray-500 dark:text-gray-450 mt-1">Berikut adalah tingkat ketercapaian detail per masing-masing standar pendidikan nasional.</p>
            </div>

            <!-- Standards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                @foreach($scoresData['standards'] as $code => $data)
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-250/50 dark:border-gray-700/50 shadow-xl overflow-hidden flex flex-col justify-between transform hover:scale-[1.01] transition-all duration-300">
                    
                    <div class="p-6">
                        <!-- Top Header -->
                        <div class="flex justify-between items-start">
                            <span class="px-2.5 py-0.5 rounded-lg text-[9px] font-black tracking-wider bg-gray-100 dark:bg-gray-900 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800 uppercase">{{ $code }}</span>
                            
                            @php
                                $roundedStdScore = round($data['average']);
                                $stdColorClass = 'text-red-500 dark:text-red-400';
                                $stdPillClass = 'bg-red-550';
                                
                                if ($data['assessed_count'] > 0) {
                                    if ($data['average'] >= 3.5) {
                                        $stdColorClass = 'text-emerald-500 dark:text-emerald-400';
                                        $stdPillClass = 'bg-emerald-500';
                                    } elseif ($data['average'] >= 3.0) {
                                        $stdColorClass = 'text-blue-500 dark:text-blue-400';
                                        $stdPillClass = 'bg-blue-500';
                                    } elseif ($data['average'] >= 2.0) {
                                        $stdColorClass = 'text-amber-500 dark:text-amber-400';
                                        $stdPillClass = 'bg-amber-500';
                                    }
                                } else {
                                    $stdColorClass = 'text-gray-400';
                                    $stdPillClass = 'bg-gray-400';
                                }
                            @endphp
                            
                            <div class="flex items-baseline space-x-0.5">
                                <span class="text-lg font-black {{ $stdColorClass }}">{{ number_format($data['average'], 2) }}</span>
                                <span class="text-[10px] text-gray-400">/4</span>
                            </div>
                        </div>

                        <!-- Content -->
                        <h4 class="text-sm font-black text-gray-900 dark:text-white mt-3 leading-snug line-clamp-2 h-10">
                            {{ $data['standard']->name }}
                        </h4>
                        <p class="text-[10px] text-gray-400 mt-1 line-clamp-2 h-7 leading-relaxed">
                            {{ $data['standard']->description }}
                        </p>

                        <!-- Standard Progress -->
                        <div class="mt-6 space-y-1.5">
                            <div class="flex justify-between items-center text-[10px] font-bold text-gray-400">
                                <span>Indikator Dinilai</span>
                                <span>{{ $data['progress_percentage'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 dark:bg-gray-900 h-1.5 rounded-full overflow-hidden">
                                <div class="h-full {{ $stdPillClass }} transition-all duration-300 rounded-full" style="width: {{ $data['progress_percentage'] }}%"></div>
                            </div>
                            <div class="text-[9px] text-gray-400 text-right font-semibold">
                                {{ $data['assessed_count'] }} dari {{ $data['total_count'] }} indikator
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="px-6 pb-6 pt-2 bg-gray-50/30 dark:bg-gray-900/10">
                        @if($period->status === 'completed')
                            <a href="{{ route('eds.assess', [$period->id, $data['standard']->id]) }}" class="w-full inline-flex justify-center items-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 rounded-xl py-2 text-xs font-bold transition-all border border-gray-200/50 dark:border-gray-600/50">
                                Lihat Nilai
                                <svg class="w-4 h-4 ml-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                        @else
                            @can('assess', $period)
                            <a href="{{ route('eds.assess', [$period->id, $data['standard']->id]) }}" class="w-full inline-flex justify-center items-center bg-primary-600 hover:bg-primary-700 text-white rounded-xl py-2 text-xs font-bold transition-all shadow-md shadow-primary-500/10">
                                Evaluasi Standar
                                <svg class="w-4 h-4 ml-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </a>
                            @else
                            <a href="{{ route('eds.assess', [$period->id, $data['standard']->id]) }}" class="w-full inline-flex justify-center items-center bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-650 text-gray-700 dark:text-gray-200 rounded-xl py-2 text-xs font-bold transition-all border border-gray-200/50 dark:border-gray-600/50">
                                Lihat Nilai
                                <svg class="w-4 h-4 ml-1 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            @endcan
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Script loading ApexCharts and drawing visual widgets -->
    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Check theme mode
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#334155';
            const radarBg = isDark ? '#1e293b' : '#ffffff';
            const gridColor = isDark ? '#334155' : '#f1f5f9';

            const categories = {!! json_encode($chartData['categories']) !!};
            const seriesData = {!! json_encode(array_column($chartData['series'], 'data')[0]) !!};

            // Format standard short names for visual simplicity on mobile
            const shortCategories = categories.map(cat => {
                if (cat.length > 20) {
                    return cat.substring(0, 18) + '...';
                }
                return cat;
            });

            const options = {
                chart: {
                    type: 'radar',
                    height: 380,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                series: [{
                    name: 'Skor Ketercapaian',
                    data: seriesData
                }],
                labels: shortCategories,
                colors: ['#4f46e5'],
                fill: {
                    opacity: 0.25,
                    colors: ['#4f46e5']
                },
                stroke: {
                    show: true,
                    width: 2.5,
                    colors: ['#4f46e5'],
                    dashArray: 0
                },
                markers: {
                    size: 4,
                    hover: { size: 6 },
                    colors: ['#4f46e5']
                },
                legend: { show: false },
                yaxis: {
                    show: true,
                    min: 0,
                    max: 4,
                    tickAmount: 4,
                    labels: {
                        formatter: function(val) {
                            return val.toFixed(1);
                        },
                        style: {
                            colors: textColor
                        }
                    }
                },
                xaxis: {
                    labels: {
                        style: {
                            colors: [textColor, textColor, textColor, textColor, textColor, textColor, textColor, textColor],
                            fontSize: '10px',
                            fontWeight: '600'
                        }
                    }
                },
                plotOptions: {
                    radar: {
                        size: 130,
                        polygons: {
                            strokeColors: gridColor,
                            connectorColors: gridColor,
                            fill: {
                                colors: [isDark ? '#0f172a' : '#fafafa', isDark ? '#1e293b' : '#ffffff']
                            }
                        }
                    }
                },
                tooltip: {
                    enabled: true,
                    theme: isDark ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            return val.toFixed(2) + ' / 4.00';
                        }
                    }
                }
            };

            const chart = new ApexCharts(document.querySelector("#eds-radar-chart"), options);
            chart.render();
        });
    </script>
</x-app-layout>
