<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="font-semibold text-xl text-slate-200 leading-tight">
                    <i class="fa-solid fa-chart-line text-indigo-400 mr-2"></i> Laporan: {{ $report->name }}
                </h2>
                <p class="text-xs text-slate-400 mt-1">
                    Sumber Data: <span class="text-indigo-300 font-semibold">{{ $sources[$report->config['source']]['label'] ?? $report->config['source'] }}</span>
                </p>
            </div>
            
            <div class="flex space-x-2">
                <a href="{{ route('admin.report-builder.export.excel', $report->uuid) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-md flex items-center">
                    <i class="fa-solid fa-file-excel mr-2"></i> Ekspor Excel
                </a>
                <a href="{{ route('admin.report-builder.export.pdf', $report->uuid) }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-medium rounded-lg transition-all duration-200 shadow-md flex items-center">
                    <i class="fa-solid fa-file-pdf mr-2"></i> Unduh PDF
                </a>
                <a href="{{ route('admin.report-builder.index') }}" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition-all duration-200 flex items-center">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Dashboard
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Description & Info Card -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Deskripsi Laporan</h3>
                    <p class="text-sm text-slate-200 mt-1">{{ $report->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>
                <div class="text-xs text-slate-400 bg-slate-900/50 border border-slate-700/50 rounded-xl px-4 py-3 space-y-1">
                    <div>Dibuat oleh: <span class="text-slate-200 font-medium">{{ $report->creator->name ?? 'N/A' }}</span></div>
                    <div>Dibuat pada: <span class="text-slate-200 font-medium">{{ $report->created_at->format('d F Y - H:i') }}</span></div>
                </div>
            </div>

            <!-- Dynamic Chart Visualization Panel (If Configured) -->
            @if(($report->config['chartType'] ?? 'table') !== 'table')
                <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl p-6 shadow-xl">
                    <h3 class="text-md font-semibold text-slate-200 mb-4 flex items-center">
                        <i class="fa-solid fa-chart-column mr-2 text-indigo-400"></i> Visualisasi Hasil Laporan
                    </h3>
                    
                    <div id="report-apex-chart" class="w-full h-80"></div>
                </div>
            @endif

            <!-- Full Dynamic Data Table -->
            <div class="bg-slate-800/50 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl overflow-hidden">
                <div class="px-6 py-5 border-b border-slate-700/50 flex justify-between items-center">
                    <h3 class="text-lg font-medium text-slate-200">
                        <i class="fa-solid fa-table-list mr-2 text-indigo-400"></i> Hasil Data Laporan
                    </h3>
                    <span class="bg-indigo-500/10 text-indigo-400 text-xs px-3 py-1 rounded-full font-bold border border-indigo-500/25">
                        {{ $data->count() }} Data Ditemukan
                    </span>
                </div>

                @if($data->isEmpty())
                    <div class="p-12 text-center text-slate-400">
                        <i class="fa-solid fa-ban text-4xl text-slate-600 mb-3 block"></i>
                        <p class="text-sm">Tidak ada baris data yang cocok dengan kriteria laporan ini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-700/50">
                            <thead class="bg-slate-900/50">
                                <tr>
                                    @foreach($headings as $colKey => $label)
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-400 uppercase tracking-wider">
                                            {{ $label }}
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-700/50 bg-slate-800/20 text-slate-300 text-sm">
                                @foreach($data as $row)
                                    <tr class="hover:bg-slate-700/20 transition-colors">
                                        @foreach($headings as $colKey => $label)
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $val = $row->{$colKey};
                                                    if ($colKey === 'gender') {
                                                        $val = $val === 'L' ? 'Laki-laki' : 'Perempuan';
                                                    }
                                                    if (in_array($colKey, ['amount', 'remaining_balance'])) {
                                                        $val = 'Rp ' . number_format($val, 0, ',', '.');
                                                    }
                                                    if ($colKey === 'status') {
                                                        if ($val === 'paid') $val = 'Lunas (Paid)';
                                                        elseif ($val === 'partial') $val = 'Sebagian (Partial)';
                                                        elseif ($val === 'unpaid') $val = 'Belum Lunas';
                                                    }
                                                @endphp
                                                {{ $val ?? '-' }}
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- ApexCharts Rendering Engine -->
    @if(($report->config['chartType'] ?? 'table') !== 'table' && !$data->isEmpty())
        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const rawData = @json($data);
                const headings = @json($headings);
                const chartType = "{{ $report->config['chartType'] }}";

                // Analyze columns: identify first category column and first numeric column
                const keys = Object.keys(headings);
                const firstKey = keys[0];
                
                // Scan for any numeric column
                let numericKey = null;
                const numericCandidates = ['amount', 'remaining_balance', 'score'];
                for (let key of keys) {
                    if (numericCandidates.includes(key)) {
                        numericKey = key;
                        break;
                    }
                }

                let categories = [];
                let seriesData = [];
                let seriesName = '';

                if (numericKey) {
                    // Scenario A: Plot numeric values against categories (e.g. score, billings)
                    categories = rawData.map(row => row[firstKey] || 'N/A');
                    seriesData = rawData.map(row => parseFloat(row[numericKey]) || 0);
                    seriesName = headings[numericKey];
                } else {
                    // Scenario B: Count distribution (e.g. count of students by classroom or gender)
                    const distribution = {};
                    rawData.forEach(row => {
                        let catVal = row[firstKey] ?? 'N/A';
                        if (firstKey === 'gender') catVal = catVal === 'L' ? 'Laki-laki' : 'Perempuan';
                        distribution[catVal] = (distribution[catVal] || 0) + 1;
                    });

                    categories = Object.keys(distribution);
                    seriesData = Object.values(distribution);
                    seriesName = 'Jumlah Frekuensi';
                }

                // Compile ApexCharts config
                let options = {};
                
                if (['pie', 'donut'].includes(chartType)) {
                    options = {
                        chart: {
                            type: chartType,
                            height: '100%',
                            background: 'transparent',
                            foreColor: '#94a3b8'
                        },
                        labels: categories,
                        series: seriesData,
                        theme: { mode: 'dark' },
                        legend: { position: 'bottom', labels: { colors: '#f1f5f9' } },
                        tooltip: { theme: 'dark' }
                    };
                } else {
                    options = {
                        chart: {
                            type: chartType,
                            height: '100%',
                            toolbar: { show: false },
                            background: 'transparent',
                            foreColor: '#94a3b8'
                        },
                        colors: ['#6366f1'],
                        series: [{
                            name: seriesName,
                            data: seriesData
                        }],
                        xaxis: {
                            categories: categories,
                            labels: { style: { colors: '#94a3b8' } },
                            axisBorder: { show: false },
                            axisTicks: { show: false }
                        },
                        yaxis: {
                            labels: { style: { colors: '#94a3b8' } },
                        },
                        grid: {
                            borderColor: '#334155',
                            strokeDashArray: 4,
                        },
                        stroke: { curve: 'smooth', width: 3 },
                        dataLabels: { enabled: false },
                        tooltip: { theme: 'dark' }
                    };
                }

                const chartElement = document.querySelector("#report-apex-chart");
                if (chartElement) {
                    const chart = new ApexCharts(chartElement, options);
                    chart.render();
                }
            });
        </script>
        @endpush
    @endif
</x-app-layout>
