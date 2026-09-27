<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <a href="{{ route('analytics.index') }}" class="text-sm font-bold text-gray-500 hover:text-primary-600 flex items-center mb-1 transition-colors">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Kembali ke Dashboard Utama
                </a>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight dark:text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-amber-500 text-white flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    {{ __('Analitik & Proyeksi Keuangan') }}
                </h2>
                <p class="text-xs text-gray-400 font-bold uppercase tracking-widest mt-1">Drill-Down Tren Pembayaran & Prediksi Pelunasan</p>
            </div>
        </div>
    </x-slot>

    <x-vendor.script lib="apexcharts" />

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Forecast Line Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-[2.5rem] p-8 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h4 class="text-lg font-black text-gray-900 dark:text-white mb-2">Proyeksi Collection Rate</h4>
                    <p class="text-xs text-gray-400 font-bold mb-6">Analisis Historis dan Proyeksi AI (Moving Average)</p>
                    <div id="financeForecastChart" class="w-full h-80"></div>
                </div>

                <div class="bg-amber-50 dark:bg-amber-900/20 rounded-[2.5rem] p-8 shadow-sm border border-amber-100 dark:border-amber-800/50 flex flex-col justify-center">
                    <div class="text-center">
                        <h4 class="text-xl font-black text-amber-800 dark:text-amber-400 mb-2">Total Outstanding Tunggakan</h4>
                        <h2 class="text-5xl font-black text-gray-900 dark:text-white my-4">Rp {{ number_format($finance['global']['unpaid'], 0, ',', '.') }}</h2>
                        <p class="text-sm text-gray-500 font-bold">Tingkat pelunasan saat ini berada di <span class="text-emerald-600 dark:text-emerald-400 font-black">{{ $finance['global']['paid_percentage'] }}%</span>.</p>
                    </div>
                    <div class="mt-8 flex justify-center gap-4">
                        <a href="{{ route('analytics.export.finance') }}" class="px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white rounded-xl text-sm font-bold shadow-sm transition-all flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            Download Laporan PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#cbd5e1' : '#475569';
            const gridColor = isDark ? '#334155' : '#e2e8f0';

            const forecastData = @json($forecast);
            const months = forecastData.map(d => d.month);
            const actual = forecastData.map(d => d.actual);
            const projected = forecastData.map(d => d.forecast);

            const options = {
                chart: { type: 'line', height: 320, toolbar: { show: false } },
                series: [
                    { name: 'Aktual (%)', data: actual },
                    { name: 'Proyeksi (%)', data: projected }
                ],
                xaxis: { categories: months, labels: { style: { colors: textColor } } },
                yaxis: { labels: { style: { colors: textColor } } },
                colors: ['#10b981', '#f59e0b'],
                stroke: { width: [3, 3], dashArray: [0, 5], curve: 'smooth' },
                grid: { borderColor: gridColor },
                legend: { labels: { colors: textColor } },
                tooltip: { theme: isDark ? 'dark' : 'light' }
            };
            new ApexCharts(document.querySelector("#financeForecastChart"), options).render();
        });
    </script>
</x-app-layout>
