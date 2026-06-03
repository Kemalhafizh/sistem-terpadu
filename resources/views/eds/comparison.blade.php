<x-app-layout>
    <x-slot name="title">Perbandingan Periode EDS</x-slot>

    <x-slot name="breadcrumbs">
        <x-breadcrumb :items="[
            ['label' => 'Dashboard', 'url' => route('dashboard')],
            ['label' => 'Raport Mutu (EDS)', 'url' => route('eds.index')],
            ['label' => 'Perbandingan Periode', 'url' => '#']
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
                        Perbandingan Mutu Antar Periode
                    </h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Bandingkan capaian mutu sekolah berdasarkan 8 Standar Nasional Pendidikan (SNP) antara dua periode penilaian.</p>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Form Pemilihan Periode -->
        <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-250/50 dark:border-gray-700/50 shadow-xl p-6">
            <form method="GET" action="{{ route('eds.comparison') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                    <!-- Dropdown Periode Sebelumnya (Baseline) -->
                    <div class="lg:col-span-2">
                        <label for="previous_period_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Periode Acuan (Baseline)</label>
                        <select 
                            id="previous_period_id" 
                            name="previous_period_id" 
                            required
                            class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                        >
                            <option value="">-- Pilih Periode Acuan --</option>
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" @selected(request('previous_period_id') == $p->id)>
                                    {{ $p->name }} ({{ $p->academicYear->year }} - {{ ucfirst($p->academicYear->semester) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Icon / Pemisah -->
                    <div class="hidden lg:flex items-center justify-center pb-3 text-gray-450 dark:text-gray-550">
                        <svg class="w-6 h-6 transform rotate-90 lg:rotate-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                        </svg>
                    </div>

                    <!-- Dropdown Periode Saat Ini (Pembanding) -->
                    <div class="lg:col-span-2">
                        <label for="current_period_id" class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Periode Pembanding (Target)</label>
                        <select 
                            id="current_period_id" 
                            name="current_period_id" 
                            required
                            class="w-full bg-gray-50 dark:bg-gray-950 border border-gray-250 dark:border-gray-800 rounded-xl px-4 py-2.5 text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:outline-none"
                        >
                            <option value="">-- Pilih Periode Pembanding --</option>
                            @foreach($periods as $p)
                                <option value="{{ $p->id }}" @selected(request('current_period_id') == $p->id)>
                                    {{ $p->name }} ({{ $p->academicYear->year }} - {{ ucfirst($p->academicYear->semester) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-2">
                    @if(request()->has('current_period_id'))
                        <a href="{{ route('eds.comparison') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-bold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-all">
                            Reset
                        </a>
                    @endif
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-xs font-extrabold uppercase tracking-wider shadow-lg shadow-primary-500/20 transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                        Bandingkan Sekarang
                    </button>
                </div>
            </form>
        </div>

        @if($comparisonData)
            <!-- Panel Ringkasan Perbandingan KPI (Glassmorphic Cards) -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- baseline Score Card -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-250/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Acuan: {{ $comparisonData['previous_period_name'] }}</span>
                        <h4 class="text-xs text-gray-500 dark:text-gray-450 mt-0.5">Rata-rata Skor Mutu</h4>
                    </div>
                    <div class="mt-4 flex items-baseline space-x-1.5">
                        <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white leading-none">
                            {{ number_format($comparisonData['previous_overall_average'], 2) }}
                        </h3>
                        <span class="text-sm text-gray-400">/ 4.00</span>
                    </div>
                </div>

                <!-- target Score Card -->
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md p-6 rounded-3xl border border-gray-250/50 dark:border-gray-700/50 shadow-xl flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-bold text-primary-500 uppercase tracking-wider">Pembanding: {{ $comparisonData['current_period_name'] }}</span>
                        <h4 class="text-xs text-gray-500 dark:text-gray-450 mt-0.5">Rata-rata Skor Mutu</h4>
                    </div>
                    <div class="mt-4 flex items-baseline space-x-1.5">
                        <h3 class="text-3xl font-extrabold text-gray-800 dark:text-white leading-none">
                            {{ number_format($comparisonData['current_overall_average'], 2) }}
                        </h3>
                        <span class="text-sm text-gray-400">/ 4.00</span>
                    </div>
                </div>

                <!-- Delta Score Card -->
                @php
                    $delta = $comparisonData['overall_difference'];
                    $deltaColorClass = 'text-gray-500';
                    $deltaBgClass = 'bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800';
                    $deltaText = 'Tidak ada perubahan';
                    
                    if ($delta > 0) {
                        $deltaColorClass = 'text-emerald-600 dark:text-emerald-400';
                        $deltaBgClass = 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-250 dark:border-emerald-900/50';
                        $deltaText = 'Peningkatan Mutu';
                    } elseif ($delta < 0) {
                        $deltaColorClass = 'text-rose-600 dark:text-rose-400';
                        $deltaBgClass = 'bg-rose-50 dark:bg-rose-950/20 border-rose-250 dark:border-rose-900/50';
                        $deltaText = 'Penurunan Mutu';
                    }
                @endphp
                <div class="backdrop-blur-md p-6 rounded-3xl border shadow-xl flex flex-col justify-between {{ $deltaBgClass }}">
                    <div>
                        <span class="text-xs font-black uppercase tracking-wider {{ $deltaColorClass }}">{{ $deltaText }}</span>
                        <h4 class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Selisih Capaian Kumulatif</h4>
                    </div>
                    <div class="mt-4 flex items-center space-x-2">
                        <h3 class="text-3xl font-black leading-none {{ $deltaColorClass }}">
                            {{ $delta > 0 ? '+' : '' }}{{ number_format($delta, 2) }}
                        </h3>
                        <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest px-2 py-0.5 rounded-md bg-white/60 dark:bg-slate-800/60 border border-gray-200/50 dark:border-gray-700/50">DELTA</span>
                    </div>
                </div>
            </div>

            <!-- Visual Comparison: Line Chart -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md border border-gray-250/50 dark:border-gray-700/50 rounded-3xl p-6 shadow-xl">
                <div>
                    <h3 class="text-base font-black text-gray-900 dark:text-white leading-tight">Tren Perbandingan 8 SNP</h3>
                    <p class="text-xs text-gray-400 mt-1">Perbandingan tren skor di setiap Standar Nasional Pendidikan (SNP) antara dua periode evaluasi.</p>
                </div>
                <div class="mt-6">
                    <div id="eds-comparison-chart" class="w-full h-80"></div>
                </div>
            </div>

            <!-- Detailed Comparison Table -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-150/50 dark:border-gray-700/50">
                    <h3 class="text-sm font-black text-gray-900 dark:text-white leading-tight">Rincian Perbandingan per Standar</h3>
                    <p class="text-[10px] text-gray-400 mt-0.5">Detail perbandingan nilai rata-rata dan gap perubahan untuk setiap standar nasional pendidikan.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50 dark:bg-gray-900/50 border-b border-gray-200/50 dark:border-gray-700/50">
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-[15%]">Standar</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 w-[45%]">Nama Standar</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center w-[12%]">Acuan (Baseline)</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center w-[12%]">Pembanding (Target)</th>
                                <th class="px-6 py-4 text-xs font-bold uppercase tracking-wider text-gray-500 text-center w-[16%]">Perubahan (Delta)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-150/40 dark:divide-gray-700/40">
                            @foreach($comparisonData['standards'] as $code => $data)
                            <tr class="hover:bg-gray-50/20 dark:hover:bg-gray-800/10 transition-colors">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black bg-gray-100 dark:bg-gray-900 text-gray-650 dark:text-gray-350 border border-gray-200/50 dark:border-gray-850 uppercase">{{ $code }}</span>
                                </td>
                                <td class="px-6 py-4 text-xs font-bold text-gray-800 dark:text-gray-200">
                                    {{ $data['name'] }}
                                </td>
                                <td class="px-6 py-4 text-center font-semibold text-gray-600 dark:text-gray-400 text-sm">
                                    {{ number_format($data['previous_score'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-center font-bold text-gray-950 dark:text-white text-sm">
                                    {{ number_format($data['current_score'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    @php
                                        $diff = $data['difference'];
                                        if ($diff > 0) {
                                            $badgeText = '+' . number_format($diff, 2);
                                            $badgeClass = 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-400 border border-emerald-200/50 dark:border-emerald-900/50';
                                        } elseif ($diff < 0) {
                                            $badgeText = number_format($diff, 2);
                                            $badgeClass = 'bg-red-100 text-red-800 dark:bg-red-950/30 dark:text-red-400 border border-red-200/50 dark:border-red-900/50';
                                        } else {
                                            $badgeText = '0.00';
                                            $badgeClass = 'bg-gray-100 text-gray-600 dark:bg-gray-900 dark:text-gray-400 border border-gray-200/50 dark:border-gray-800';
                                        }
                                    @endphp
                                    <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-black {{ $badgeClass }}">
                                        {{ $badgeText }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Script loading ApexCharts and drawing the double line/area comparison chart -->
            <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
            <script>
                document.addEventListener("DOMContentLoaded", function () {
                    const isDark = document.documentElement.classList.contains('dark');
                    const textColor = isDark ? '#cbd5e1' : '#334155';
                    const gridColor = isDark ? '#334155' : '#f1f5f9';

                    const categories = {!! json_encode(array_values(collect($comparisonData['standards'])->map(fn($item, $key) => $key)->toArray())) !!};
                    const previousData = {!! json_encode(array_values(collect($comparisonData['standards'])->map(fn($item) => $item['previous_score'])->toArray())) !!};
                    const currentData = {!! json_encode(array_values(collect($comparisonData['standards'])->map(fn($item) => $item['current_score'])->toArray())) !!};

                    const options = {
                        chart: {
                            type: 'area',
                            height: 320,
                            toolbar: { show: false },
                            fontFamily: 'Inter, sans-serif'
                        },
                        series: [
                            {
                                name: "{!! $comparisonData['previous_period_name'] !!}",
                                data: previousData
                            },
                            {
                                name: "{!! $comparisonData['current_period_name'] !!}",
                                data: currentData
                            }
                        ],
                        xaxis: {
                            categories: categories,
                            labels: {
                                style: {
                                    colors: textColor,
                                    fontWeight: 700
                                }
                            }
                        },
                        yaxis: {
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
                        colors: ['#94a3b8', '#4f46e5'], // Neutral slate/gray vs Premium Indigo
                        fill: {
                            type: 'gradient',
                            gradient: {
                                shadeIntensity: 1,
                                opacityFrom: 0.3,
                                opacityTo: 0.05,
                                stops: [0, 90, 100]
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: [2, 3]
                        },
                        markers: {
                            size: 4,
                            hover: { size: 6 }
                        },
                        grid: {
                            borderColor: gridColor
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            labels: {
                                colors: textColor
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

                    const chart = new ApexCharts(document.querySelector("#eds-comparison-chart"), options);
                    chart.render();
                });
            </script>
        @else
            <!-- Empty state when no comparison has been made -->
            <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-md rounded-3xl border border-gray-200/50 dark:border-gray-700/50 shadow-xl p-12 text-center">
                <span class="text-5xl">📊</span>
                <h3 class="text-lg font-black text-gray-800 dark:text-white mt-4">Analisis Komparatif Mutu</h3>
                <p class="text-sm text-gray-400 dark:text-gray-550 mt-2 max-w-lg mx-auto">
                    Silakan pilih <strong>Periode Acuan (Baseline)</strong> dan <strong>Periode Pembanding (Target)</strong> pada form pilihan di atas, kemudian klik tombol <strong>Bandingkan Sekarang</strong> untuk memetakan deviasi mutu secara real-time.
                </p>
                <div class="mt-6 flex flex-wrap justify-center gap-4 text-xs font-semibold text-gray-400">
                    <span class="flex items-center gap-1">🛡️ Visualisasi Area Chart Tren 8 SNP</span>
                    <span class="flex items-center gap-1">📈 Perhitungan Delta Peningkatan</span>
                    <span class="flex items-center gap-1">⚡ Rincian Perbandingan Numerik</span>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
