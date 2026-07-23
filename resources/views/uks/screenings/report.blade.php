<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('uks.screenings.index') }}" class="hover:text-teal-600 transition-colors">
                    <span class="w-8 h-8 bg-teal-100 dark:bg-teal-950/50 text-teal-600 dark:text-teal-400 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                    </span>
                </a>
                <div>
                    <h2 class="font-extrabold text-2xl text-gray-800 dark:text-white leading-tight tracking-tight">
                        {{ __('Laporan Screening: ' . $screening->title) }}
                    </h2>
                    <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 mt-1 uppercase tracking-wider">SMA ISLAM SULTAN AGUNG 1 • ANALISA & PROFIL KESEHATAN</p>
                </div>
            </div>

            <a href="{{ route('uks.screenings.export-pdf', $screening->id) }}" class="inline-flex items-center px-4 py-2.5 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-black uppercase tracking-wider transition-all shadow-md shadow-teal-600/10">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Ekspor ke PDF
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            
            <!-- Stat Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <!-- Stat 1: Total Screened -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-teal-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Siswa Diperiksa</p>
                        <h3 class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $totalScreened }}</h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Jumlah siswa terdaftar</p>
                    </div>
                    <div class="w-14 h-14 bg-teal-50 dark:bg-teal-950/30 text-teal-600 dark:text-teal-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 2: Avg Height -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-blue-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Rata-rata Tinggi</p>
                        <h3 class="text-4xl font-extrabold text-blue-600 dark:text-blue-500">{{ $averageHeight }} <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">cm</span></h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Tinggi badan siswa</p>
                    </div>
                    <div class="w-14 h-14 bg-blue-50 dark:bg-blue-950/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                </div>

                <!-- Stat 3: Avg Weight -->
                <div class="bg-white dark:bg-gray-800/80 backdrop-blur-md rounded-[2rem] p-6 shadow-sm border border-gray-100 dark:border-gray-700/50 flex items-center justify-between group hover:border-indigo-500/50 transition-all duration-300">
                    <div class="space-y-2">
                        <p class="text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">Rata-rata Berat</p>
                        <h3 class="text-4xl font-extrabold text-indigo-600 dark:text-indigo-500">{{ $averageWeight }} <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">kg</span></h3>
                        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500">Berat badan siswa</p>
                    </div>
                    <div class="w-14 h-14 bg-indigo-50 dark:bg-indigo-950/30 text-indigo-600 dark:text-indigo-400 rounded-2xl flex items-center justify-center transition-transform group-hover:scale-110 duration-300">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Chart Section (Classroom Averages) -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h4 class="text-lg font-black text-gray-900 dark:text-white tracking-tight">Perbandingan Pertumbuhan Tinggi & Berat per Kelas</h4>
                        <p class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mt-1">Metrik Rata-rata per Rombel</p>
                    </div>
                </div>
                
                <div id="classroomGrowthChart" class="w-full h-80"></div>
            </div>

            <!-- Table of Results -->
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-6 sm:p-8 shadow-sm border border-gray-100 dark:border-gray-700/50 flex flex-col">
                <h4 class="text-lg font-black text-gray-900 dark:text-white flex items-center mb-6">
                    <span class="w-2 h-6 bg-teal-600 rounded-full mr-3"></span>
                    Rincian Hasil Pemeriksaan Individu
                </h4>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-gray-100 dark:border-gray-700/50 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-500">
                                <th class="pb-3">Siswa</th>
                                <th class="pb-3 text-center">Tinggi (cm)</th>
                                <th class="pb-3 text-center">Berat (kg)</th>
                                <th class="pb-3 text-center">Mata (V.L / V.R)</th>
                                <th class="pb-3 text-center">Gigi & Mulut</th>
                                <th class="pb-3">Temuan Klinis</th>
                                <th class="pb-3">Rekomendasi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-55/30 dark:divide-gray-700/30">
                            @forelse($results as $res)
                                <tr class="group hover:bg-gray-50/55 dark:hover:bg-gray-900/10 transition-all">
                                    <td class="py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-xl flex items-center justify-center shrink-0 font-extrabold text-sm mr-3">
                                                {{ substr($res->student->user->name ?? '-', 0, 1) }}
                                            </div>
                                            <div>
                                                <h5 class="text-sm font-extrabold text-gray-900 dark:text-white">
                                                    {{ $res->student->user->name ?? '-' }}
                                                </h5>
                                                <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold mt-0.5">
                                                    Kelas: {{ $res->student->classroom->name ?? '-' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 text-center text-xs font-bold text-gray-800 dark:text-gray-200">
                                        {{ $res->height_cm ?? '-' }}
                                    </td>
                                    <td class="py-4 text-center text-xs font-bold text-gray-800 dark:text-gray-200">
                                        {{ $res->weight_kg ?? '-' }}
                                    </td>
                                    <td class="py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $res->vision_left ?? '-' }} / {{ $res->vision_right ?? '-' }}
                                    </td>
                                    <td class="py-4 text-center text-xs font-semibold text-gray-600 dark:text-gray-400">
                                        {{ $res->dental_status ?? '-' }}
                                    </td>
                                    <td class="py-4 text-xs text-gray-650 dark:text-gray-450">
                                        {{ $res->findings ?? '-' }}
                                    </td>
                                    <td class="py-4 text-xs font-bold text-teal-600 dark:text-teal-400">
                                        {{ $res->recommendations ?? '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400 dark:text-gray-600 italic">Belum ada hasil screening tercatat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ApexCharts Scripts -->
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Group results by classroom
            const results = @json($results->map(function($r) {
                return [
                    'classroom' => $r->student->classroom->name ?? 'N/A',
                    'height' => (float) $r->height_cm,
                    'weight' => (float) $r->weight_kg
                ];
            }));

            const classMap = {};
            results.forEach(r => {
                if(!classMap[r.classroom]) {
                    classMap[r.classroom] = { heightSum: 0, weightSum: 0, count: 0 };
                }
                if(r.height) {
                    classMap[r.classroom].heightSum += r.height;
                    classMap[r.classroom].count++;
                }
                if(r.weight) {
                    classMap[r.classroom].weightSum += r.weight;
                }
            });

            const categories = Object.keys(classMap);
            const avgHeights = categories.map(c => classMap[c].count > 0 ? Math.round(classMap[c].heightSum / classMap[c].count * 10) / 10 : 0);
            const avgWeights = categories.map(c => classMap[c].count > 0 ? Math.round(classMap[c].weightSum / classMap[c].count * 10) / 10 : 0);

            const options = {
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'Outfit, sans-serif'
                },
                series: [{
                    name: 'Rata-rata Tinggi (cm)',
                    data: avgHeights
                }, {
                    name: 'Rata-rata Berat (kg)',
                    data: avgWeights
                }],
                colors: ['#0d9488', '#2563eb'],
                plotOptions: {
                    bar: {
                        horizontal: false,
                        columnWidth: '55%',
                        borderRadius: 8
                    },
                },
                dataLabels: { enabled: false },
                xaxis: {
                    categories: categories,
                    labels: {
                        style: { colors: '#9ca3af', fontWeight: 'bold' }
                    }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#9ca3af', fontWeight: 'bold' }
                    }
                },
                legend: {
                    position: 'top',
                    labels: { colors: '#9ca3af' }
                },
                grid: {
                    borderColor: '#374151',
                    strokeDashArray: 4
                },
                tooltip: { theme: 'dark' }
            };

            const chart = new ApexCharts(document.querySelector("#classroomGrowthChart"), options);
            chart.render();
        });
    </script>
    @endpush
</x-app-layout>
