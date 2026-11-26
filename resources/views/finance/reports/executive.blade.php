<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-black text-2xl text-gray-800 leading-tight">
                {{ __('Executive Financial Intelligence') }}
            </h2>
            <div class="flex items-center space-x-3">
                <form action="{{ route('finance.reports.executive') }}" method="GET" class="flex items-center space-x-2">
                    <select name="year_id" onchange="this.form.submit()" class="rounded-xl border-gray-100 text-xs font-bold focus:border-primary-500 focus:ring-primary-500">
                        @foreach($years ?? [] as $year)
                            <option value="{{ $year->id }}" {{ $yearId == $year->id ? 'selected' : '' }}>
                                T.A {{ $year->year }} - {{ ucfirst($year->semester) }}
                            </option>
                        @endforeach
                    </select>
                </form>
                <button onclick="window.print()" class="px-4 py-2 bg-gray-900 text-white rounded-xl font-bold text-xs hover:bg-black transition-all">Ekspor Laporan</button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-10">
            
            <!-- Tab Navigasi -->
            <div class="bg-white p-2 rounded-2xl shadow-sm border border-gray-100 flex flex-wrap gap-2">
                <a href="{{ route('finance.reports.executive') }}" class="px-4 py-2.5 rounded-xl text-xs font-black transition-all bg-emerald-50 text-emerald-700 shadow-sm">
                     Ringkasan Eksekutif
                </a>
                <a href="{{ route('finance.reports.arrears') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Laporan Tunggakan
                </a>
                <a href="{{ route('finance.reports.projection') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Proyeksi Pendapatan
                </a>
                <a href="{{ route('finance.reports.monthly') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Rekap Bulanan
                </a>
                <a href="{{ route('finance.reports.income') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Laporan Laba Rugi
                </a>
                <a href="{{ route('finance.reports.balance') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Neraca Keuangan
                </a>
                <a href="{{ route('finance.reports.ledger') }}" class="px-4 py-2.5 rounded-xl text-xs font-bold transition-all text-gray-500 hover:text-gray-900 hover:bg-gray-50">
                     Log Jurnal Umum
                </a>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Total Projected -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Total Target Pendapatan</p>
                    <h3 class="text-2xl font-black text-gray-900">Rp {{ number_format($totalProjected, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-gray-400 mt-2 font-bold italic">Berdasarkan total tagihan terbit</p>
                </div>
                <!-- Total Collected -->
                <div class="bg-emerald-600 p-6 rounded-[2rem] shadow-xl text-white">
                    <p class="text-[10px] font-black text-emerald-100 uppercase tracking-widest mb-1">Dana Terkumpul (Cash In)</p>
                    <h3 class="text-2xl font-black">Rp {{ number_format($totalCollected, 0, ',', '.') }}</h3>
                    <div class="mt-2 flex items-center text-[10px] font-bold">
                        <span class="px-2 py-0.5 bg-emerald-500 rounded-lg mr-2">{{ number_format($collectionRate, 1) }}% Efficiency</span>
                    </div>
                </div>
                <!-- Total Outstanding -->
                <div class="bg-white p-6 rounded-[2rem] shadow-sm border border-gray-100">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Sisa Piutang (Arrears)</p>
                    <h3 class="text-2xl font-black text-red-600">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</h3>
                    <p class="text-[10px] text-red-400 mt-2 font-bold italic">Menunggu pembayaran</p>
                </div>
                <!-- Status -->
                <div class="bg-gray-900 p-6 rounded-[2rem] shadow-sm text-white">
                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest mb-1">Kesehatan Finansial</p>
                    <h3 class="text-2xl font-black {{ $collectionRate > 80 ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ $collectionRate > 80 ? 'Sangat Sehat' : ($collectionRate > 50 ? 'Stabil' : 'Perlu Atensi') }}
                    </h3>
                    <p class="text-[10px] text-gray-500 mt-2 font-bold italic tracking-tighter">Berdasarkan rasio kolektabilitas</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Monthly Trend Chart -->
                <div class="lg:col-span-2 bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                    <h4 class="text-lg font-black text-gray-800 mb-8 flex items-center">
                        <span class="w-2 h-6 bg-primary-600 rounded-full mr-3"></span> Tren Pendapatan Bulanan
                    </h4>
                    <div class="h-64">
                        <canvas id="revenueTrendChart"></canvas>
                    </div>
                </div>

                <!-- Arrears by Class -->
                <div class="bg-white rounded-[2.5rem] p-8 shadow-sm border border-gray-100">
                    <h4 class="text-lg font-black text-gray-800 mb-6 flex items-center">
                        <span class="w-2 h-6 bg-red-500 rounded-full mr-3"></span> Tunggakan per Kelas
                    </h4>
                    <div class="space-y-5">
                        @foreach($classArrears ?? [] as $class)
                            <div>
                                <div class="flex justify-between text-xs font-bold mb-1">
                                    <span class="text-gray-600 uppercase">{{ $class['name'] }}</span>
                                    <span class="text-red-600">Rp {{ number_format($class['outstanding'], 0, ',', '.') }}</span>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    @php 
                                        $percent = $class['total_bill'] > 0 ? ($class['total_paid'] / $class['total_bill']) * 100 : 0;
                                    @endphp
                                    <div class="bg-emerald-500 h-1.5 rounded-full" style="width: {{ $percent }}%"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-gray-400 mt-1">
                                    <span>Collected: {{ number_format($percent, 1) }}%</span>
                                    <span>Target: Rp {{ number_format($class['total_bill'] / 1000000, 1) }}jt</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Top Debtors Table -->
            <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8 border-b border-gray-50 flex justify-between items-center">
                    <h4 class="text-lg font-black text-gray-800 flex items-center">
                        <span class="w-2 h-6 bg-amber-500 rounded-full mr-3"></span> 10 Penunggak Terbesar
                    </h4>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-widest">Memerlukan Penanganan Segera</span>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Nama Siswa</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Kelas</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest">Total Tunggakan</th>
                            <th class="px-8 py-4 text-[10px] font-black text-gray-400 uppercase tracking-widest text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($topDebtors ?? [] as $debtor)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-gray-900">{{ $debtor['student']?->user?->name }}</div>
                                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tighter">NIS: {{ $debtor['student']->nis }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-sm font-bold text-gray-600">{{ $debtor['student']?->classroom?->name ?? 'N/A' }}</span>
                                </td>
                                <td class="px-8 py-5">
                                    <span class="text-sm font-black text-red-600">Rp {{ number_format($debtor['outstanding'], 0, ',', '.') }}</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <a href="{{ route('finance.billings.index', ['student_id' => $debtor['student']->id]) }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-xl text-[10px] font-black uppercase hover:bg-primary-600 hover:text-white transition-all">Detail Tagihan</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-8 py-10 text-center text-gray-400 italic text-sm">Luar biasa! Tidak ada siswa yang menunggak.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <!-- Chart Scripts -->
    <x-vendor.script lib="chartjs" />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($monthlyRevenue->pluck('month')) !!},
                    datasets: [{
                        label: 'Pendapatan (Cash In)',
                        data: {!! json_encode($monthlyRevenue->pluck('total')) !!},
                        borderColor: '#059669',
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 4,
                        pointBackgroundColor: '#059669',
                        pointRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { 
                            beginAtZero: true,
                            grid: { borderDash: [5, 5], color: '#f3f4f6' },
                            ticks: { callback: function(value) { return 'Rp ' + (value/1000000) + 'jt'; } }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });
        });
    </script>
</x-app-layout>
