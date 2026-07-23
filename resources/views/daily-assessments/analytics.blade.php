<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-gray-800 leading-tight tracking-tight">
            {{ __('Analitik Penilaian Harian — Kelas ') }}{{ $classroom->name }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Filter & Selection Card -->
            <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex flex-col md:flex-row justify-between items-end gap-4">
                <div class="w-full md:w-1/2">
                    <form method="GET" action="{{ route('daily-assessments.analytics', $classroom->id) }}">
                        <label for="subject_id" class="block font-medium text-sm text-gray-700 mb-1">Pilih Mata Pelajaran</label>
                        <select id="subject_id" name="subject_id" class="block w-full rounded-md shadow-sm border-gray-300 focus:border-primary-300 focus:ring focus:ring-primary-200 focus:ring-opacity-50" onchange="this.form.submit()">
                            @foreach($subjects as $sbj)
                                <option value="{{ $sbj->id }}" {{ $selectedSubjectId == $sbj->id ? 'selected' : '' }}>{{ $sbj->name }}</option>
                            @endforeach
                        </select>
                    </form>
                </div>
                <a href="{{ route('daily-assessments.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-xl font-semibold text-xs text-gray-700 uppercase tracking-widest bg-white hover:bg-gray-50 shadow-sm transition">
                    Kembali ke Daftar
                </a>
            </div>

            @if($analytics['has_data'])
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold uppercase tracking-wider">Rata-rata Kelas</span>
                            <span class="text-3xl font-black text-gray-800">{{ $analytics['average_score'] }}</span>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold uppercase tracking-wider">Persentase Tuntas KKM</span>
                            <span class="text-3xl font-black text-gray-800">{{ $analytics['pass_rate'] }}%</span>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm flex items-center justify-between">
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold uppercase tracking-wider">Materi Terendah</span>
                            <span class="text-lg font-bold text-red-600 truncate max-w-[180px] block" title="{{ $analytics['lowest_assessment']['title'] ?? '-' }}">
                                {{ $analytics['lowest_assessment']['title'] ?? '-' }}
                            </span>
                            <span class="text-xs text-gray-400">Rata-rata: {{ $analytics['lowest_assessment']['average'] ?? 0 }}</span>
                        </div>
                        <div class="p-3 bg-red-50 text-red-600 rounded-xl">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Charts Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Histogram Card -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
                        <h3 class="font-bold text-gray-700 text-lg border-b border-gray-100 pb-2">Distribusi Nilai Siswa</h3>
                        <div id="distribution-chart" class="h-80"></div>
                    </div>

                    <!-- Line Trend Card -->
                    <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm space-y-4">
                        <h3 class="font-bold text-gray-700 text-lg border-b border-gray-100 pb-2">Tren Evaluasi Kelas</h3>
                        <div id="trend-chart" class="h-80"></div>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 border-2 border-dashed border-gray-200 p-16 rounded-2xl text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10a2 2 0 01-2 2h-2a2 2 0 01-2-2zm4 5V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <h3 class="mt-4 font-bold text-gray-700 text-lg">Data Analitik Kosong</h3>
                    <p class="text-sm text-gray-400 mt-1">Belum ada siswa yang dinilai untuk mata pelajaran ini di kelas {{ $classroom->name }}.</p>
                </div>
            @endif

        </div>
    </div>

    @if($analytics['has_data'])
        <!-- ApexCharts JS CDN -->
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Distribution Histogram Options
                const distData = @json($analytics['distribution']);
                const distOptions = {
                    chart: {
                        type: 'bar',
                        height: 300,
                        toolbar: { show: false }
                    },
                    series: [{
                        name: 'Jumlah Siswa',
                        data: distData
                    }],
                    xaxis: {
                        categories: ['<60', '60-69', '70-79', '80-89', '90-100']
                    },
                    colors: ['#3b82f6'],
                    plotOptions: {
                        bar: {
                            borderRadius: 6,
                            dataLabels: { position: 'top' }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        offsetY: -20,
                        style: { colors: ['#374151'] }
                    }
                };
                new ApexCharts(document.querySelector("#distribution-chart"), distOptions).render();

                // 2. Trend Line Chart Options
                const trendData = @json($analytics['assessments_trend']);
                const trendSeries = trendData.map(item => item.average);
                const trendLabels = trendData.map(item => item.title);

                const trendOptions = {
                    chart: {
                        type: 'line',
                        height: 300,
                        toolbar: { show: false }
                    },
                    series: [{
                        name: 'Rata-rata Kelas',
                        data: trendSeries
                    }],
                    xaxis: {
                        categories: trendLabels
                    },
                    colors: ['#8b5cf6'],
                    stroke: {
                        curve: 'smooth',
                        width: 4
                    },
                    markers: {
                        size: 6
                    },
                    yaxis: {
                        min: 0,
                        max: 100
                    }
                };
                new ApexCharts(document.querySelector("#trend-chart"), trendOptions).render();
            });
        </script>
    @endif
</x-app-layout>
