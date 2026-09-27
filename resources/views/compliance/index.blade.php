<x-app-layout>
    <x-slot name="title">Compliance & Akreditasi Dashboard</x-slot>

    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight flex items-center gap-2">
                    <span></span> Audit & Kepatuhan Akreditasi
                </h2>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    Pusat kendali persiapan akreditasi sekolah {{ school_name() }} terintegrasi data riil sistem.
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
        <!-- Alert Success/Error -->
        @if(session('success'))
        <div class="p-4 text-sm text-emerald-800 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50" role="alert">
            <span class="font-bold">Berhasil!</span> {{ session('success') }}
        </div>
        @endif

        <!-- Submenu Navigation -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-2 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex flex-wrap gap-1">
            <a href="{{ route('compliance.index') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all bg-primary-600 text-white shadow-md shadow-primary-500/20">
                 Dashboard Kepatuhan
            </a>
            <a href="{{ route('compliance.checklist') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Checklist Instrumen
            </a>
            <a href="{{ route('compliance.documents') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Repositori Bukti Fisik
            </a>
            <a href="{{ route('compliance.timeline') }}" class="px-4 py-2.5 text-xs font-black uppercase tracking-wider rounded-xl transition-all text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700/50 hover:text-gray-800 dark:hover:text-white">
                 Timeline Visitasi
            </a>
        </div>

        <!-- Top Section: Gauge & Summaries -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Semi-circular Gauge Chart -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col items-center justify-center min-h-[340px]">
                <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-2 text-center">Skor Kesiapan Akreditasi</h3>
                <div id="readiness-gauge" class="w-full"></div>
                <div class="text-center mt-2">
                    @php
                        $score = $report['overall_score'];
                        $predicate = 'BELUM SIAP';
                        $colorClass = 'text-red-500';
                        if ($score >= 91) {
                            $predicate = 'AKREDITASI A (UNGGUL)';
                            $colorClass = 'text-emerald-500';
                        } elseif ($score >= 81) {
                            $predicate = 'AKREDITASI B (BAIK)';
                            $colorClass = 'text-blue-500';
                        } elseif ($score >= 71) {
                            $predicate = 'AKREDITASI C (CUKUP)';
                            $colorClass = 'text-amber-500';
                        }
                    @endphp
                    <span class="text-xs font-black uppercase tracking-widest {{ $colorClass }} px-3 py-1 bg-gray-100 dark:bg-gray-900 rounded-full">
                        {{ $predicate }}
                    </span>
                    <p class="text-[10px] text-gray-400 mt-2 max-w-[240px] mx-auto">
                        Skor agregat tertimbang dihitung otomatis dari 10 indikator riil SNP.
                    </p>
                </div>
            </div>

            <!-- Key Summary widgets -->
            <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Card 1: Dokumen Pendukung -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="p-3 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-indigo-500 bg-indigo-50 dark:bg-indigo-950/40 px-2 py-0.5 rounded">Bukti Fisik</span>
                    </div>
                    <div class="mt-4">
                        <h4 class="text-2xl font-black text-gray-800 dark:text-white">{{ $documentsCount }}</h4>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Dokumen terunggah dalam sistem.</p>
                    </div>
                    <a href="{{ route('compliance.documents') }}" class="mt-4 inline-flex items-center text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:underline">
                        Buka Repositori Dokumen &rarr;
                    </a>
                </div>

                <!-- Card 2: Progres Timeline -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-between">
                    <div class="flex justify-between items-start">
                        <div class="p-3 bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                            </svg>
                        </div>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-500 bg-amber-50 dark:bg-amber-950/40 px-2 py-0.5 rounded">Milestones</span>
                    </div>
                    <div class="mt-4">
                        <div class="flex justify-between items-end">
                            <h4 class="text-2xl font-black text-gray-800 dark:text-white">{{ $milestonesProgress }}%</h4>
                            <span class="text-xs text-gray-400">{{ $milestones->where('status', 'completed')->count() }} / {{ $milestones->count() }} Tugas</span>
                        </div>
                        <div class="w-full bg-gray-150 dark:bg-gray-900 h-2 rounded-full overflow-hidden mt-2">
                            <div class="h-full bg-amber-500 transition-all duration-500" style="width: {{ $milestonesProgress }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">Penyelesaian milestones visitasi.</p>
                    </div>
                    <a href="{{ route('compliance.timeline') }}" class="mt-4 inline-flex items-center text-xs font-bold text-amber-600 dark:text-amber-400 hover:underline">
                        Lihat Agenda Persiapan &rarr;
                    </a>
                </div>

                <!-- Card 3: Tahun Akademik Aktif -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-md flex items-center space-x-4">
                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M12 21v-8.25"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tahun Akademik</p>
                        <h4 class="text-sm font-extrabold text-gray-800 dark:text-white mt-1">{{ $activeYear ? $activeYear->year . ' (' . ucfirst($activeYear->semester) . ')' : 'Tidak Aktif' }}</h4>
                    </div>
                </div>

                <!-- Card 4: Status Target Visitasi -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-5 rounded-2xl border border-gray-200/50 dark:border-gray-700/50 shadow-md flex items-center space-x-4">
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0-10.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.57-.598-3.75h-.152c-3.196 0-6.1-1.249-8.25-3.286Zm0 13.036h.008v.008H12v-.008Z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Status Visitasi</p>
                        <h4 class="text-sm font-extrabold text-gray-800 dark:text-white mt-1">SIAP AUDIT MANDIRI</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Breakdown Grid -->
        <div>
            <h3 class="text-lg font-black text-gray-800 dark:text-white mb-4 flex items-center gap-2">
                <span></span> Detail 10 Indikator Akreditasi
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach($report['metrics'] as $key => $metric)
                @php
                    $badgeClass = 'bg-red-50 text-red-700 dark:bg-red-950/30 dark:text-red-400 border border-red-200 dark:border-red-900/50';
                    $badgeText = 'BELUM TERPENUHI';
                    $progressColor = 'bg-rose-500';
                    
                    if ($metric['status'] === 'terpenuhi') {
                        $badgeClass = 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/50';
                        $badgeText = 'TERPENUHI';
                        $progressColor = 'bg-emerald-500';
                    } elseif ($metric['status'] === 'perlu_perbaikan') {
                        $badgeClass = 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400 border border-amber-200 dark:border-amber-900/50';
                        $badgeText = 'PERLU PERBAIKAN';
                        $progressColor = 'bg-amber-500';
                    }
                @endphp
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-lg flex flex-col justify-between transform hover:scale-[1.01] transition-all duration-300">
                    <div>
                        <div class="flex justify-between items-start gap-2">
                            <div>
                                <h4 class="font-extrabold text-sm text-gray-800 dark:text-white">{{ $metric['label'] }}</h4>
                                <span class="inline-flex px-2 py-0.5 text-[9px] font-black rounded-full uppercase tracking-wider {{ $badgeClass }} mt-1">
                                    {{ $badgeText }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span class="text-lg font-black text-gray-900 dark:text-white leading-none">{{ $metric['value'] }}</span>
                                <div class="text-[10px] text-gray-400 mt-1">Skor Kepatuhan: {{ number_format($metric['score'], 0) }}%</div>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-4 font-normal">
                            {{ $metric['desc'] }}
                        </p>
                    </div>

                    <div class="mt-6 space-y-1.5 border-t border-gray-100 dark:border-gray-700/50 pt-4">
                        <div class="flex justify-between text-[10px] text-gray-400 font-bold">
                            <span>Status Pemenuhan</span>
                            <span>{{ number_format($metric['score'], 0) }}%</span>
                        </div>
                        <div class="w-full bg-gray-100 dark:bg-gray-900 h-2 rounded-full overflow-hidden">
                            <div class="h-full {{ $progressColor }} rounded-full transition-all duration-500" style="width: {{ $metric['score'] }}%"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- ApexCharts JS -->
    @push('scripts')
    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var options = {
                chart: {
                    type: 'radialBar',
                    height: 260,
                    sparkline: {
                        enabled: true
                    }
                },
                series: [{{ $report['overall_score'] }}],
                colors: [function({ value }) {
                    if (value >= 91) {
                        return '#10b981'; // Emerald
                    } else if (value >= 81) {
                        return '#3b82f6'; // Blue
                    } else if (value >= 71) {
                        return '#f59e0b'; // Amber
                    } else {
                        return '#ef4444'; // Red
                    }
                }],
                plotOptions: {
                    radialBar: {
                        startAngle: -90,
                        endAngle: 90,
                        track: {
                            background: '#e2e8f0',
                            strokeWidth: '97%',
                            margin: 5,
                            dropShadow: {
                                enabled: false
                            }
                        },
                        dataLabels: {
                            name: {
                                show: false
                            },
                            value: {
                                offsetY: -2,
                                fontSize: '32px',
                                fontWeight: '900',
                                color: '#1f2937',
                                formatter: function (val) {
                                    return val + "%";
                                }
                            }
                        }
                    }
                },
                grid: {
                    padding: {
                        top: -10
                    }
                },
                stroke: {
                    lineCap: 'round'
                }
            };

            // Switch text color in gauge if dark mode is active
            if (document.documentElement.classList.contains('dark')) {
                options.plotOptions.radialBar.dataLabels.value.color = '#ffffff';
                options.plotOptions.radialBar.track.background = '#1e293b';
            }

            var chart = new ApexCharts(document.querySelector("#readiness-gauge"), options);
            chart.render();
        });
    </script>
    @endpush
</x-app-layout>
