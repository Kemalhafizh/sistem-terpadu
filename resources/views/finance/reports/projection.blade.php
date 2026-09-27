<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Proyeksi Pendapatan Keuangan') }}
            </h2>
            <div class="flex items-center space-x-3">
                <form action="{{ route('finance.reports.projection') }}" method="GET" class="flex items-center space-x-2">
                    <select name="year_id" onchange="this.form.submit()" class="rounded-xl border-gray-100 text-xs font-bold focus:border-primary-500 focus:ring-primary-500">
                        @foreach($years ?? [] as $year)
                            <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>
                                T.A {{ $year->year }} - {{ ucfirst($year->semester) }}
                            </option>
                        @endforeach
                    </select>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">

            <!-- Back to Executive Report Link -->
            <div class="flex justify-between items-center">
                <a href="{{ route('finance.reports.executive') }}" class="text-xs font-bold text-gray-500 hover:text-gray-900 flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali ke Dashboard Eksekutif
                </a>
            </div>

            <!-- Projection KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Collection Rate -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Rasio Kolektabilitas (6 Bln)</p>
                    <h3 class="text-2xl font-black text-gray-900">{{ number_format($collectionRate, 1) }}%</h3>
                    <p class="text-[10px] text-emerald-600 mt-2 font-bold italic">Rasio efisiensi kas masuk historis</p>
                </div>
                <!-- Total Forecast Billings -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Tagihan 6 Bulan Depan</p>
                    <h3 class="text-2xl font-black text-gray-900">Rp {{ number_format(array_sum($billingAmounts), 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-gray-400 mt-2 font-bold italic">Total tagihan akan jatuh tempo</p>
                </div>
                <!-- Projected Cash Inflow -->
                <div class="bg-emerald-600 p-6 rounded-[2rem] shadow-xl text-white">
                    <p class="text-[10px] font-black text-emerald-100 uppercase tracking-widest mb-1">Estimasi Kas Masuk</p>
                    <h3 class="text-2xl font-black">Rp {{ number_format(array_sum($projectedCollections), 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-emerald-100 mt-2 font-bold italic">Berdasarkan rasio kolektabilitas {{ number_format($collectionRate, 1) }}%</p>
                </div>
                <!-- Projected Outstanding -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Estimasi Outstanding (Tunggakan)</p>
                    <h3 class="text-2xl font-black text-red-600">Rp {{ number_format(array_sum($projectedOutstandings), 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-red-400 mt-2 font-bold italic">Potensi sisa piutang tertunda</p>
                </div>
            </div>

            <!-- Chart Section -->
            <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100 space-y-4">
                <h4 class="text-base font-black text-gray-800 uppercase tracking-wider flex items-center">
                    <span class="w-2 h-5 bg-emerald-500 rounded-full mr-2.5"></span> Grafik Proyeksi Aliran Kas Masuk (6 Bulan Mendatang)
                </h4>
                <div id="projectionChart"></div>
            </div>

            <!-- Projection Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                    <h4 class="text-base font-black text-gray-800 uppercase tracking-wider flex items-center">
                        <span class="w-2 h-5 bg-indigo-500 rounded-full mr-2.5"></span> Rincian Proyeksi Bulanan
                    </h4>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-gray-50/50 text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                <th class="px-8 py-4">Bulan Tagihan</th>
                                <th class="px-8 py-4 text-center">Total Tagihan Rencana</th>
                                <th class="px-8 py-4 text-center">Proyeksi Dana Terkumpul ({{ number_format($collectionRate, 1) }}%)</th>
                                <th class="px-8 py-4 text-center">Proyeksi Outstanding Piutang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach($months as $index => $monthLabel)
                                <tr class="text-xs text-gray-700 hover:bg-gray-50/50 transition-all">
                                    <td class="px-8 py-5 font-black text-gray-900 uppercase">{{ $monthLabel }}</td>
                                    <td class="px-8 py-5 text-center font-bold text-gray-700">Rp {{ number_format($billingAmounts[$index], 0, ',', '.') }}</td>
                                    <td class="px-8 py-5 text-center font-black text-emerald-600">Rp {{ number_format($projectedCollections[$index], 0, ',', '.') }}</td>
                                    <td class="px-8 py-5 text-center font-black text-red-600">Rp {{ number_format($projectedOutstandings[$index], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- ApexCharts JS -->
    <x-vendor.script lib="apexcharts" />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const options = {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: { show: false },
                    fontFamily: 'Inter, sans-serif'
                },
                series: [
                    {
                        name: 'Rencana Tagihan (Total)',
                        data: @json($billingAmounts)
                    },
                    {
                        name: 'Proyeksi Dana Terkumpul (Cash In)',
                        data: @json($projectedCollections)
                    }
                ],
                xaxis: {
                    categories: @json($months),
                    labels: { style: { colors: '#9ca3af', fontWeight: 'bold' } }
                },
                yaxis: {
                    labels: {
                        style: { colors: '#9ca3af', fontWeight: 'bold' },
                        formatter: function (val) {
                            return 'Rp ' + (val/1000000).toFixed(1) + ' Jt';
                        }
                    }
                },
                colors: ['#6366f1', '#10b981'], // Indigo, Emerald
                fill: {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.35,
                        opacityTo: 0.05,
                        stops: [0, 100]
                    }
                },
                stroke: { curve: 'smooth', width: 3 },
                dataLabels: { enabled: false },
                grid: { borderColor: '#f3f4f6' },
                legend: { position: 'top', horizontalAlign: 'right', labels: { colors: '#4b5563', fontWeight: 'bold' } }
            };
            new ApexCharts(document.querySelector("#projectionChart"), options).render();
        });
    </script>
</x-app-layout>
