<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    </div>
                    {{ __('Heatmap Matriks Kehadiran') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Peta Panas Kepadatan Ketidakhadiran per Kelas</p>
            </div>
        </div>
    </x-slot>

    <x-vendor.script lib="apexcharts" />

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                <div class="mb-4">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Heatmap Kehadiran Harian</h4>
                    <p class="text-xs text-gray-400 font-bold">Semakin gelap warna menunjukkan tingkat ketidakhadiran (S/I/A) yang lebih tinggi</p>
                </div>
                <div id="attendanceHeatmapChart" class="w-full h-[500px]"></div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#475569';

            // Generate Mock Data for Heatmap since it's UI demonstration
            function generateData(count, yrange) {
                let i = 0;
                let series = [];
                while (i < count) {
                    let x = 'Day ' + (i + 1).toString();
                    let y = Math.floor(Math.random() * (yrange.max - yrange.min + 1)) + yrange.min;
                    series.push({ x: x, y: y });
                    i++;
                }
                return series;
            }

            const options = {
                chart: { type: 'heatmap', height: 500, toolbar: { show: false } },
                series: [
                    { name: 'X-IPA 1', data: generateData(14, { min: 0, max: 15 }) },
                    { name: 'X-IPA 2', data: generateData(14, { min: 0, max: 10 }) },
                    { name: 'XI-IPA 1', data: generateData(14, { min: 0, max: 20 }) },
                    { name: 'XI-IPS 1', data: generateData(14, { min: 0, max: 30 }) },
                    { name: 'XII-IPA 1', data: generateData(14, { min: 0, max: 5 }) },
                    { name: 'XII-IPS 1', data: generateData(14, { min: 0, max: 25 }) }
                ],
                xaxis: { labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                plotOptions: {
                    heatmap: {
                        shadeIntensity: 0.5,
                        radius: 8,
                        useFillColorAsStroke: false,
                        colorScale: {
                            ranges: [
                                { from: 0, to: 5, name: 'Sangat Baik (0-5%)', color: '#10b981' },
                                { from: 6, to: 15, name: 'Waspada (6-15%)', color: '#f59e0b' },
                                { from: 16, to: 100, name: 'Kritis (>15%)', color: '#ef4444' }
                            ]
                        }
                    }
                },
                dataLabels: { enabled: false },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#attendanceHeatmapChart"), options).render();
        });
    </script>
</x-app-layout>
